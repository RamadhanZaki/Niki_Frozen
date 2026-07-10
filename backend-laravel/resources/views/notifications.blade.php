@extends('layouts.app')
@section('title', 'Riwayat Notifikasi')
@section('page-title', 'Riwayat Notifikasi')

@section('content')

@if(session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif

{{-- Muncul lewat JS kalau polling mendeteksi ada notifikasi baru masuk /
     status berubah selagi halaman ini terbuka. Sengaja tidak auto-replace
     tabel di bawah (ada paginasi + filter status + form per baris), jadi
     cukup ajak user klik "Muat ulang" sendiri. --}}
<div id="notifUpdateBanner" class="alert alert-info d-none py-2 px-3 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span class="small"><i class="bi bi-arrow-repeat me-1"></i> Ada pembaruan notifikasi.</span>
    <a href="{{ url()->full() }}" class="btn btn-sm btn-primary">Muat ulang</a>
</div>

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
                <a href="{{ route(auth()->user()->role . '.notifications.history', ['status' => 'all']) }}"
                   class="btn btn-outline-secondary {{ $status === 'all' ? 'active' : '' }}">Semua</a>
                <a href="{{ route(auth()->user()->role . '.notifications.history', ['status' => 'unread']) }}"
                   class="btn btn-outline-secondary {{ $status === 'unread' ? 'active' : '' }}">Belum Dibaca</a>
                <a href="{{ route(auth()->user()->role . '.notifications.history', ['status' => 'read']) }}"
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
                                    <form method="POST" action="{{ route(auth()->user()->role . '.notifications.read', $notif->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Tandai dibaca">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route(auth()->user()->role . '.notifications.destroy', $notif->id) }}"
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

@push('scripts')
<script>
    // ═══════════════════════════════════════════════════════════════
    // REALTIME RIWAYAT NOTIFIKASI — polling ringan supaya user tahu ada
    // notifikasi baru masuk selagi halaman riwayat ini terbuka, tanpa perlu
    // reload manual buat "ngecek". Pakai endpoint /notifications/poll yang
    // sama dengan yang dipakai lonceng notifikasi di layouts/app.blade.php.
    //
    // Beda dengan #productGrid di halaman POS: di sini kita SENGAJA tidak
    // me-render ulang tabel langsung lewat JS, karena halaman ini punya
    // paginasi, filter status (all/unread/read), dan form mark-read/hapus
    // per baris (dengan CSRF token masing-masing) — replace DOM diam-diam
    // berisiko menimpa state itu atau menimpa aksi yang lagi diklik user.
    // Jadi cukup munculkan banner ringan yang minta user klik "Muat ulang".
    // ═══════════════════════════════════════════════════════════════
    const NOTIF_HISTORY_POLL_URL = @json(route('notifications.poll'));
    const NOTIF_HISTORY_POLL_INTERVAL_MS = 5000; // samain dengan interval lonceng notifikasi
    const notifHistoryBaselineUnread = {{ (int) $unreadCount }};

    const notifUpdateBanner = document.getElementById('notifUpdateBanner');
    let notifHistoryBannerShown = false;

    async function pollNotifHistory() {
        if (document.visibilityState !== 'visible') return;
        if (notifHistoryBannerShown) return; // sudah ditawarin reload sekali, jangan spam fetch terus

        try {
            const res = await fetch(NOTIF_HISTORY_POLL_URL, {
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;

            const data = await res.json();

            // unread_count berubah (baik nambah karena notif baru, maupun
            // berkurang karena ditandai dibaca dari tab/perangkat lain) =
            // sinyal kalau daftar di halaman ini sudah tidak sinkron lagi.
            if (data.unread_count !== notifHistoryBaselineUnread) {
                notifUpdateBanner.classList.remove('d-none');
                notifHistoryBannerShown = true;
            }
        } catch (e) {
            // Diam-diam gagal (koneksi putus sesaat) — poll berikutnya coba lagi.
        }
    }

    const notifHistoryPollTimer = setInterval(pollNotifHistory, NOTIF_HISTORY_POLL_INTERVAL_MS);

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') pollNotifHistory();
    });
</script>
@endpush

@endsection
