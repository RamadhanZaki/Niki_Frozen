@extends('layouts.app')
@section('title', 'Shift')
@section('page-title', 'Monitoring Shift Kasir')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3">
        <span class="fw-semibold">Riwayat & Status Shift Seluruh Kasir</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Kasir</th>
                        <th>Cabang</th>
                        <th>Dibuka</th>
                        <th>Ditutup</th>
                        <th>Modal Awal</th>
                        <th>Penjualan</th>
                        <th>Transaksi</th>
                        <th>Selisih</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $i => $s)
                    <tr>
                        <td class="text-muted small">{{ $shifts->firstItem() + $i }}</td>
                        <td class="fw-semibold small">{{ $s->user?->name ?? '-' }}</td>
                        <td class="small text-muted">{{ $s->branch?->name ?? '-' }}</td>
                        <td class="small">{{ \Carbon\Carbon::parse($s->opened_at)->format('d/m/Y H:i') }}</td>
                        <td class="small">
                            {{ $s->closed_at ? \Carbon\Carbon::parse($s->closed_at)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="small">Rp {{ number_format($s->opening_cash, 0, ',', '.') }}</td>
                        <td class="small text-success">Rp {{ number_format($s->total_sales, 0, ',', '.') }}</td>
                        <td class="small">{{ $s->total_transactions }}</td>
                        <td class="small">
                            @if(is_null($s->difference))
                                <span class="text-muted">-</span>
                            @else
                                <span class="badge {{ $s->difference == 0 ? 'bg-success' : ($s->difference > 0 ? 'bg-info' : 'bg-danger') }}">
                                    {{ $s->difference == 0 ? 'Pas' : ($s->difference > 0 ? '+' : '') . number_format($s->difference, 0, ',', '.') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($s->status === 'aktif')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Tutup</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">Belum ada data shift</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $shifts->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection