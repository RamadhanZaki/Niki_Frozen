<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    private function checkOwner(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Akses ditolak. Hanya owner yang diizinkan.'], 403);
        }
        return null;
    }

    /** GET /api/stocks */
    public function index(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $query = Product::with(['branch', 'stock']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('stock_filter')) {
            if ($request->stock_filter === 'low') {
                $query->whereHas('stock', fn($q) => $q->whereRaw('quantity <= min_stock')->where('quantity', '>', 0));
            } elseif ($request->stock_filter === 'critical') {
                $query->whereHas('stock', fn($q) => $q->where('quantity', 0));
            } elseif ($request->stock_filter === 'normal') {
                $query->whereHas('stock', fn($q) => $q->whereRaw('quantity > min_stock'));
            }
        }

        $products = $query->orderBy('name')->get()->map(fn($p) => $this->formatStock($p));

        $lowStock      = DB::table('stocks')->whereRaw('quantity <= min_stock')->where('quantity', '>', 0)->count();
        $criticalStock = DB::table('stocks')->where('quantity', 0)->count();
        $totalValue    = DB::table('stocks')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->selectRaw('COALESCE(SUM(products.price * stocks.quantity), 0) as total')
            ->value('total');

        return response()->json([
            'products'       => $products,
            'total_products' => Product::count(),
            'low_stock'      => $lowStock,
            'critical_stock' => $criticalStock,
            'total_value'    => (float) $totalValue,
            'branches'       => Branch::select('id', 'name')->get(),
        ]);
    }

    /** GET /api/stocks/{productId}/history */
    public function history(Request $request, int $productId)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        }

        $mutations = StockMutation::with(['user'])
            ->where('product_id', $productId)
            ->orderByDesc('created_at')
            ->paginate(20);

        $data = $mutations->through(function ($m) {
            return [
                'id'           => $m->id,
                'date'         => $m->created_at->format('d/m/Y H:i'),
                'type'         => $m->type,
                'quantity'     => $m->quantity,
                'before_stock' => $m->before_stock,
                'after_stock'  => $m->after_stock,
                'note'         => $m->note ?? '-',
                'user'         => $m->user?->name ?? 'Sistem',
            ];
        });

        return response()->json([
            'product_name' => $product->name,
            'mutations'    => $data,
        ]);
    }

    /** POST /api/stocks/adjust */
    public function adjust(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:add,reduce',
            'quantity'   => 'required|integer|min:1',
            'note'       => 'nullable|string|max:255',
        ]);

        $stock = Stock::where('product_id', $request->product_id)->first();

        if (!$stock) {
            return response()->json(['message' => 'Data stok tidak ditemukan.'], 404);
        }

        $beforeStock = $stock->quantity;

        if ($request->type === 'add') {
            $stock->quantity += $request->quantity;
        } else {
            if ($stock->quantity < $request->quantity) {
                return response()->json(['message' => 'Stok tidak mencukupi untuk dikurangi.'], 422);
            }
            $stock->quantity -= $request->quantity;
        }

        $stock->updated_at = now();
        $stock->save();

        // Catat mutasi
        StockMutation::create([
            'product_id'   => $request->product_id,
            'branch_id'    => $stock->branch_id,
            'user_id'      => $request->user()?->id,
            'type'         => $request->type === 'add' ? 'in' : 'out',
            'quantity'     => $request->quantity,
            'before_stock' => $beforeStock,
            'after_stock'  => $stock->quantity,
            'note'         => $request->note,
        ]);

        $product = Product::with(['branch', 'stock'])->find($request->product_id);

        return response()->json([
            'message' => 'Stok berhasil diperbarui.',
            'product' => $this->formatStock($product),
        ]);
    }

    /** POST /api/stocks */
    public function store(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:0',
            'min_stock'  => 'nullable|integer|min:0',
            'note'       => 'nullable|string|max:255',
        ]);

        $productModel = Product::findOrFail($request->product_id);

        $existingStock = Stock::where('product_id', $request->product_id)->first();
        $beforeStock   = $existingStock?->quantity ?? 0;

        $stock = Stock::updateOrCreate(
            ['product_id' => $request->product_id],
            [
                'branch_id'  => $productModel->branch_id,
                'quantity'   => $request->quantity,
                'min_stock'  => $request->min_stock ?? 10,
                'updated_at' => now(),
            ]
        );

        // Catat mutasi jika ada perubahan
        if ($request->quantity != $beforeStock) {
            $diff = $request->quantity - $beforeStock;
            StockMutation::create([
                'product_id'   => $request->product_id,
                'branch_id'    => $productModel->branch_id,
                'user_id'      => $request->user()?->id,
                'type'         => $diff > 0 ? 'in' : 'out',
                'quantity'     => abs($diff),
                'before_stock' => $beforeStock,
                'after_stock'  => $request->quantity,
                'note'         => $request->note ?? 'Tambah stok manual',
            ]);
        }

        $product = Product::with(['branch', 'stock'])->find($request->product_id);

        return response()->json([
            'message' => 'Stok berhasil ditambahkan.',
            'product' => $this->formatStock($product),
        ]);
    }

    private function formatStock(Product $p): array
    {
        $stock = $p->stock;
        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'category'    => $p->category,
            'price'       => (float) $p->price,
            'branch_id'   => $p->branch_id,
            'branch_name' => $p->branch?->name ?? '-',
            'stock'       => $stock?->quantity ?? 0,
            'min_stock'   => $stock?->min_stock ?? 10,
            'updated_at'  => $stock?->updated_at ? \Carbon\Carbon::parse($stock->updated_at)->format('d/m/Y H:i') : '-',
        ];
    }
}
