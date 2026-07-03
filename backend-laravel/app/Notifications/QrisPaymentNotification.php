<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Notification;

class QrisPaymentNotification extends Notification
{
    public function __construct(public Transaction $transaction)
    {
    }

    /**
     * Hanya pakai database channel — cukup untuk ditampilkan di dropdown
     * lonceng notifikasi Owner, sama seperti CashDifferenceNotification.
     * Dikirim ke Owner setiap kali ada transaksi yang dibayar via QRIS,
     * supaya Owner tahu real-time: kasir mana, cabang mana, dan nominalnya.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'          => 'Pembayaran QRIS Masuk',
            'message'        => sprintf(
                'Transaksi %s sebesar Rp%s dibayar via QRIS oleh %s di cabang %s.',
                $this->transaction->invoice_number,
                number_format((float) $this->transaction->total, 0, ',', '.'),
                $this->transaction->user->name ?? 'Kasir',
                $this->transaction->branch->name ?? '-'
            ),
            'transaction_id' => $this->transaction->id,
            'branch_id'      => $this->transaction->branch_id,
            'amount'         => (float) $this->transaction->total,
        ];
    }
}
