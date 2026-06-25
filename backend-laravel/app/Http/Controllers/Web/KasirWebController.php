<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required|exists:products,id',
            'items.*.qty'       => 'required|integer|min:1',
            'payment'           => 'required|numeric|min:0',
        ]);

        $shift = Shift::where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->latest()->first();

        if (!$shift) {
            return back()->with('error', 'Shift tidak aktif. Silakan buka shift terlebih dahulu.');
        }

        // Hitung total & validasi stok dulu sebelum simpan apa pun
        $total = 0;
        $cartData = [];

        foreach ($request->items as $item) {
            $product = Product::with('stock')->findOrFail($item['id']);
            $stock   = $product->stock;

            if (!$stock || $stock->quantity < $item['qty']) {
                return back()->with('error', "Stok {$product->name} tidak cukup.");
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
            return back()->with('error', 'Jumlah pembayaran kurang dari total belanja.');
        }

        $transaction = DB::transaction(function () use ($cartData, $total, $request, $shift) {
            $transaction = Transaction::create([
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad(Transaction::count() + 1, 4, '0', STR_PAD_LEFT),
                'user_id'        => Auth::id(),
                'branch_id'      => session('branch_id'),
                'shift_id'       => $shift->id,
                'total'          => $total,
                'payment'        => $request->payment,
                'change_amount'  => $request->payment - $total,
                'status'         => 'sukses',
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

            return $transaction;
        });

        return redirect()->route('kasir.pos')
            ->with('success', "Transaksi {$transaction->invoice_number} berhasil disimpan.");
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

        $shift->update([
            'closing_cash'  => $request->closing_cash,
            'expected_cash' => $expected,
            'difference'    => $request->closing_cash - $expected,
            'status'        => 'tutup',
            'closed_at'     => now(),
        ]);

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