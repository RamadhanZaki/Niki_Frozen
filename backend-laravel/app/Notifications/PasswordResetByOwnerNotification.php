<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class PasswordResetByOwnerNotification extends Notification
{
    public function __construct(public User $resetBy)
    {
    }

    /**
     * Hanya pakai database channel — cukup untuk ditampilkan di dropdown
     * lonceng notifikasi Kasir, sama seperti notifikasi lain di aplikasi ini.
     * Tidak menyertakan password baru di dalam data notifikasi (disimpan di
     * DB dalam bentuk teks) — kasir tetap harus menghubungi Owner untuk tahu
     * password barunya.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Password Direset oleh Owner',
            'message' => sprintf(
                'Password akun kamu direset oleh %s pada %s. Silakan minta password baru ke Owner untuk login kembali.',
                $this->resetBy->name,
                now()->translatedFormat('d M Y H:i')
            ),
            'reset_by_id' => $this->resetBy->id,
        ];
    }
}
