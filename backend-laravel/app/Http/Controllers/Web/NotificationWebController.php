<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationWebController extends Controller
{
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    /**
     * Endpoint polling ringan dipanggil dari JS setiap beberapa detik
     * (lihat layouts/app.blade.php) supaya badge & dropdown lonceng ter-update
     * tanpa reload manual — tanpa perlu WebSocket/Pusher/Reverb. Owner (atau
     * siapa pun yang login) cukup diam di dashboard, notifikasi baru (QRIS
     * masuk, selisih kas shift) otomatis muncul dalam beberapa detik.
     */
    public function poll(Request $request)
    {
        $user = $request->user();

        $unreadCount = $user->unreadNotifications()->count();

        $notifications = $user->unreadNotifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'title'      => $n->data['title'] ?? 'Notifikasi',
                'message'    => $n->data['message'] ?? '',
                'created_at' => $n->created_at->diffForHumans(),
            ])
            ->values();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Halaman riwayat notifikasi Owner: menampilkan seluruh notifikasi
     * (baik yang sudah maupun belum dibaca), bisa difilter statusnya,
     * dan diurutkan dari yang terbaru.
     */
    public function history(Request $request)
    {
        $status = $request->query('status', 'all'); // all | unread | read

        $query = $request->user()->notifications()->latest();

        if ($status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(15)->withQueryString();

        $unreadCount = $request->user()->unreadNotifications()->count();

        return view('notifications', compact('notifications', 'status', 'unreadCount'));
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca (dipakai dari halaman
     * riwayat, misalnya saat Owner klik salah satu baris notifikasi).
     */
    public function markRead(Request $request, string $notification)
    {
        $notif = $request->user()->notifications()->findOrFail($notification);

        if (is_null($notif->read_at)) {
            $notif->markAsRead();
        }

        return back();
    }

    /**
     * Hapus satu notifikasi dari riwayat.
     */
    public function destroy(Request $request, string $notification)
    {
        $notif = $request->user()->notifications()->findOrFail($notification);
        $notif->delete();

        return back()->with('success', 'Notifikasi dihapus.');
    }
}
