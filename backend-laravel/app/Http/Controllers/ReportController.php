<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\FinancialReport;
use App\Models\Setting;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * GET /api/reports
     * Query params: branch_id, period (today|week|month|custom), start_date, end_date
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        [$start, $end] = $this->resolveDateRange($request);

        // Ambil data dari tabel shifts (sumber data real transaksi)
        $query = Shift::with(['branch', 'user'])
            ->whereNotNull('closed_at')
            ->whereBetween('closed_at', [$start, $end]);

        if ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        $shifts = $query->get();

        // Group per tanggal + cabang
        $grouped = [];
        foreach ($shifts as $shift) {
            $date      = Carbon::parse($shift->closed_at)->toDateString();
            $branchId  = $shift->branch_id;
            $key       = "{$date}_{$branchId}";

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'date'         => $date,
                    'branch_id'    => $branchId,
                    'branch_name'  => $shift->branch?->name ?? '-',
                    'revenue'      => 0,
                    'transactions' => 0,
                ];
            }
            $grouped[$key]['revenue']      += (float) $shift->total_sales;
            $grouped[$key]['transactions'] += (int)   $shift->total_transactions;
        }

        // Ambil margin dari settings DB (default 25%)
        $marginPercent = (float) Setting::get('profit_margin', 25);
        $marginRate    = $marginPercent / 100;

        // Hitung average & profit
        $reports = array_values(array_map(function ($r) use ($marginRate) {
            $avg    = $r['transactions'] > 0 ? round($r['revenue'] / $r['transactions']) : 0;
            $profit = round($r['revenue'] * $marginRate);
            return array_merge($r, [
                'average' => $avg,
                'profit'  => $profit,
            ]);
        }, $grouped));

        // Sort by date desc
        usort($reports, fn($a, $b) => strcmp($b['date'], $a['date']));

        // Summary total
        $totalRevenue      = array_sum(array_column($reports, 'revenue'));
        $totalTransactions = array_sum(array_column($reports, 'transactions'));
        $netProfit         = array_sum(array_column($reports, 'profit'));
        $avgTransaction    = $totalTransactions > 0
            ? round($totalRevenue / $totalTransactions)
            : 0;

        // Daftar cabang untuk filter dropdown
        $branches = Branch::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'reports'           => $reports,
            'summary' => [
                'total_revenue'       => $totalRevenue,
                'total_transactions'  => $totalTransactions,
                'net_profit'          => $netProfit,
                'average_transaction' => $avgTransaction,
                'margin_percent'      => $marginPercent,
            ],
            'branches' => $branches,
        ]);
    }

    private function resolveDateRange(Request $request): array
    {
        $period = $request->get('period', 'week');
        $today  = Carbon::today();

        switch ($period) {
            case 'today':
                return [$today->startOfDay()->copy(), $today->endOfDay()->copy()];

            case 'month':
                return [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ];

            case 'custom':
                $start = $request->filled('start_date')
                    ? Carbon::parse($request->start_date)->startOfDay()
                    : $today->copy()->subDays(7)->startOfDay();
                $end   = $request->filled('end_date')
                    ? Carbon::parse($request->end_date)->endOfDay()
                    : $today->copy()->endOfDay();
                return [$start, $end];

            case 'week':
            default:
                return [
                    Carbon::now()->startOfWeek(Carbon::MONDAY),
                    Carbon::now()->endOfWeek(Carbon::SUNDAY),
                ];
        }
    }
}
