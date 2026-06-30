<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OwnerWebController extends Controller
{
    // ─── Dashboard ──────────────────────────────────────────────────
    public function dashboard()
    {
        $today    = now()->toDateString();
        $soonDate = now()->addDays(7)->toDateString();

        $stats = [
            'total_penjualan'    => Transaction::whereDate('created_at', today())->sum('total'),
            'total_transaksi'    => Transaction::whereDate('created_at', today())->count(),
            'total_produk'       => Product::count(),
            'total_cabang'       => Branch::count(),
            'produk_kadaluarsa'  => Product::whereBetween('expired_date', [$today, $soonDate])->count(),
            'stok_menipis'       => Stock::whereColumn('quantity', '<=', 'min_stock')->count(),
            'total_stok'         => Stock::sum('quantity'),
            'transfer_stok'      => StockMutation::count(),
            'transfer_hari_ini'  => StockMutation::whereDate('created_at', today())->count(),
        ];

        $transaksi_terbaru = Transaction::with(['user', 'branch'])
            ->whereDate('created_at', today())
            ->latest()->limit(10)->get();

        $stok_menipis = Stock::with('product')
            ->whereColumn('quantity', '<=', 'min_stock')->get();

        // ── Revenue 90 hari terakhir untuk grafik ──
        $start90 = now()->subDays(89)->startOfDay();
        $rows = Transaction::where('created_at', '>=', $start90)
            ->selectRaw('DATE(created_at) as tanggal, SUM(total) as total')
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal');

        $revenue_labels = [];
        $revenue_data   = [];
        for ($d = $start90->copy(); $d <= now(); $d->addDay()) {
            $key = $d->toDateString();
            $revenue_labels[] = $d->format('d M');
            $revenue_data[]   = (float) ($rows[$key] ?? 0);
        }
        $total_revenue_90 = array_sum($revenue_data);

        // ── Distribusi kategori produk untuk donut chart ──
        $kategori_raw = Product::selectRaw('category, COUNT(*) as jumlah')
            ->groupBy('category')->pluck('jumlah', 'category');
        $total_kategori = $kategori_raw->sum();
        $kategori_produk = $kategori_raw->map(function ($jumlah, $kategori) use ($total_kategori) {
            return [
                'label'  => $kategori,
                'jumlah' => $jumlah,
                'persen' => $total_kategori > 0 ? round($jumlah / $total_kategori * 100) : 0,
            ];
        })->values();

        return view('owner.dashboard', compact(
            'stats', 'transaksi_terbaru', 'stok_menipis',
            'revenue_labels', 'revenue_data', 'total_revenue_90',
            'kategori_produk'
        ));
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

        // Gabungan kategori default + kategori unik yang sudah pernah diinput manual
        $categories = collect(['Frozen', 'Snack', 'Dessert', 'Minuman', 'Lainnya'])
            ->merge(Product::select('category')->distinct()->pluck('category'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('owner.products', compact('products', 'branches', 'expiring_soon', 'expired_count', 'low_stock', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'category'     => 'required|string|max:50',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'price'        => 'required|numeric|min:0',
            'expired_date' => 'required|date',
            'branch_id'    => 'required|exists:branches,id',
            'stock'        => 'required|integer|min:0',
            'min_stock'    => 'nullable|integer|min:0',
        ]);

        $data = $request->only('name', 'category', 'price', 'expired_date', 'branch_id');
        $data['category'] = trim($data['category']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

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
            'category'     => 'required|string|max:50',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'price'        => 'required|numeric|min:0',
            'expired_date' => 'required|date',
            'branch_id'    => 'required|exists:branches,id',
            'stock'        => 'required|integer|min:0',
            'min_stock'    => 'nullable|integer|min:0',
        ]);

        $data = $request->only('name', 'category', 'price', 'expired_date', 'branch_id');
        $data['category'] = trim($data['category']);

        if ($request->hasFile('image')) {
            // Hapus gambar lama kalau ada, lalu simpan yang baru
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

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

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->stock()->delete();
        $product->delete();

        return redirect()->route('owner.products')->with('success', "{$name} berhasil dihapus.");
    }

    // ─── Stocks ─────────────────────────────────────────────────────
    public function stocks(Request $request)
    {
        $query = Product::with(['branch', 'stock']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('stock_filter')) {
            $query->whereHas('stock', function ($q) use ($request) {
                if ($request->stock_filter === 'critical') {
                    $q->where('quantity', 0);
                } elseif ($request->stock_filter === 'low') {
                    $q->whereColumn('quantity', '<=', 'min_stock')->where('quantity', '>', 0);
                } elseif ($request->stock_filter === 'normal') {
                    $q->whereColumn('quantity', '>', 'min_stock');
                }
            });
        }

        $stocks   = $query->orderBy('name')->paginate(15)->withQueryString();
        $branches = Branch::select('id', 'name')->get();

        $total_products = Product::count();
        $low_stock      = Stock::whereColumn('quantity', '<=', 'min_stock')->where('quantity', '>', 0)->count();
        $critical_stock = Stock::where('quantity', 0)->count();
        $total_value    = Product::join('stocks', 'stocks.product_id', '=', 'products.id')
            ->selectRaw('SUM(products.price * stocks.quantity) as total')
            ->value('total') ?? 0;

        return view('owner.stocks', compact(
            'stocks', 'branches', 'total_products', 'low_stock', 'critical_stock', 'total_value'
        ));
    }

    public function adjustStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:add,reduce',
            'quantity'   => 'required|integer|min:1',
            'note'       => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);
        $stock   = $product->stock;

        if (!$stock) {
            $stock = Stock::create([
                'product_id' => $product->id,
                'branch_id'  => $product->branch_id,
                'quantity'   => 0,
                'min_stock'  => 10,
            ]);
        }

        $before = $stock->quantity;

        if ($request->type === 'add') {
            $stock->quantity += $request->quantity;
        } else {
            if ($request->quantity > $stock->quantity) {
                return back()->with('error', 'Jumlah pengurangan melebihi stok yang tersedia.');
            }
            $stock->quantity -= $request->quantity;
        }

        $stock->updated_at = now();
        $stock->save();

        StockMutation::create([
            'product_id'   => $product->id,
            'branch_id'    => $stock->branch_id,
            'user_id'      => auth()->id(),
            'type'         => $request->type === 'add' ? 'in' : 'out',
            'quantity'     => $request->quantity,
            'before_stock' => $before,
            'after_stock'  => $stock->quantity,
            'note'         => $request->note,
        ]);

        return redirect()->route('owner.stocks')->with('success', 'Stok berhasil disesuaikan.');
    }

    // ─── Reports ────────────────────────────────────────────────────
    public function reports(Request $request)
    {
        $start = $request->filled('start') ? $request->start : now()->startOfMonth()->toDateString();
        $end   = $request->filled('end')   ? $request->end   : now()->toDateString();

        $summary = [
            'total_penjualan'   => Transaction::whereBetween('created_at', [$start, $end . ' 23:59:59'])->sum('total'),
            'total_transaksi'   => Transaction::whereBetween('created_at', [$start, $end . ' 23:59:59'])->count(),
            'rata_rata'         => Transaction::whereBetween('created_at', [$start, $end . ' 23:59:59'])->avg('total') ?? 0,
        ];

        $penjualan_harian = Transaction::whereBetween('created_at', [$start, $end . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as tanggal, SUM(total) as total, COUNT(*) as jumlah')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $produk_terlaris = TransactionDetail::with('product')
            ->whereHas('transaction', function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end . ' 23:59:59']);
            })
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_omzet')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $penjualan_per_cabang = Transaction::with('branch')
            ->whereBetween('created_at', [$start, $end . ' 23:59:59'])
            ->selectRaw('branch_id, SUM(total) as total, COUNT(*) as jumlah')
            ->groupBy('branch_id')
            ->get();

        return view('owner.reports', compact(
            'summary', 'penjualan_harian', 'produk_terlaris', 'penjualan_per_cabang', 'start', 'end'
        ));
    }

    // ─── Branches ───────────────────────────────────────────────────
    public function branches()
    {
        $branches = Branch::withCount(['users', 'products'])->latest()->get();
        return view('owner.branches', compact('branches'));
    }

    public function storeBranch(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:20',
        ]);

        Branch::create($request->only('name', 'address', 'phone'));

        return redirect()->route('owner.branches')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:20',
        ]);

        $branch->update($request->only('name', 'address', 'phone'));

        return redirect()->route('owner.branches')->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroyBranch(Branch $branch)
    {
        if ($branch->users()->exists() || $branch->products()->exists()) {
            return back()->with('error', 'Cabang tidak dapat dihapus karena masih memiliki data terkait.');
        }

        $branch->delete();

        return redirect()->route('owner.branches')->with('success', 'Cabang berhasil dihapus.');
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
        $settings = [
            'store_name'    => Setting::get('store_name', 'Niki Frozen'),
            'store_address' => Setting::get('store_address', ''),
            'store_phone'   => Setting::get('store_phone', ''),
            'tax_percent'   => Setting::get('tax_percent', 0),
            'receipt_note'  => Setting::get('receipt_note', 'Terima kasih telah berbelanja!'),
        ];

        return view('owner.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'store_name'    => 'required|string|max:150',
            'store_address' => 'nullable|string',
            'store_phone'   => 'nullable|string|max:20',
            'tax_percent'   => 'nullable|numeric|min:0|max:100',
            'receipt_note'  => 'nullable|string|max:255',
        ]);

        foreach (['store_name', 'store_address', 'store_phone', 'tax_percent', 'receipt_note'] as $key) {
            Setting::set($key, $request->input($key));
        }

        return redirect()->route('owner.settings')->with('success', 'Pengaturan berhasil disimpan.');
    }

    // ─── Users (Kasir) ────────────────────────────────────────────────
    public function users(Request $request)
    {
        $query = User::with('branch')->where('role', 'kasir');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $users    = $query->orderBy('name')->paginate(15)->withQueryString();
        $branches = Branch::select('id', 'name')->get();

        $total_users     = User::where('role', 'kasir')->count();
        $active_cashiers = User::where('role', 'kasir')->where('status', 'aktif')->count();

        return view('owner.users', compact('users', 'branches', 'total_users', 'active_cashiers'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'branch_id' => 'nullable|exists:branches,id',
            'status'    => 'in:aktif,nonaktif',
        ]);

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'kasir',
            'branch_id' => $request->branch_id ?: null,
            'status'    => $request->status ?? 'aktif',
        ]);

        return redirect()->route('owner.users')->with('success', 'Kasir berhasil ditambahkan.');
    }

    public function updateUser(Request $request, User $user)
    {
        if ($user->role !== 'kasir') {
            return back()->with('error', 'Hanya akun kasir yang dapat diubah dari halaman ini.');
        }

        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'branch_id' => 'nullable|exists:branches,id',
            'status'    => 'in:aktif,nonaktif',
        ]);

        $user->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'branch_id' => $request->branch_id ?: null,
            'status'    => $request->status ?? $user->status,
        ]);

        return redirect()->route('owner.users')->with('success', 'Data kasir berhasil diperbarui.');
    }

    public function resetPasswordUser(Request $request, User $user)
    {
        if ($user->role !== 'kasir') {
            return back()->with('error', 'Hanya akun kasir yang dapat direset dari halaman ini.');
        }

        $request->validate([
            'password'              => 'required|string|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('owner.users')->with('success', "Password untuk {$user->name} berhasil direset.");
    }

    public function destroyUser(User $user)
    {
        if ($user->role !== 'kasir') {
            return back()->with('error', 'Hanya akun kasir yang dapat dihapus dari halaman ini.');
        }

        $name = $user->name;
        $user->tokens()->delete();
        $user->delete();

        return redirect()->route('owner.users')->with('success', "{$name} berhasil dihapus.");
    }
}