<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * GET /api/cashier/transactions
     * Riwayat transaksi kasir yang sedang login
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Transaction::with('details.product')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');

        // Filter opsional berdasarkan shift
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }

        $transactions = $query->paginate(20)->through(fn($t) => $this->format($t));

        return response()->json(['transactions' => $transactions]);
    }

    /**
     * POST /api/cashier/transactions
     * Simpan 1 transaksi (online real-time)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
            'payment'       => 'required|numeric|min:0',
            'shift_id'      => 'required|integer|exists:shifts,id',
        ]);

        // Validasi shift aktif milik kasir ini
        $shift = Shift::where('id', $request->shift_id)
            ->where('user_id', $user->id)
            ->where('status', 'aktif')
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'Shift tidak valid atau sudah ditutup.'], 422);
        }

        $total = collect($request->items)->sum(fn($i) => $i['qty'] * $i['price']);

        if ($request->payment < $total) {
            return response()->json(['message' => 'Jumlah pembayaran kurang.'], 422);
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'invoice_number' => $this->generateInvoice(),
                'user_id'        => $user->id,
                'branch_id'      => $user->branch_id,
                'shift_id'       => $shift->id,
                'total'          => $total,
                'payment'        => $request->payment,
                'change_amount'  => $request->payment - $total,
                'status'         => 'sukses',
                'sync_status'    => 'tersinkronisasi',
                'synced_at'      => now(),
            ]);

            foreach ($request->items as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $item['product_id'],
                    'qty'            => $item['qty'],
                    'price_at_sale'  => $item['price'],
                    'subtotal'       => $item['qty'] * $item['price'],
                ]);

                // Kurangi stok
                Stock::where('product_id', $item['product_id'])
                    ->where('branch_id', $user->branch_id)
                    ->decrement('quantity', $item['qty']);
            }

            // Update total_sales & total_transactions di shift
            $shift->increment('total_sales', $total);
            $shift->increment('total_transactions');

            DB::commit();

            return response()->json([
                'message'     => 'Transaksi berhasil disimpan.',
                'transaction' => $this->format($transaction->load('details.product')),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/cashier/transactions/sync
     * Batch sync transaksi offline (array of transactions)
     */
    public function sync(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'transactions'   => 'required|array|min:1',
            'transactions.*.local_id'   => 'required',
            'transactions.*.shift_id'   => 'required|integer|exists:shifts,id',
            'transactions.*.items'      => 'required|array|min:1',
            'transactions.*.items.*.product_id' => 'required|integer|exists:products,id',
            'transactions.*.items.*.qty'         => 'required|integer|min:1',
            'transactions.*.items.*.price'       => 'required|numeric|min:0',
            'transactions.*.payment'    => 'required|numeric|min:0',
            'transactions.*.created_at' => 'nullable|date',
        ]);

        $results = [];

        foreach ($request->transactions as $data) {
            DB::beginTransaction();
            try {
                $shift = Shift::where('id', $data['shift_id'])
                    ->where('user_id', $user->id)
                    ->first();

                if (!$shift) {
                    $results[] = ['local_id' => $data['local_id'], 'status' => 'gagal', 'reason' => 'Shift tidak valid'];
                    DB::rollBack();
                    continue;
                }

                $total = collect($data['items'])->sum(fn($i) => $i['qty'] * $i['price']);

                $transaction = Transaction::create([
                    'invoice_number' => $this->generateInvoice(),
                    'user_id'        => $user->id,
                    'branch_id'      => $user->branch_id,
                    'shift_id'       => $shift->id,
                    'total'          => $total,
                    'payment'        => $data['payment'],
                    'change_amount'  => $data['payment'] - $total,
                    'status'         => 'sukses',
                    'sync_status'    => 'tersinkronisasi',
                    'synced_at'      => now(),
                    'created_at'     => isset($data['created_at']) ? $data['created_at'] : now(),
                ]);

                foreach ($data['items'] as $item) {
                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id'     => $item['product_id'],
                        'qty'            => $item['qty'],
                        'price_at_sale'  => $item['price'],
                        'subtotal'       => $item['qty'] * $item['price'],
                    ]);

                    Stock::where('product_id', $item['product_id'])
                        ->where('branch_id', $user->branch_id)
                        ->decrement('quantity', $item['qty']);
                }

                $shift->increment('total_sales', $total);
                $shift->increment('total_transactions');

                DB::commit();
                $results[] = ['local_id' => $data['local_id'], 'status' => 'ok', 'server_id' => $transaction->id];

            } catch (\Throwable $e) {
                DB::rollBack();
                $results[] = ['local_id' => $data['local_id'], 'status' => 'gagal', 'reason' => $e->getMessage()];
            }
        }

        return response()->json(['results' => $results]);
    }

    // -------------------------------------------------------------------------

    private function generateInvoice(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        $last = Transaction::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function format(Transaction $t): array
    {
        return [
            'id'             => $t->id,
            'invoice_number' => $t->invoice_number,
            'total'          => (float) $t->total,
            'payment'        => (float) $t->payment,
            'change_amount'  => (float) $t->change_amount,
            'status'         => $t->status,
            'sync_status'    => $t->sync_status,
            'shift_id'       => $t->shift_id,
            'created_at'     => $t->created_at?->format('Y-m-d H:i:s'),
            'items'          => $t->relationLoaded('details')
                ? $t->details->map(fn($d) => [
                    'product_id'   => $d->product_id,
                    'name'         => $d->product?->name ?? '-',
                    'qty'          => $d->qty,
                    'price'        => (float) $d->price_at_sale,
                    'subtotal'     => (float) $d->subtotal,
                ])->toArray()
                : [],
        ];
    }
}