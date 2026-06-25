@extends('layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan Keuangan')

@section('content')

{{-- Filter Tanggal --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('owner.reports') }}" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                <input type="date" name="start" class="form-control form-control-sm" value="{{ $start }}">
            </div>
            <div class="col-sm-4">
                <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                <input type="date" name="end" class="form-control form-control-sm" value="{{ $end }}">
            </div>
            <div class="col-sm-4 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i> Terapkan Filter
                </button>
                <a href="{{ route('owner.reports') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f4fd;color:#0F4C81;"><i class="bi bi-cash-coin"></i></div>
            <div class="stat-value">Rp {{ number_format($summary['total_penjualan'], 0, ',', '.') }}</div>
            <div class="stat-label">Total Penjualan</div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eafaf1;color:#2dc653;"><i class="bi bi-receipt"></i></div>
            <div class="stat-value">{{ $summary['total_transaksi'] }}</div>
            <div class="stat-label">Total Transaksi</div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef9e7;color:#f4a261;"><i class="bi bi-graph-up"></i></div>
            <div class="stat-value">Rp {{ number_format($summary['rata_rata'], 0, ',', '.') }}</div>
            <div class="stat-label">Rata-rata per Transaksi</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Penjualan Harian --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <span class="fw-semibold">Penjualan Harian</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Jumlah Transaksi</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penjualan_harian as $row)
                            <tr>
                                <td class="small">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                                <td class="small">{{ $row->jumlah }}</td>
                                <td class="small text-end fw-semibold">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada data pada rentang ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Produk Terlaris --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <span class="fw-semibold">Produk Terlaris</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th class="text-end">Terjual</th>
                                <th class="text-end">Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produk_terlaris as $row)
                            <tr>
                                <td class="small">{{ $row->product?->name ?? 'Produk dihapus' }}</td>
                                <td class="small text-end">{{ $row->total_qty }}</td>
                                <td class="small text-end">Rp {{ number_format($row->total_omzet, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Penjualan per Cabang --}}
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white border-0 pt-3">
        <span class="fw-semibold">Penjualan per Cabang</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cabang</th>
                        <th>Jumlah Transaksi</th>
                        <th class="text-end">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penjualan_per_cabang as $row)
                    <tr>
                        <td class="small fw-semibold">{{ $row->branch?->name ?? '-' }}</td>
                        <td class="small">{{ $row->jumlah }}</td>
                        <td class="small text-end fw-semibold">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection