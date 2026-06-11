<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    private function checkOwner(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        return null;
    }

    /**
     * GET /api/shifts
     * Query params: branch_id, date_from, date_to, status
     */
    public function index(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $query = Shift::with(['branch', 'user'])
            ->orderByDesc('opened_at');

        if ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('opened_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('opened_at', '<=', $request->date_to);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $shifts = $query->paginate(20)->through(function ($s) {
            return [
                'id'                => $s->id,
                'branch_name'       => $s->branch?->name ?? '-',
                'cashier_name'      => $s->user?->name ?? '-',
                'opening_cash'      => (float) $s->opening_cash,
                'closing_cash'      => (float) $s->closing_cash,
                'expected_cash'     => (float) $s->expected_cash,
                'difference'        => (float) $s->difference,
                'total_sales'       => (float) $s->total_sales,
                'total_transactions'=> (int)   $s->total_transactions,
                'status'            => $s->status,
                'opened_at'         => $s->opened_at?->format('Y-m-d H:i'),
                'closed_at'         => $s->closed_at?->format('Y-m-d H:i'),
            ];
        });

        // Summary hari ini
        $today = Carbon::today();
        $todayShifts = Shift::whereDate('opened_at', $today);

        $summary = [
            'total_shifts_today'       => (clone $todayShifts)->count(),
            'active_shifts'            => (clone $todayShifts)->where('status', 'aktif')->count(),
            'total_sales_today'        => (float) (clone $todayShifts)->sum('total_sales'),
            'total_difference_today'   => (float) (clone $todayShifts)->sum('difference'),
        ];

        $branches = Branch::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'shifts'   => $shifts,
            'summary'  => $summary,
            'branches' => $branches,
        ]);
    }
}
