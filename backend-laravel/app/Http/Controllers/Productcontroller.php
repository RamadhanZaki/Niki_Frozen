<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ProductController extends Controller
{
    private function checkOwner(Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'owner') {
            return response()->json(['message' => 'Akses ditolak. Hanya owner yang diizinkan.'], 403);
        }
        return null;
    }

    /**
     * GET /api/products
     */
    public function index(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $query = Product::with(['branch', 'stock']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        if ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        $products = $query->orderBy('name')->get()
            ->map(fn($p) => $this->formatProduct($p));

        // Filter status di PHP karena butuh kalkulasi days_left
        if ($request->filled('status') && $request->status !== 'all') {
            $products = $products->filter(fn($p) => match ($request->status) {
                'low_stock' => $p['is_low_stock'] && !$p['is_expired'],
                'expiring'  => $p['is_expiring']  && !$p['is_expired'],
                'expired'   => $p['is_expired'],
                'normal'    => !$p['is_low_stock'] && !$p['is_expiring'] && !$p['is_expired'] && $p['stock'] > 0,
                default     => true,
            })->values();
        }

        // Statistik dari SEMUA produk (tanpa filter)
        $all = Product::with('stock')->get()->map(fn($p) => $this->formatProduct($p));

        return response()->json([
            'products'       => $products,
            'total_products' => $all->count(),
            'low_stock'      => $all->filter(fn($p) => $p['is_low_stock'] && !$p['is_expired'])->count(),
            'expiring'       => $all->filter(fn($p) => $p['is_expiring']  && !$p['is_expired'])->count(),
            'expired'        => $all->filter(fn($p) => $p['is_expired'])->count(),
            'branches'       => Branch::select('id', 'name')->get(),
        ]);
    }

    /**
     * POST /api/products
     */
    public function store(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $request->validate([
            'name'         => 'required|string|max:150',
            'category'     => ['required', Rule::in(['Frozen', 'Snack', 'Dessert', 'Minuman', 'Lainnya'])],
            'price'        => 'required|numeric|min:1',
            'expired_date' => 'required|date',
            'branch_id'    => 'required|exists:branches,id',
            'stock'        => 'nullable|integer|min:0',
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
            'quantity'   => $request->stock    ?? 0,
            'min_stock'  => $request->min_stock ?? 10,
            'updated_at' => now(),
        ]);

        $product->load(['branch', 'stock']);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'product' => $this->formatProduct($product),
        ], 201);
    }

    /**
     * PUT /api/products/{product}
     */
    public function update(Request $request, Product $product)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $request->validate([
            'name'         => 'required|string|max:150',
            'category'     => ['required', Rule::in(['Frozen', 'Snack', 'Dessert', 'Minuman', 'Lainnya'])],
            'price'        => 'required|numeric|min:1',
            'expired_date' => 'required|date',
            'branch_id'    => 'required|exists:branches,id',
            'stock'        => 'nullable|integer|min:0',
            'min_stock'    => 'nullable|integer|min:0',
        ]);

        $product->update([
            'name'         => $request->name,
            'category'     => $request->category,
            'price'        => $request->price,
            'expired_date' => $request->expired_date,
            'branch_id'    => $request->branch_id,
        ]);

        if ($product->stock) {
            $product->stock->update([
                'quantity'   => $request->stock    ?? $product->stock->quantity,
                'min_stock'  => $request->min_stock ?? $product->stock->min_stock,
                'updated_at' => now(),
            ]);
        } else {
            Stock::create([
                'product_id' => $product->id,
                'branch_id'  => $request->branch_id,
                'quantity'   => $request->stock    ?? 0,
                'min_stock'  => $request->min_stock ?? 10,
                'updated_at' => now(),
            ]);
        }

        $product->load(['branch', 'stock']);

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'product' => $this->formatProduct($product),
        ]);
    }

    /**
     * DELETE /api/products/{product}
     */
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
     * Helper format produk
     */
    private function formatProduct(Product $product): array
    {
        $quantity  = $product->stock?->quantity  ?? 0;
        $minStock  = $product->stock?->min_stock ?? 10;
        $expDate   = $product->expired_date ? Carbon::parse($product->expired_date) : null;
        $daysLeft  = $expDate ? Carbon::today()->diffInDays($expDate, false) : null;

        return [
            'id'           => $product->id,
            'name'         => $product->name,
            'category'     => $product->category,
            'price'        => (float) $product->price,
            'expired_date' => $product->expired_date?->format('Y-m-d'),
            'branch_id'    => $product->branch_id,
            'branch_name'  => $product->branch?->name ?? '-',
            'stock'        => $quantity,
            'min_stock'    => $minStock,
            'days_left'    => $daysLeft,
            'is_low_stock' => $quantity > 0 && $quantity <= $minStock,
            'is_expiring'  => $daysLeft !== null && $daysLeft > 0 && $daysLeft <= 7,
            'is_expired'   => $daysLeft !== null && $daysLeft <= 0,
        ];
    }
}
