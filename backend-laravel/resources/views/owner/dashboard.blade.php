@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- ══ STAT CARDS ══ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f4fd;color:#0F4C81;"><i class="bi bi-cash-coin"></i></div>
            <div class="stat-value">Rp {{ number_format($stats['total_penjualan'], 0, ',', '.') }}</div>
            <div class="stat-label">Penjualan Hari Ini</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eafaf1;color:#2dc653;"><i class="bi bi-receipt"></i></div>
            <div class="stat-value">{{ $stats['total_transaksi'] }}</div>
            <div class="stat-label">Transaksi Hari Ini</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef9e7;color:#f4a261;"><i class="bi bi-box-seam"></i></div>
            <div class="stat-value">{{ $stats['total_produk'] }}</div>
            <div class="stat-label">Total Produk</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fdf2f8;color:#e63946;"><i class="bi bi-building"></i></div>
            <div class="stat-value">{{ $stats['total_cabang'] }}</div>
            <div class="stat-label">Total Cabang</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- ══ TRANSAKSI TERBARU ══ --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Transaksi Terbaru</span>
                <span class="text-muted small">Hari ini</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th>Kasir</th>
                                <th>Cabang</th>
                                <th>Waktu</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksi_terbaru as $t)
                            <tr>
                                <td class="small fw-semibold">{{ $t->invoice_number }}</td>
                                <td class="small">{{ $t->user?->name ?? '-' }}</td>
                                <td class="small text-muted">{{ $t->branch?->name ?? '-' }}</td>
                                <td class="small text-muted">{{ \Carbon\Carbon::parse($t->created_at)->format('H:i') }}</td>
                                <td class="small text-end fw-semibold">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi hari ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ STOK MENIPIS ══ --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-exclamation-triangle text-warning me-1"></i> Stok Menipis</span>
                <a href="{{ route('owner.stocks') }}" class="small text-decoration-none">Lihat semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th class="text-end">Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stok_menipis as $s)
                            <tr>
                                <td class="small">{{ $s->product?->name ?? '-' }}</td>
                                <td class="text-end">
                                    <span class="badge {{ $s->quantity == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                        {{ $s->quantity }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-4">Semua stok aman</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection