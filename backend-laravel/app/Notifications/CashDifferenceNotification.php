<?php

namespace App\Notifications;

use App\Models\Shift;
use Illuminate\Notifications\Notification;

class CashDifferenceNotification extends Notification
{
    public function __construct(public Shift $shift)
    {
    }

    /**
     * Hanya pakai database channel — cukup untuk ditampilkan di dropdown
     * lonceng notifikasi Owner. Bisa ditambah 'mail' nanti kalau perlu
     * notifikasi email juga.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $selisih = (float) $this->shift->difference;
        $label   = $selisih > 0 ? 'lebih' : 'kurang';

        return [
            'title'      => 'Selisih Kas Shift',
            'message'    => sprintf(
                'Shift %s di cabang %s ditutup dengan selisih kas %s Rp%s.',
                $this->shift->user->name ?? 'Kasir',
                $this->shift->branch->name ?? '-',
                $label,
                number_format(abs($selisih), 0, ',', '.')
            ),
            'shift_id'   => $this->shift->id,
            'branch_id'  => $this->shift->branch_id,
            'difference' => $selisih,
        ];
    }
}
