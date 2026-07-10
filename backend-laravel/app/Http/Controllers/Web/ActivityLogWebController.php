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
        $logs = $this->filteredQuery($request)->latest('created_at')->paginate(20)->withQueryString();

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

    /**
     * Query dengan filter user/branch/action/tanggal/search — dipakai
     * bareng oleh index() (tampilan berpaginasi) dan exportCsv() (dump
     * semua baris tanpa paginasi) supaya definisi filter TIDAK PERNAH bisa
     * drift antara dua tempat (mis. lupa update salah satu kalau nanti ada
     * filter baru ditambah).
     */
    private function filteredQuery(Request $request)
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

        return $query;
    }

    /**
     * Export CSV dari log yang SEDANG terfilter (bukan selalu dump semua
     * baris) — supaya Owner yang lagi menyaring mis. satu cabang / satu
     * jenis aksi / satu rentang tanggal bisa langsung dapat file yang
     * sudah relevan, tanpa perlu saring ulang manual di Excel.
     *
     * Di-stream langsung ke response (bukan dikumpulkan ke variabel dulu)
     * supaya tidak membebani memori kalau riwayat aktivitasnya sudah
     * ribuan/puluhan-ribu baris.
     */
    public function exportCsv(Request $request)
    {
        $filename = 'riwayat-aktivitas-'.now()->format('Y-m-d_His').'.csv';

        $callback = function () use ($request) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 supaya Excel di Windows tidak salah baca karakter
            // non-ASCII (mis. "Rp", nama produk dengan aksen) sebagai encoding lain.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Waktu', 'User', 'Role', 'Cabang', 'Aksi', 'Deskripsi', 'IP Address']);

            $this->filteredQuery($request)
                ->latest('created_at')
                ->chunk(500, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->created_at?->format('Y-m-d H:i:s'),
                            $log->user?->name ?? '-',
                            $log->user?->role ?? '-',
                            $log->branch?->name ?? '-',
                            $log->action_label,
                            $log->description,
                            $log->ip_address ?? '-',
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
