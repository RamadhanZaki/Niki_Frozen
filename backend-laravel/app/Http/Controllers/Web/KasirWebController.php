<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InvoiceCounter;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Notifications\CashDifferenceNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

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

        return view('kasir.pos', compact('products', 'shift'));
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

        // Hitung total & validasi stok dulu sebelum simpan apa pun
        $total = 0;
        $cartData = [];

        foreach ($request->items as $item) {
            $product = Product::with('stock')->findOrFail($item['id']);
            $stock   = $product->stock;

            if (!$stock || $stock->quantity < $item['qty']) {
                $message = "Stok {$product->name} tidak cukup.";
                return $wantsJson
                    ? response()->json(['success' => false, 'message' => $message], 422)
                    : back()->with('error', $message);
            }

            $subtotal = $product->price * $item['qty'];
            $total   += $subtotal;

            $cartData[] = [
                'product'  => $product,
                'qty'      => $item['qty'],
                'subtotal' => $subtotal,
            ];
        }

        if ($request->payment < $total) {
            $message = 'Jumlah pembayaran kurang dari total belanja.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $transaction = DB::transaction(function () use ($cartData, $total, $request, $shift) {
            $transaction = Transaction::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'client_txn_id'  => $request->client_txn_id,
                'user_id'        => Auth::id(),
                'branch_id'      => session('branch_id'),
                'shift_id'       => $shift->id,
                'total'          => $total,
                'payment'        => $request->payment,
                'payment_method' => $request->payment_method,
                'change_amount'  => $request->payment - $total,
                'status'         => 'sukses',
                // Kalau request ini datang lewat sync offline (ada client_txn_id
                // dan dikirim setelah delay), tetap ditandai tersinkronisasi karena
                // pada titik ini transaksi sudah berhasil sampai ke server.
                'sync_status'    => 'tersinkronisasi',
                'synced_at'      => now(),
            ]);

            foreach ($cartData as $row) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $row['product']->id,
                    'qty'            => $row['qty'],
                    'price_at_sale'  => $row['product']->price,
                    'subtotal'       => $row['subtotal'],
                ]);

                // Kurangi stok
                $row['product']->stock->decrement('quantity', $row['qty']);
            }

            // Update akumulasi shift
            $shift->increment('total_sales', $total);
            $shift->increment('total_transactions');

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
                [$shift->branch_id, now()->toDateString(), $total, $total]
            );

            return $transaction;
        });

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

        $expected = $shift->opening_cash + $shift->total_sales;
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
}
