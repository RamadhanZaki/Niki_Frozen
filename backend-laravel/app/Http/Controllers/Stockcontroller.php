<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
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

        // Stats pakai DB langsung — hindari whereColumn di Eloquent
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

    /** POST /api/stocks/adjust */
    public function adjust(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:add,reduce',
            'quantity'   => 'required|integer|min:1',
        ]);

        $stock = Stock::where('product_id', $request->product_id)->first();

        if (!$stock) {
            return response()->json(['message' => 'Data stok tidak ditemukan.'], 404);
        }

        if ($request->type === 'add') {
            $stock->quantity += $request->quantity;
        } else {
            if ($stock->quantity < $request->quantity) {
                return response()->json(['message' => 'Stok tidak mencukupi untuk dikurangi.'], 422);
            }
            $stock->quantity -= $request->quantity;
        }

        // Update manual karena $timestamps = false
        $stock->updated_at = now();
        $stock->save();

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
        ]);

        $productModel = Product::findOrFail($request->product_id);

        $stock = Stock::updateOrCreate(
            ['product_id' => $request->product_id],
            [
                'branch_id'  => $productModel->branch_id,
                'quantity'   => $request->quantity,
                'min_stock'  => $request->min_stock ?? 10,
                'updated_at' => now(),
            ]
        );

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
