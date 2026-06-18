<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class CashierShiftController extends Controller
{
    /**
     * GET /api/cashier/shift/active
     * Cek apakah kasir punya shift aktif
     */
    public function active(Request $request)
    {
        $user = $request->user();

        $shift = Shift::where('user_id', $user->id)
            ->where('status', 'aktif')
            ->latest('opened_at')
            ->first();

        if (!$shift) {
            return response()->json(['shift' => null]);
        }

        return response()->json(['shift' => $this->format($shift)]);
    }

    /**
     * POST /api/cashier/shift/open
     * Buka shift baru
     */
    public function open(Request $request)
    {
        $user = $request->user();

        // Cek shift aktif yang belum ditutup
        $existing = Shift::where('user_id', $user->id)
            ->where('status', 'aktif')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Anda masih memiliki shift aktif.',
                'shift'   => $this->format($existing),
            ], 422);
        }

        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $shift = Shift::create([
            'user_id'      => $user->id,
            'branch_id'    => $user->branch_id,
            'opening_cash' => $request->opening_cash,
            'total_sales'  => 0,
            'total_transactions' => 0,
            'status'       => 'aktif',
            'opened_at'    => now(),
        ]);

        return response()->json([
            'message' => 'Shift berhasil dibuka.',
            'shift'   => $this->format($shift),
        ], 201);
    }

    /**
     * POST /api/cashier/shift/close
     * Tutup shift aktif
     */
    public function close(Request $request)
    {
        $user = $request->user();

        $shift = Shift::where('user_id', $user->id)
            ->where('status', 'aktif')
            ->latest('opened_at')
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'Tidak ada shift aktif.'], 404);
        }

        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
        ]);

        $expectedCash = $shift->opening_cash + $shift->total_sales;
        $difference   = $request->closing_cash - $expectedCash;

        $shift->update([
            'closing_cash'  => $request->closing_cash,
            'expected_cash' => $expectedCash,
            'difference'    => $difference,
            'status'        => 'tutup',
            'closed_at'     => now(),
        ]);

        return response()->json([
            'message' => 'Shift berhasil ditutup.',
            'shift'   => $this->format($shift),
        ]);
    }

    /**
     * GET /api/cashier/shift/history
     * Riwayat shift milik kasir yang login
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $shifts = Shift::where('user_id', $user->id)
            ->orderByDesc('opened_at')
            ->paginate(20)
            ->through(fn($s) => $this->format($s));

        return response()->json(['shifts' => $shifts]);
    }

    private function format(Shift $s): array
    {
        return [
            'id'                 => $s->id,
            'opening_cash'       => (float) $s->opening_cash,
            'closing_cash'       => $s->closing_cash !== null ? (float) $s->closing_cash : null,
            'expected_cash'      => $s->expected_cash !== null ? (float) $s->expected_cash : null,
            'difference'         => $s->difference !== null ? (float) $s->difference : null,
            'total_sales'        => (float) $s->total_sales,
            'total_transactions' => (int) $s->total_transactions,
            'status'             => $s->status,
            'opened_at'          => $s->opened_at?->format('Y-m-d H:i:s'),
            'closed_at'          => $s->closed_at?->format('Y-m-d H:i:s'),
        ];
    }
}