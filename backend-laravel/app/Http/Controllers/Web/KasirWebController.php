<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DiscountCode;
use App\Models\InvoiceCounter;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Notifications\CashDifferenceNotification;
use App\Notifications\QrisPaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class KasirWebController extends Controller
{
    public function pos()
    {
        $shift = Shift::where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->latest()->first();

        // Kasir wajib buka shift dulu sebelum bisa transaksi
        if (!$shift) {
            return redirect()->route('kasir.shift')
                ->with('error', 'Anda harus membuka shift terlebih dahulu sebelum berjualan.');
        }

        $products = Product::with('stock')
            ->where('branch_id', session('branch_id'))
            ->get();

        $taxPercent = (float) Setting::get('tax_percent', 0);

        return view('kasir.pos', compact('products', 'shift', 'taxPercent'));
    }

    /**
     * Preview kode diskon SEBELUM pembayaran — dipanggil dari JS saat kasir
     * klik "Terapkan" di POS. Ini HANYA preview: tidak menambah used_count
     * dan tidak mengunci baris apa pun (lock=false), karena tidak ada state
     * yang diubah di sini.
     *
     * Subtotal di sini dipercaya dari client (dihitung dari isi keranjang di
     * browser) — ini aman karena cuma dipakai untuk MENAMPILKAN estimasi
     * potongan, bukan keputusan final. Begitu kasir benar-benar checkout(),
     * subtotal dihitung ULANG di server dari harga produk yang sesungguhnya
     * (bukan dari input client) dan kode diskon divalidasi ULANG dari nol —
     * jadi kasir tidak bisa memanipulasi subtotal di sini untuk dapat
     * potongan lebih besar dari seharusnya.
     */
    public function applyDiscount(Request $request)
    {
        $request->validate([
            'code'     => 'required|string|max:30',
            'subtotal' => 'required|numeric|min:0.01',
        ]);

        try {
            $discount = DiscountCode::validateForCheckout(
                $request->code,
                (float) $request->subtotal,
                session('branch_id')
            );
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $discountAmount = $discount->calculateDiscountAmount((float) $request->subtotal);

        return response()->json([
            'success'         => true,
            'code'            => $discount->code,
            'discount_amount' => $discountAmount,
            'message'         => "Kode diskon {$discount->code} berhasil diterapkan.",
        ]);
    }

    public function checkout(Request $request)
    {
        $wantsJson = $request->wantsJson() || $request->ajax();

        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required|exists:products,id',
            'items.*.qty'       => 'required|integer|min:1',
            'payment'           => 'required|numeric|min:0',
            'payment_method'    => 'required|in:cash,qris',
            'discount_code'     => 'nullable|string|max:30',
            // Dikirim oleh JS kasir (dibuat di browser). Dipakai untuk mencegah
            // transaksi tersimpan dobel kalau request sync offline dikirim ulang.
            'client_txn_id'     => 'nullable|string|max:64',
        ]);

        // ── Idempotency check ───────────────────────────────────────────
        // Kalau transaksi dengan client_txn_id ini sudah pernah tersimpan
        // (misal koneksi putus setelah server sukses simpan tapi sebelum
        // browser terima response), langsung anggap sukses tanpa proses ulang.
        if ($request->client_txn_id) {
            $existing = Transaction::where('client_txn_id', $request->client_txn_id)->first();
            if ($existing) {
                return $this->checkoutResponse($wantsJson, $existing, duplicate: true);
            }
        }

        $shift = Shift::where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->latest()->first();

        if (!$shift) {
            $message = 'Shift tidak aktif. Silakan buka shift terlebih dahulu.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        // ── Cek stok & proses checkout dalam satu DB transaction ──────────
        // Semua pengecekan (stok cukup, pembayaran cukup) dilakukan DI DALAM
        // transaction ini, setelah baris stok dikunci (lockForUpdate). Ini
        // mencegah race condition: kalau 2 checkout produk yang sama datang
        // nyaris bersamaan, checkout kedua akan menunggu checkout pertama
        // commit dulu sebelum bisa baca quantity stok yang sudah ter-update,
        // bukan baca data basi yang membuat keduanya lolos validasi.
        // Diambil di luar DB::transaction karena cuma baca Settings (tidak perlu
        // ikut dikunci bersama baris stok).
        $taxPercent = (float) Setting::get('tax_percent', 0);

        $transaction = null;

        try {
            $transaction = DB::transaction(function () use ($request, $shift, $taxPercent) {
                // ── Kunci baris stok yang terlibat, urut berdasarkan product_id ──
                // Urutan yang konsisten (ascending) mencegah deadlock kalau ada dua
                // checkout paralel yang sama-sama butuh produk A & B tapi beda urutan
                // input. Stok DIKUNCI DI DALAM transaction ini (lockForUpdate), jadi
                // checkout lain yang butuh produk yang sama harus menunggu transaction
                // ini commit dulu sebelum bisa baca quantity terbaru — mencegah dua
                // checkout paralel sama-sama lolos validasi "stok cukup" dari data basi
                // lalu sama-sama mengurangi stok sampai minus.
                $productIds = collect($request->items)->pluck('id')->unique()->sort()->values();

                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

                $stocks = Stock::whereIn('product_id', $productIds)
                    ->orderBy('product_id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                $total = 0;
                $cartData = [];

                foreach ($request->items as $item) {
                    $product = $products->get($item['id']);
                    $stock   = $stocks->get($item['id']);

                    if (!$stock || $stock->quantity < $item['qty']) {
                        $nama = $product->name ?? 'produk';
                        throw new \RuntimeException("Stok {$nama} tidak cukup.");
                    }

                    $subtotal = $product->price * $item['qty'];
                    $total   += $subtotal;

                    $cartData[] = [
                        'product'  => $product,
                        'stock'    => $stock,
                        'qty'      => $item['qty'],
                        'subtotal' => $subtotal,
                    ];
                }

                // ── Kode Diskon (opsional) ──────────────────────────────────
                // Divalidasi ULANG dari nol di sini (bukan cuma percaya hasil
                // preview applyDiscount()) — kasir bisa saja klik "Terapkan"
                // lalu menunggu lama sebelum bayar, di mana kode diskon bisa
                // saja sudah kadaluarsa/nonaktif/kuota habis di antara waktu itu.
                // lock=true supaya baris discount_codes dikunci DI DALAM
                // transaction ini sebelum used_count ditambah — pola yang sama
                // persis dengan lockForUpdate() pada Stock di atas, mencegah dua
                // kasir sama-sama lolos validasi "kuota tersisa 1" dari data basi.
                $discountCode   = null;
                $discountAmount = 0;

                if ($request->filled('discount_code')) {
                    $discountCode = DiscountCode::validateForCheckout(
                        $request->discount_code,
                        $total,
                        session('branch_id'),
                        lock: true
                    );
                    $discountAmount = $discountCode->calculateDiscountAmount($total);
                }

                // ── Pajak (diatur Owner lewat halaman Settings) ──
                // Dihitung dari subtotal SETELAH dipotong diskon — supaya kalau
                // ada diskon, customer tidak membayar pajak atas nominal yang
                // sudah tidak perlu ia bayar. 'total' (dan seluruh akumulasi
                // omzet turunan lain di bawah) sengaja dihitung SETELAH pajak,
                // karena itulah nominal yang benar-benar diterima kasir dari
                // customer.
                $taxableBase  = $total - $discountAmount;
                $taxAmount    = round($taxableBase * $taxPercent / 100);
                $preRoundTotal = $taxableBase + $taxAmount;

                // ── Pembulatan kembalian (khusus Tunai) ──
                // Pajak sering menghasilkan angka receh (mis. Rp250/Rp750) yang
                // tidak punya pecahan uang fisik, menyulitkan kasir menghitung
                // kembalian. Untuk Tunai, total akhir dibulatkan ke kelipatan
                // Rp500 terdekat. QRIS tidak dibulatkan karena nominal digital
                // bisa dibayar presisi berapa pun tanpa masalah kembalian.
                if ($request->payment_method === 'cash') {
                    $grandTotal = (float) (round($preRoundTotal / 500) * 500);
                } else {
                    $grandTotal = $preRoundTotal;
                }
                $roundingAmount = $grandTotal - $preRoundTotal;

                if ($request->payment < $grandTotal) {
                    throw new \RuntimeException('Jumlah pembayaran kurang dari total belanja.');
                }

                $transaction = Transaction::create([
                    'invoice_number'   => $this->generateInvoiceNumber(),
                    'client_txn_id'    => $request->client_txn_id,
                    'user_id'          => Auth::id(),
                    'branch_id'        => session('branch_id'),
                    'shift_id'         => $shift->id,
                    'discount_code_id' => $discountCode?->id,
                    'subtotal'         => $total,
                    'discount_amount'  => $discountAmount,
                    'tax_amount'       => $taxAmount,
                    'rounding_amount'  => $roundingAmount,
                    'total'            => $grandTotal,
                    'payment'          => $request->payment,
                    'payment_method'   => $request->payment_method,
                    'change_amount'    => $request->payment - $grandTotal,
                    'status'           => 'sukses',
                    // Kalau request ini datang lewat sync offline (ada client_txn_id
                    // dan dikirim setelah delay), tetap ditandai tersinkronisasi karena
                    // pada titik ini transaksi sudah berhasil sampai ke server.
                    'sync_status'      => 'tersinkronisasi',
                    'synced_at'        => now(),
                ]);

                // Baris kode diskon sudah dikunci (lockForUpdate) di atas sejak
                // sebelum perhitungan pajak, jadi increment ini aman dari race
                // condition walau ada banyak checkout paralel memakai kode yang
                // sama dengan kuota tersisa sedikit.
                $discountCode?->increment('used_count');

                foreach ($cartData as $row) {
                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id'     => $row['product']->id,
                        'qty'            => $row['qty'],
                        'price_at_sale'  => $row['product']->price,
                        'subtotal'       => $row['subtotal'],
                    ]);

                    // Kurangi stok yang sudah dikunci di atas
                    $row['stock']->decrement('quantity', $row['qty']);
                }

                // Update akumulasi shift (pakai grandTotal — nominal yang
                // benar-benar diterima kasir termasuk pajak).
                $shift->increment('total_sales', $grandTotal);
                $shift->increment('total_transactions');

                // Pisahkan akumulasi cash vs QRIS supaya rekonsiliasi kas fisik
                // saat tutup shift (closeShift) tidak ikut menghitung uang QRIS
                // yang tidak pernah masuk ke laci kas.
                if ($request->payment_method === 'qris') {
                    $shift->increment('total_qris_sales', $grandTotal);
                } else {
                    $shift->increment('total_cash_sales', $grandTotal);
                }

                // ── Ringkasan keuangan harian per cabang (financial_reports) ──
                // Upsert atomic pakai raw query: kalau baris (branch_id, date)
                // sudah ada, nilai baru DITAMBAHKAN (bukan ditimpa), sehingga aman
                // dipanggil dari banyak transaksi paralel tanpa race condition —
                // sama seperti pendekatan invoice_counters di generateInvoiceNumber().
                DB::statement(
                    'INSERT INTO financial_reports
                        (branch_id, date, total_revenue, total_expense, net_profit, total_transactions, created_at, updated_at)
                     VALUES (?, ?, ?, 0, ?, 1, NOW(), NOW())
                     ON DUPLICATE KEY UPDATE
                        total_revenue = total_revenue + VALUES(total_revenue),
                        net_profit = net_profit + VALUES(net_profit),
                        total_transactions = total_transactions + 1,
                        updated_at = NOW()',
                    [$shift->branch_id, now()->toDateString(), $grandTotal, $grandTotal]
                );

                return $transaction;
            });
        } catch (\RuntimeException $e) {
            // Kegagalan validasi terkendali (stok kurang / bayar kurang) yang
            // dilempar dari dalam transaction di atas — transaction otomatis
            // di-rollback oleh Laravel begitu exception ini terlempar.
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage());
        }

        // ── Notifikasi ke Owner untuk setiap pembayaran QRIS ──
        // Owner ingin tahu real-time setiap ada transaksi QRIS masuk: dari
        // kasir mana, cabang mana, dan berapa nominalnya. Dikirim setelah
        // DB transaction commit (bukan di dalamnya) supaya kegagalan kirim
        // notifikasi tidak pernah membatalkan transaksi penjualan yang
        // sudah sukses tersimpan.
        if ($request->payment_method === 'qris') {
            $owners = User::where('role', 'owner')->get();
            if ($owners->isNotEmpty()) {
                Notification::send($owners, new QrisPaymentNotification($transaction->fresh(['user', 'branch'])));
            }
        }

        return $this->checkoutResponse($wantsJson, $transaction, duplicate: false);
    }

    /**
     * Generate nomor invoice yang aman dari race condition.
     *
     * Sebelumnya pakai Transaction::count()+1 yang dibaca lalu ditulis secara
     * terpisah (read-then-write) — kalau dua checkout (misal beberapa transaksi
     * offline yang sync bersamaan begitu koneksi pulih) masuk hampir bersamaan,
     * keduanya bisa membaca count() yang sama sebelum insert manapun selesai,
     * sehingga menghasilkan invoice_number dobel.
     *
     * Di sini counter disimpan di tabel terpisah (invoice_counters, satu baris
     * per tanggal) dan baris tersebut di-lock dengan SELECT ... FOR UPDATE di
     * dalam DB transaction yang sama dengan pembuatan Transaction. Request lain
     * yang mencoba ambil nomor di hari yang sama akan menunggu (blocked) sampai
     * transaksi pertama commit, baru dapat giliran. Ini menjamin nomor urut
     * per hari tidak pernah bentrok, walau ada banyak checkout paralel.
     */
    private function generateInvoiceNumber(): string
    {
        $today = now()->format('Y-m-d');

        // Baris counter untuk hari ini dibuat kalau belum ada (aman dipanggil
        // berkali-kali karena counter_date unique + insertOrIgnore).
        InvoiceCounter::query()->insertOrIgnore([
            'counter_date' => $today,
            'last_number'  => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $counter = InvoiceCounter::where('counter_date', $today)
            ->lockForUpdate()
            ->first();

        $nextNumber = $counter->last_number + 1;
        $counter->update(['last_number' => $nextNumber]);

        return 'INV-' . now()->format('Ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function checkoutResponse(bool $wantsJson, Transaction $transaction, bool $duplicate)
    {
        if ($wantsJson) {
            return response()->json([
                'success'         => true,
                'duplicate'       => $duplicate,
                'invoice_number'  => $transaction->invoice_number,
                'message'         => "Transaksi {$transaction->invoice_number} berhasil disimpan.",
                'receipt_url'     => route('kasir.pos.receipt', $transaction->id),
            ]);
        }

        return redirect()->route('kasir.pos')
            ->with('success', "Transaksi {$transaction->invoice_number} berhasil disimpan.");
    }

    /**
     * Halaman struk transaksi — dibuka lewat tab baru dari POS dan bisa
     * dicetak langsung (window.print()) sesuai UC-01 langkah 6.
     */
    public function receipt(Transaction $transaction)
    {
        // Kasir cuma boleh lihat struk transaksinya sendiri.
        abort_unless($transaction->user_id === Auth::id(), 403);

        $transaction->load('details.product', 'user', 'branch');

        return view('kasir.receipt', compact('transaction'));
    }

    public function shift()
    {
        $shift = Shift::where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->latest()->first();

        $riwayat = Shift::where('user_id', Auth::id())
            ->whereNotNull('closed_at')
            ->latest()->limit(10)->get();

        return view('kasir.shift', compact('shift', 'riwayat'));
    }

    public function openShift(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $existing = Shift::where('user_id', Auth::id())
            ->whereNull('closed_at')->first();

        if ($existing) {
            return back()->with('error', 'Anda sudah memiliki shift yang aktif.');
        }

        Shift::create([
            'user_id'      => Auth::id(),
            'branch_id'    => session('branch_id'),
            'opening_cash' => $request->opening_cash,
            'status'       => 'aktif',
            'opened_at'    => now(),
        ]);

        return redirect()->route('kasir.pos')->with('success', 'Shift berhasil dibuka. Selamat berjualan!');
    }

    public function closeShift(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
        ]);

        $shift = Shift::where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->latest()->first();

        if (!$shift) {
            return back()->with('error', 'Tidak ada shift aktif untuk ditutup.');
        }

        // Kas fisik yang diharapkan ada di laci HANYA dari transaksi cash.
        // Uang QRIS masuk ke rekening/akun QRIS terpisah, bukan ke laci,
        // jadi tidak boleh ikut dihitung di sini.
        $expected = $shift->opening_cash + $shift->total_cash_sales;
        $difference = $request->closing_cash - $expected;

        $shift->update([
            'closing_cash'  => $request->closing_cash,
            'expected_cash' => $expected,
            'difference'    => $difference,
            'status'        => 'tutup',
            'closed_at'     => now(),
        ]);

        // ── Notifikasi ke Owner kalau selisih kas > Rp5.000 (UC-02 langkah 7) ──
        if (abs($difference) > 5000) {
            $owners = User::where('role', 'owner')->get();
            if ($owners->isNotEmpty()) {
                Notification::send($owners, new CashDifferenceNotification($shift->fresh(['user', 'branch'])));
            }
        }

        return redirect()->route('kasir.shift')->with('success', 'Shift berhasil ditutup.');
    }

    public function transactions()
    {
        $transactions = Transaction::with('details.product')
            ->where('user_id', Auth::id())
            ->latest()->paginate(15);

        return view('kasir.transactions', compact('transactions'));
    }

    public function settings()
    {
        return view('kasir.settings');
    }

    /**
     * Update profil kasir (nama, email, & opsional password). Password
     * dibiarkan kosong kalau kasir tidak ingin menggantinya — cuma nama/email
     * yang berubah kalau field password tidak diisi.
     *
     * Setelah berhasil, halaman menampilkan pop-up konfirmasi lalu MEMAKSA
     * logout begitu kasir menekan OK (lihat kasir/settings.blade.php) —
     * supaya kasir login ulang dengan kredensial barunya dan sesi lama yang
     * mungkin masih dipakai di device lain otomatis tidak valid lagi
     * (session di-invalidate saat logout, bukan cuma redirect kosong).
     */
    public function updateSettings(Request $request)
    {
        // @var hint di bawah ini murni untuk static analyzer (Intelephense/PHPStan):
        // Auth::user() punya return type Authenticatable|null (interface tanpa
        // method save()), padahal runtime-nya selalu instance App\Models\User
        // (Eloquent Model yang punya save()). Tanpa hint ini editor akan salah
        // menandai $user->save() di bawah sebagai "Undefined method" walau
        // kodenya tetap berjalan normal.
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            // Password sepenuhnya opsional. Kalau diisi, wajib penuhi semua
            // aturan kompleksitas (min 8 karakter, huruf besar+kecil, angka,
            // simbol) dan wajib sama dengan password_confirmation.
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak sama dengan password baru.',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('profile_updated', true);
    }
}
