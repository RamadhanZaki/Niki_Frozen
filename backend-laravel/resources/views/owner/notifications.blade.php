@extends('layouts.app')
@section('title', 'Riwayat Notifikasi')
@section('page-title', 'Riwayat Notifikasi')

@section('content')

@if(session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold">
            Semua Notifikasi
            @if($unreadCount > 0)
                <span class="badge bg-danger ms-1">{{ $unreadCount }} belum dibaca</span>
            @endif
        </span>

        <div class="d-flex align-items-center gap-2">
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('owner.notifications.history', ['status' => 'all']) }}"
                   class="btn btn-outline-secondary {{ $status === 'all' ? 'active' : '' }}">Semua</a>
                <a href="{{ route('owner.notifications.history', ['status' => 'unread']) }}"
                   class="btn btn-outline-secondary {{ $status === 'unread' ? 'active' : '' }}">Belum Dibaca</a>
                <a href="{{ route('owner.notifications.history', ['status' => 'read']) }}"
                   class="btn btn-outline-secondary {{ $status === 'read' ? 'active' : '' }}">Sudah Dibaca</a>
            </div>

            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">Tandai semua dibaca</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Status</th>
                        <th>Notifikasi</th>
                        <th>Waktu</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $i => $notif)
                    <tr class="{{ is_null($notif->read_at) ? 'table-warning bg-opacity-25' : '' }}">
                        <td class="text-muted small">{{ $notifications->firstItem() + $i }}</td>
                        <td>
                            @if(is_null($notif->read_at))
                                <span class="badge bg-danger">Baru</span>
                            @else
                                <span class="badge bg-secondary">Dibaca</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold small">{{ $notif->data['title'] ?? 'Notifikasi' }}</div>
                            <div class="text-muted small">{{ $notif->data['message'] ?? '' }}</div>
                        </td>
                        <td class="small text-muted" style="white-space:nowrap;">
                            {{ $notif->created_at->format('d/m/Y H:i') }}
                            <div class="text-muted" style="font-size:.7rem;">{{ $notif->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                @if(is_null($notif->read_at))
                                    <form method="POST" action="{{ route('owner.notifications.read', $notif->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Tandai dibaca">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('owner.notifications.destroy', $notif->id) }}"
                                      onsubmit="return confirm('Hapus notifikasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada notifikasi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $notifications->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
