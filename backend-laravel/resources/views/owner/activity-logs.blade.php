@extends('layouts.app')
@section('title', 'Riwayat Aktivitas')
@section('page-title', 'Riwayat Aktivitas (Audit Log)')

@section('content')

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('owner.activityLogs') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Cari Deskripsi</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       value="{{ request('search') }}" placeholder="Contoh: nama produk...">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Pengguna</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->role }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Cabang</label>
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ (string) request('branch_id') === (string) $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Jenis Aksi</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($availableActions as $code => $label)
                        <option value="{{ $code }}" {{ request('action') === $code ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small fw-semibold mb-1">Dari</label>
                <input type="date" name="start" class="form-control form-control-sm" value="{{ request('start') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label small fw-semibold mb-1">Sampai</label>
                <input type="date" name="end" class="form-control form-control-sm" value="{{ request('end') }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i>
                </button>
                @if(request()->hasAny(['search', 'user_id', 'branch_id', 'action', 'start', 'end']))
                    <a href="{{ route('owner.activityLogs') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <span class="fw-semibold">Jejak Aktivitas Sistem</span>
            <span class="text-muted small">({{ $logs->total() }} aktivitas ditemukan)</span>
        </div>
        <a href="{{ route('owner.activityLogs.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Cabang</th>
                        <th>Jenis Aksi</th>
                        <th>Deskripsi</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="small text-muted text-nowrap">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="small fw-semibold">
                            {{ $log->user?->name ?? 'Sistem' }}
                            @if($log->user)
                                <span class="text-muted fw-normal">({{ $log->user->role }})</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $log->branch?->name ?? '-' }}</td>
                        <td><span class="badge {{ $log->action_badge_class }}">{{ $log->action_label }}</span></td>
                        <td class="small">
                            {{ $log->description }}
                            @if($log->properties)
                                <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline btn-detail-log"
                                        data-log-id="{{ $log->id }}" title="Lihat detail">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">
                        @if(request()->hasAny(['search', 'user_id', 'branch_id', 'action', 'start', 'end']))
                            Tidak ada aktivitas yang cocok dengan filter ini.
                        @else
                            Belum ada aktivitas tercatat.
                        @endif
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- Modal Detail Properti --}}
<div class="modal fade" id="detailLogModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Aktivitas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="detailLogBody" class="small mb-0" style="white-space: pre-wrap;"></pre>
            </div>
        </div>
    </div>
</div>

{{-- Detail properties tiap baris disiapkan di server (tanpa request tambahan),
     sama seperti pola transactionDetailsData di kasir/transactions.blade.php. --}}
<script type="application/json" id="activityLogPropertiesData">
    {!! json_encode(
        $logs->mapWithKeys(fn ($log) => [$log->id => $log->properties])
    ) !!}
</script>

<script>
    const activityLogProperties = JSON.parse(
        document.getElementById('activityLogPropertiesData').textContent
    );

    document.querySelectorAll('.btn-detail-log').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.logId;
            const data = activityLogProperties[id];
            document.getElementById('detailLogBody').textContent = data
                ? JSON.stringify(data, null, 2)
                : 'Tidak ada detail tambahan.';
            new bootstrap.Modal(document.getElementById('detailLogModal')).show();
        });
    });
</script>

@endsection
