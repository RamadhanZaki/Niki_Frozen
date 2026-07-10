<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogWebController extends Controller
{
    /**
     * Halaman Riwayat Aktivitas (audit log) — khusus Owner. Menampilkan
     * seluruh jejak aksi penting (login/logout, CRUD produk/cabang/user/
     * diskon, penyesuaian stok, buka/tutup shift, perubahan pengaturan)
     * dari SEMUA user, dengan filter per user, per jenis aksi, per cabang,
     * dan rentang tanggal.
     *
     * Sengaja TIDAK dibuat polling realtime seperti halaman lain (dashboard/
     * stocks/shifts) — riwayat aktivitas biasanya ditinjau sesekali (bukan
     * dipelototi terus-menerus), dan menghindari query filter berat
     * (beberapa where + join user/branch) berjalan berulang tiap 15 detik
     * tanpa manfaat nyata. Owner cukup refresh manual kalau butuh data
     * terbaru.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'branch']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('start')) {
            $query->where('created_at', '>=', $request->start.' 00:00:00');
        }

        if ($request->filled('end')) {
            $query->where('created_at', '<=', $request->end.' 23:59:59');
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->search.'%');
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        // Daftar aksi yang PERNAH tercatat (bukan daftar statis) — supaya
        // dropdown filter otomatis menyesuaikan kalau nanti ada jenis aksi
        // baru ditambah di kode, tanpa perlu diedit manual di sini.
        $availableActions = ActivityLog::select('action')
            ->distinct()
            ->pluck('action')
            ->mapWithKeys(fn ($action) => [$action => (new ActivityLog(['action' => $action]))->action_label])
            ->sort()
            ->all();

        $users    = User::orderBy('name')->select('id', 'name', 'role')->get();
        $branches = Branch::orderBy('name')->select('id', 'name')->get();

        return view('owner.activity-logs', compact('logs', 'availableActions', 'users', 'branches'));
    }
}
