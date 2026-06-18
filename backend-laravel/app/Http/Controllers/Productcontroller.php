<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private function checkOwner(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Akses ditolak. Hanya owner yang diizinkan.'], 403);
        }
        return null;
    }

    /** GET /api/products */
    public function index(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $query = Product::with(['branch', 'stock']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $products = $query->orderBy('name')->get()->map(fn($p) => $this->formatProduct($p));

        $today = now()->toDateString();
        $soonDate = now()->addDays(7)->toDateString();

        return response()->json([
            'products'       => $products,
            'total_products' => Product::count(),
            'expiring_soon'  => Product::whereBetween('expired_date', [$today, $soonDate])->count(),
            'expired_count'  => Product::where('expired_date', '<', $today)->count(),
            'low_stock'      => Stock::whereColumn('quantity', '<=', 'min_stock')->count(),
            'branches'       => Branch::select('id', 'name')->get(),
        ]);
    }

    /** POST /api/products */
    public function store(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $request->validate([
            'name'         => 'required|string|max:150',
            'category'     => ['required', Rule::in(['Frozen', 'Snack', 'Dessert', 'Minuman', 'Lainnya'])],
            'price'        => 'required|numeric|min:0',
            'expired_date' => 'required|date',
            'branch_id'    => 'required|exists:branches,id',
            'stock'        => 'required|integer|min:0',
            'min_stock'    => 'nullable|integer|min:0',
        ]);

        $product = Product::create([
            'name'         => $request->name,
            'category'     => $request->category,
            'price'        => $request->price,
            'expired_date' => $request->expired_date,
            'branch_id'    => $request->branch_id,
        ]);

        Stock::create([
            'product_id' => $product->id,
            'branch_id'  => $request->branch_id,
            'quantity'   => $request->stock,
            'min_stock'  => $request->min_stock ?? 10,
        ]);

        $product->load(['branch', 'stock']);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'product' => $this->formatProduct($product),
        ], 201);
    }

    /** PUT /api/products/{product} */
    public function update(Request $request, Product $product)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $request->validate([
            'name'         => 'required|string|max:150',
            'category'     => ['required', Rule::in(['Frozen', 'Snack', 'Dessert', 'Minuman', 'Lainnya'])],
            'price'        => 'required|numeric|min:0',
            'expired_date' => 'required|date',
            'branch_id'    => 'required|exists:branches,id',
            'stock'        => 'required|integer|min:0',
            'min_stock'    => 'nullable|integer|min:0',
        ]);

        $product->update([
            'name'         => $request->name,
            'category'     => $request->category,
            'price'        => $request->price,
            'expired_date' => $request->expired_date,
            'branch_id'    => $request->branch_id,
        ]);

        $stock = $product->stock;
        if ($stock) {
            $stock->update([
                'branch_id' => $request->branch_id,
                'quantity'  => $request->stock,
                'min_stock' => $request->min_stock ?? $stock->min_stock,
            ]);
        } else {
            Stock::create([
                'product_id' => $product->id,
                'branch_id'  => $request->branch_id,
                'quantity'   => $request->stock,
                'min_stock'  => $request->min_stock ?? 10,
            ]);
        }

        $product->load(['branch', 'stock']);

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'product' => $this->formatProduct($product),
        ]);
    }

    /** DELETE /api/products/{product} */
    public function destroy(Request $request, Product $product)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $name = $product->name;
        $product->stock()->delete();
        $product->delete();

        return response()->json(['message' => "{$name} berhasil dihapus."]);
    }


    /**
     * GET /api/cashier/products
     * Daftar produk + stok untuk kasir — hanya cabang kasir yang login
     */
    public function forCashier(Request $request)
    {
        $user = $request->user();

        $query = Product::with('stock')
            ->where('branch_id', $user->branch_id);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('name')->get()->map(function ($p) {
            return [
                'id'           => $p->id,
                'name'         => $p->name,
                'category'     => $p->category,
                'price'        => (float) $p->price,
                'expired_date' => $p->expired_date?->format('Y-m-d'),
                'stock'        => $p->stock?->quantity ?? 0,
                'min_stock'    => $p->stock?->min_stock ?? 10,
            ];
        });

        return response()->json(['products' => $products]);
    }

        private function formatProduct(Product $p): array
    {
        $today    = \Carbon\Carbon::today();
        $expired  = $p->expired_date ? \Carbon\Carbon::parse($p->expired_date) : null;
        // positif = belum expired, negatif = sudah expired
        $daysLeft = $expired ? $today->diffInDays($expired, false) : null;

        return [
            'id'           => $p->id,
            'name'         => $p->name,
            'category'     => $p->category,
            'price'        => (float) $p->price,
            'expired_date' => $p->expired_date?->format('Y-m-d'),
            'days_left'    => $daysLeft !== null ? (int) $daysLeft : null,
            'branch_id'    => $p->branch_id,
            'branch_name'  => $p->branch?->name ?? '-',
            'stock'        => $p->stock?->quantity ?? 0,
            'min_stock'    => $p->stock?->min_stock ?? 10,
            'created_at'   => $p->created_at?->format('d/m/Y'),
        ];
    }
}