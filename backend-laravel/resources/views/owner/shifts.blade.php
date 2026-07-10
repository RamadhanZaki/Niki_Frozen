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
                        <th>Tunai</th>
                        <th>QRIS</th>
                        <th>Total Penjualan</th>
                        <th>Transaksi</th>
                        <th>Selisih</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="shiftsTableBody">
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
                        <td class="small">Rp {{ number_format($s->total_cash_sales, 0, ',', '.') }}</td>
                        <td class="small">Rp {{ number_format($s->total_qris_sales, 0, ',', '.') }}</td>
                        <td class="small text-success fw-semibold">Rp {{ number_format($s->total_sales, 0, ',', '.') }}</td>
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
                    <tr><td colspan="12" class="text-center text-muted py-4">Belum ada data shift</td></tr>
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

@push('scripts')
<script>
// ═══════════════════════════════════════════════════════════════
// REALTIME SHIFT — polling ringan (pola sama dengan dashboard/stocks/POS).
// Angka total_sales/total_cash_sales/total_qris_sales/total_transactions di
// tabel shifts SUDAH ter-update live saat kasir checkout (lihat
// KasirWebController::checkout(), pakai $shift->increment(...)) — endpoint
// poll ini cuma mengirim ulang apa yang sudah ada di DB, tidak menghitung
// ulang apa-apa dari nol.
// ═══════════════════════════════════════════════════════════════
const SHIFTS_POLL_BASE_URL = "{{ route('owner.shifts.poll') }}";
const SHIFTS_POLL_INTERVAL_MS = 15000;

function escapeHtmlShift(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function formatRpShift(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function differenceBadgeHtml(diff) {
    if (diff === null) return '<span class="text-muted">-</span>';
    if (diff == 0) return '<span class="badge bg-success">Pas</span>';
    const cls  = diff > 0 ? 'bg-info' : 'bg-danger';
    const text = (diff > 0 ? '+' : '') + Number(diff).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    return `<span class="badge ${cls}">${text}</span>`;
}

function statusBadgeHtml(status) {
    return status === 'aktif'
        ? '<span class="badge bg-success">Aktif</span>'
        : '<span class="badge bg-secondary">Tutup</span>';
}

function shiftRowHtml(s) {
    return `
        <tr>
            <td class="text-muted small">${s.no}</td>
            <td class="fw-semibold small">${escapeHtmlShift(s.kasir)}</td>
            <td class="small text-muted">${escapeHtmlShift(s.cabang)}</td>
            <td class="small">${escapeHtmlShift(s.opened_at)}</td>
            <td class="small">${s.closed_at ? escapeHtmlShift(s.closed_at) : '-'}</td>
            <td class="small">${formatRpShift(s.opening_cash)}</td>
            <td class="small">${formatRpShift(s.total_cash_sales)}</td>
            <td class="small">${formatRpShift(s.total_qris_sales)}</td>
            <td class="small text-success fw-semibold">${formatRpShift(s.total_sales)}</td>
            <td class="small">${s.total_transactions}</td>
            <td class="small">${differenceBadgeHtml(s.difference)}</td>
            <td>${statusBadgeHtml(s.status)}</td>
        </tr>`;
}

async function pollShifts() {
    if (document.visibilityState !== 'visible') return;

    try {
        // window.location.search bawa ?page=N kalau Owner lagi di halaman
        // selain 1, supaya poll tetap konsisten dengan halaman yang dilihat.
        const url = SHIFTS_POLL_BASE_URL + window.location.search;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();

        const body = document.getElementById('shiftsTableBody');
        if (!data.rows.length) {
            body.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-4">Belum ada data shift</td></tr>';
        } else {
            body.innerHTML = data.rows.map(shiftRowHtml).join('');
        }
    } catch (e) {
        // Diam-diam gagal (koneksi putus sesaat) — poll berikutnya coba lagi.
    }
}

pollShifts();
setInterval(pollShifts, SHIFTS_POLL_INTERVAL_MS);

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') pollShifts();
});
</script>
@endpush
