<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OwnerWebController extends Controller
{
    // ─── Dashboard ──────────────────────────────────────────────────
    public function dashboard()
    {
        $stats = [
            'total_penjualan' => Transaction::whereDate('created_at', today())->sum('total'),
            'total_transaksi' => Transaction::whereDate('created_at', today())->count(),
            'total_produk'    => Product::count(),
            'total_cabang'    => Branch::count(),
        ];

        $transaksi_terbaru = Transaction::with(['kasir', 'branch'])
            ->whereDate('created_at', today())
            ->latest()->limit(10)->get();

        $stok_menipis = Stock::with('product')
            ->whereColumn('quantity', '<=', 'min_stock')->get();

        return view('owner.dashboard', compact('stats', 'transaksi_terbaru', 'stok_menipis'));
    }

    // ─── Products ───────────────────────────────────────────────────
    public function products(Request $request)
    {
        $today    = now()->toDateString();
        $soonDate = now()->addDays(7)->toDateString();

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

        $products      = $query->orderBy('name')->paginate(15);
        $branches      = Branch::select('id', 'name')->get();
        $expiring_soon = Product::whereBetween('expired_date', [$today, $soonDate])->count();
        $expired_count = Product::where('expired_date', '<', $today)->count();
        $low_stock     = Stock::whereColumn('quantity', '<=', 'min_stock')->count();

        return view('owner.products', compact('products', 'branches', 'expiring_soon', 'expired_count', 'low_stock'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'category'     => ['required', Rule::in(['Frozen', 'Snack', 'Dessert', 'Minuman', 'Lainnya'])],
            'price'        => 'required|numeric|min:0',
            'expired_date' => 'required|date',
            'branch_id'    => 'required|exists:branches,id',
            'stock'        => 'required|integer|min:0',
            'min_stock'    => 'nullable|integer|min:0',
        ]);

        $product = Product::create($request->only('name', 'category', 'price', 'expired_date', 'branch_id'));

        Stock::create([
            'product_id' => $product->id,
            'branch_id'  => $request->branch_id,
            'quantity'   => $request->stock,
            'min_stock'  => $request->min_stock ?? 10,
        ]);

        return redirect()->route('owner.products')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'category'     => ['required', Rule::in(['Frozen', 'Snack', 'Dessert', 'Minuman', 'Lainnya'])],
            'price'        => 'required|numeric|min:0',
            'expired_date' => 'required|date',
            'branch_id'    => 'required|exists:branches,id',
            'stock'        => 'required|integer|min:0',
            'min_stock'    => 'nullable|integer|min:0',
        ]);

        $product->update($request->only('name', 'category', 'price', 'expired_date', 'branch_id'));

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

        return redirect()->route('owner.products')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroyProduct(Product $product)
    {
        $name = $product->name;
        $product->stock()->delete();
        $product->delete();

        return redirect()->route('owner.products')->with('success', "{$name} berhasil dihapus.");
    }

    // ─── Stocks ─────────────────────────────────────────────────────
    public function stocks()
    {
        $stocks = Stock::with(['product', 'branch'])->latest()->paginate(15);
        return view('owner.stocks', compact('stocks'));
    }

    // ─── Reports ────────────────────────────────────────────────────
    public function reports()
    {
        return view('owner.reports');
    }

    // ─── Branches ───────────────────────────────────────────────────
    public function branches()
    {
        $branches = Branch::withCount('users')->latest()->get();
        return view('owner.branches', compact('branches'));
    }

    // ─── Shifts ─────────────────────────────────────────────────────
    public function shifts()
    {
        $shifts = Shift::with(['user', 'branch'])->latest()->paginate(15);
        return view('owner.shifts', compact('shifts'));
    }

    // ─── Settings ───────────────────────────────────────────────────
    public function settings()
    {
        return view('owner.settings');
    }
}