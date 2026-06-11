<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function checkOwner(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        return null;
    }

    /** GET /api/dashboard */
    public function index(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $today     = \Carbon\Carbon::today();
        $soonDate  = \Carbon\Carbon::today()->addDays(7);

        // ── Stats ringkasan ──────────────────────────────────────
        $totalRevenue      = (float) Shift::whereDate('closed_at', $today)->sum('total_sales');
        $totalTransactions = (int)   Shift::whereDate('closed_at', $today)->sum('total_transactions');
        $expiringProducts  = Product::whereBetween('expired_date', [$today, $soonDate])->count();
        $totalDifference   = (float) Shift::whereDate('closed_at', $today)
                                ->whereNotNull('difference')->sum('difference');

        // ── Performa cabang hari ini ─────────────────────────────
        $branches = Branch::with(['shifts' => function ($q) use ($today) {
            $q->whereDate('closed_at', $today);
        }])->get()->map(function ($b) {
            $shifts     = $b->shifts;
            $revenue    = (float) $shifts->sum('total_sales');
            $trx        = (int)   $shifts->sum('total_transactions');
            $avg        = $trx > 0 ? round($revenue / $trx) : 0;
            $difference = (float) $shifts->sum('difference');

            return [
                'id'          => $b->id,
                'name'        => $b->name,
                'revenue'     => $revenue,
                'transactions'=> $trx,
                'average'     => $avg,
                'difference'  => $difference,
                'status'      => 'active',
            ];
        });

        // ── Produk hampir expired (≤7 hari) ─────────────────────
        $expiredProductsList = Product::with('branch')
            ->where('expired_date', '>=', $today)
            ->where('expired_date', '<=', $soonDate)
            ->orderBy('expired_date')
            ->limit(5)
            ->get()
            ->map(function ($p) use ($today) {
                $daysLeft = $today->diffInDays(\Carbon\Carbon::parse($p->expired_date), false);
                return [
                    'id'           => $p->id,
                    'name'         => $p->name,
                    'branch_name'  => $p->branch?->name ?? '-',
                    'expired_date' => $p->expired_date->format('Y-m-d'),
                    'days_left'    => (int) $daysLeft,
                ];
            });

        // ── Selisih kas shift hari ini ───────────────────────────
        $cashDifferencesList = Shift::with(['branch', 'user'])
            ->whereDate('closed_at', $today)
            ->whereNotNull('difference')
            ->where('difference', '!=', 0)
            ->orderByDesc('closed_at')
            ->limit(5)
            ->get()
            ->map(function ($s) {
                return [
                    'id'           => $s->id,
                    'branch_name'  => $s->branch?->name ?? '-',
                    'shift_date'   => $s->closed_at?->format('d/m/Y'),
                    'cashier_name' => $s->user?->name ?? '-',
                    'difference'   => (float) $s->difference,
                    'time_ago'     => $s->closed_at?->diffForHumans(),
                ];
            });

        return response()->json([
            'total_revenue'        => $totalRevenue,
            'total_transactions'   => $totalTransactions,
            'expiring_products'    => $expiringProducts,
            'total_difference'     => $totalDifference,
            'branches'             => $branches,
            'expired_products_list'=> $expiredProductsList,
            'cash_differences_list'=> $cashDifferencesList,
        ]);
    }
}
