@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .nk-card {
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:14px;
        box-shadow:0 1px 3px rgba(0,0,0,.04);
        overflow:hidden;
        height:100%;
    }
    .nk-card-top {
        height:4px;
        width:100%;
    }
    .nk-card-body { padding:18px 20px; }
    .nk-icon-box {
        width:44px; height:44px;
        border-radius:12px;
        display:flex; align-items:center; justify-content:center;
        font-size:1.15rem;
    }
    .nk-pill {
        font-size:.68rem;
        font-weight:600;
        padding:3px 10px;
        border-radius:999px;
        display:inline-flex;
        align-items:center;
        gap:4px;
    }
    .nk-stat-value { font-size:1.55rem; font-weight:700; line-height:1.1; margin:12px 0 2px; }
    .nk-stat-label { font-size:.82rem; font-weight:600; color:#1e293b; }
    .nk-stat-sub   { font-size:.72rem; color:#94a3b8; }

    .nk-mini-card {
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:14px;
        box-shadow:0 1px 3px rgba(0,0,0,.04);
        padding:18px 20px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        height:100%;
    }
    .nk-mini-card .nk-mini-value { font-size:1.6rem; font-weight:700; line-height:1; }
    .nk-mini-card .nk-mini-label { font-size:.78rem; color:#1e293b; font-weight:600; margin-top:6px; }
    .nk-mini-card .nk-mini-sub   { font-size:.7rem; color:#94a3b8; }
    .nk-mini-icon {
        width:44px; height:44px;
        border-radius:12px;
        display:flex; align-items:center; justify-content:center;
        font-size:1.2rem;
        flex-shrink:0;
    }

    .nk-legend-dot {
        width:9px; height:9px;
        border-radius:50%;
        display:inline-block;
        margin-right:8px;
    }
    .nk-legend-row {
        display:flex;
        align-items:center;
        justify-content:space-between;
        padding:7px 0;
        font-size:.8rem;
    }
</style>
@endpush

@section('content')

{{-- ══ STAT CARDS ══ --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="nk-card">
            <div class="nk-card-top" style="background:#2563eb;"></div>
            <div class="nk-card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="nk-icon-box" style="background:#e8f0fe;color:#2563eb;"><i class="bi bi-bag"></i></div>
                    <span class="nk-pill" style="background:#eafaf1;color:#15803d;"><i class="bi bi-arrow-up-short"></i>Aktif</span>
                </div>
                <div class="nk-stat-value" id="stat-total-produk">{{ $stats['total_produk'] }}</div>
                <div class="nk-stat-label">Total Produk</div>
                <div class="nk-stat-sub">Produk terdaftar</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="nk-card">
            <div class="nk-card-top" style="background:#16a34a;"></div>
            <div class="nk-card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="nk-icon-box" style="background:#eafaf1;color:#16a34a;"><i class="bi bi-currency-dollar"></i></div>
                    <span class="nk-pill" style="background:#eafaf1;color:#15803d;"><i class="bi bi-arrow-up-short"></i>Realtime</span>
                </div>
                <div class="nk-stat-value" id="stat-total-penjualan">Rp {{ number_format($stats['total_penjualan'], 0, ',', '.') }}</div>
                <div class="nk-stat-label">Pendapatan Hari Ini</div>
                <div class="nk-stat-sub">Penjualan hari ini</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="nk-card">
            <div class="nk-card-top" style="background:#f59e0b;"></div>
            <div class="nk-card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="nk-icon-box" style="background:#fef9e7;color:#f59e0b;"><i class="bi bi-exclamation-triangle"></i></div>
                    <span class="nk-pill" style="background:#fdecec;color:#dc2626;"><i class="bi bi-arrow-down-short"></i>Perlu Aksi</span>
                </div>
                <div class="nk-stat-value" id="stat-produk-kadaluarsa">{{ $stats['produk_kadaluarsa'] }}</div>
                <div class="nk-stat-label">Produk Kadaluarsa</div>
                <div class="nk-stat-sub">&le; 7 hari ke depan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="nk-card">
            <div class="nk-card-top" style="background:#e11d48;"></div>
            <div class="nk-card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="nk-icon-box" style="background:#fdf2f8;color:#e11d48;"><i class="bi bi-cart-x"></i></div>
                    <span class="nk-pill" style="background:#fdecec;color:#dc2626;"><i class="bi bi-arrow-down-short"></i>Segera Restock</span>
                </div>
                <div class="nk-stat-value" id="stat-stok-menipis">{{ $stats['stok_menipis'] }}</div>
                <div class="nk-stat-label">Stok Menipis</div>
                <div class="nk-stat-sub">&lt; 10 unit tersisa</div>
            </div>
        </div>
    </div>
</div>

{{-- ══ MINI STAT ROW ══ --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="nk-mini-card">
            <div>
                <div class="nk-mini-value text-primary" id="mini-total-stok">{{ number_format($stats['total_stok'], 0, ',', '.') }}</div>
                <div class="nk-mini-label">Total Stok</div>
                <div class="nk-mini-sub">Seluruh stok semua cabang</div>
            </div>
            <div class="nk-mini-icon" style="background:#e8f0fe;color:#2563eb;"><i class="bi bi-box-seam"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="nk-mini-card">
            <div>
                <div class="nk-mini-value" style="color:#7c3aed;" id="mini-transfer-stok">{{ $stats['transfer_stok'] }}</div>
                <div class="nk-mini-label">Transfer Stok</div>
                <div class="nk-mini-sub">Total transfer tercatat</div>
            </div>
            <div class="nk-mini-icon" style="background:#f3e8ff;color:#7c3aed;"><i class="bi bi-arrow-left-right"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="nk-mini-card">
            <div>
                <div class="nk-mini-value" style="color:#16a34a;" id="mini-transfer-hari-ini">{{ $stats['transfer_hari_ini'] }}</div>
                <div class="nk-mini-label">Transfer Hari Ini</div>
                <div class="nk-mini-sub">Aktivitas hari ini</div>
            </div>
            <div class="nk-mini-icon" style="background:#eafaf1;color:#16a34a;"><i class="bi bi-truck"></i></div>
        </div>
    </div>
</div>

{{-- ══ CHART ROW ══ --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="nk-card">
            <div class="nk-card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                        <div class="fw-bold" style="font-size:1rem;">Revenue Penjualan</div>
                        <div class="text-muted small">Total Pendapatan</div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Total Revenue</div>
                        <div class="fw-bold text-success" style="font-size:1.1rem;" id="total-revenue-90">
                            Rp {{ number_format($total_revenue_90, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                <div style="height:280px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="nk-card">
            <div class="nk-card-body">
                <div class="fw-bold mb-3" style="font-size:1rem;">Kategori Produk</div>
                <div style="height:200px;" class="d-flex justify-content-center">
                    <canvas id="kategoriChart"></canvas>
                </div>
                <div class="mt-3" id="kategoriLegend">
                    @foreach($kategori_produk as $i => $k)
                        <div class="nk-legend-row">
                            <span>
                                <span class="nk-legend-dot" id="legend-dot-{{ $i }}"></span>
                                {{ $k['label'] }}
                            </span>
                            <span class="fw-semibold">{{ $k['persen'] }}%</span>
                        </div>
                    @endforeach
                    @if($kategori_produk->isEmpty())
                        <div class="text-muted small text-center py-3">Belum ada data produk</div>
                    @endif
                </div>
            </div>
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
                        <tbody id="transaksiTerbaruBody">
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
                        <tbody id="stokMenipisBody">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
// Disimpan di scope luar (bukan cuma di dalam DOMContentLoaded) supaya
// pollDashboard() di bawah bisa update chart yang sama tanpa destroy/rebuild
// — destroy+rebuild tiap 15 detik bikin chart "berkedip" dan lebih berat.
let revenueChartInstance  = null;
let kategoriChartInstance = null;
const kategoriColors = ['#1e3a8a', '#2563eb', '#38bdf8', '#7dd3fc', '#a5f3fc', '#0ea5e9'];

document.addEventListener('DOMContentLoaded', function () {
    // ── Revenue Line Chart ──
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const gradient = revenueCtx.createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, 'rgba(37, 99, 235, .25)');
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

    revenueChartInstance = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($revenue_labels),
            datasets: [{
                label: 'Revenue',
                data: @json($revenue_data),
                borderColor: '#2563eb',
                backgroundColor: gradient,
                fill: true,
                tension: .35,
                pointRadius: 0,
                pointHoverRadius: 5,
                borderWidth: 2.5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { maxTicksLimit: 10, font: { size: 10 }, color: '#94a3b8' }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 10 }, color: '#94a3b8',
                        callback: v => v >= 1000000 ? (v/1000000).toFixed(1) + 'Jt' : v.toLocaleString('id-ID')
                    }
                }
            }
        }
    });

    // ── Kategori Donut Chart ──
    const kategoriLabels = @json($kategori_produk->pluck('label'));
    const kategoriData   = @json($kategori_produk->pluck('jumlah'));

    kategoriLabels.forEach((_, i) => {
        const dot = document.getElementById('legend-dot-' + i);
        if (dot) dot.style.background = kategoriColors[i % kategoriColors.length];
    });

    if (kategoriLabels.length) {
        kategoriChartInstance = new Chart(document.getElementById('kategoriChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: kategoriLabels,
                datasets: [{
                    data: kategoriData,
                    backgroundColor: kategoriColors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { display: false } }
            }
        });
    }

    pollDashboard();
    setInterval(pollDashboard, DASHBOARD_POLL_INTERVAL_MS);
});

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') pollDashboard();
});

// ═══════════════════════════════════════════════════════════════
// REALTIME DASHBOARD — polling ringan (pola sama dengan notifikasi lonceng
// & polling produk di POS kasir) supaya Owner tidak perlu reload manual
// tiap ada transaksi baru masuk atau stok berubah dari kasir manapun.
// ═══════════════════════════════════════════════════════════════
const DASHBOARD_POLL_URL = "{{ route('owner.dashboard.poll') }}";
const DASHBOARD_POLL_INTERVAL_MS = 15000; // 15 detik — samakan dengan cadence polling notifikasi; query dashboard lebih berat (banyak agregasi) daripada productsPoll jadi tidak dibuat serapat itu

function formatRpDash(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function escapeHtmlDash(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
}

function renderTransaksiTerbaru(rows) {
    const body = document.getElementById('transaksiTerbaruBody');
    if (!rows.length) {
        body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi hari ini</td></tr>';
        return;
    }
    body.innerHTML = rows.map(t => `
        <tr>
            <td class="small fw-semibold">${escapeHtmlDash(t.invoice_number)}</td>
            <td class="small">${escapeHtmlDash(t.kasir)}</td>
            <td class="small text-muted">${escapeHtmlDash(t.cabang)}</td>
            <td class="small text-muted">${escapeHtmlDash(t.waktu)}</td>
            <td class="small text-end fw-semibold">${formatRpDash(t.total)}</td>
        </tr>`).join('');
}

function renderStokMenipis(rows) {
    const body = document.getElementById('stokMenipisBody');
    if (!rows.length) {
        body.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-4">Semua stok aman</td></tr>';
        return;
    }
    body.innerHTML = rows.map(s => `
        <tr>
            <td class="small">${escapeHtmlDash(s.product_name)}</td>
            <td class="text-end">
                <span class="badge ${s.quantity == 0 ? 'bg-danger' : 'bg-warning text-dark'}">${s.quantity}</span>
            </td>
        </tr>`).join('');
}

function renderKategoriLegend(kategori) {
    const legend = document.getElementById('kategoriLegend');
    if (!kategori.length) {
        legend.innerHTML = '<div class="text-muted small text-center py-3">Belum ada data produk</div>';
        return;
    }
    legend.innerHTML = kategori.map((k, i) => `
        <div class="nk-legend-row">
            <span>
                <span class="nk-legend-dot" style="background:${kategoriColors[i % kategoriColors.length]}"></span>
                ${escapeHtmlDash(k.label)}
            </span>
            <span class="fw-semibold">${k.persen}%</span>
        </div>`).join('');
}

async function pollDashboard() {
    // Sama seperti polling lain di project ini: skip kalau tab tidak aktif.
    if (document.visibilityState !== 'visible') return;

    try {
        const res = await fetch(DASHBOARD_POLL_URL, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();

        // ── Stat cards & mini cards ──
        setText('stat-total-produk', data.stats.total_produk);
        setText('stat-total-penjualan', formatRpDash(data.stats.total_penjualan));
        setText('stat-produk-kadaluarsa', data.stats.produk_kadaluarsa);
        setText('stat-stok-menipis', data.stats.stok_menipis);
        setText('mini-total-stok', Number(data.stats.total_stok).toLocaleString('id-ID'));
        setText('mini-transfer-stok', data.stats.transfer_stok);
        setText('mini-transfer-hari-ini', data.stats.transfer_hari_ini);
        setText('total-revenue-90', formatRpDash(data.total_revenue_90));

        // ── Revenue chart: timpa seluruh dataset (90 hari bergeser tiap
        // hari, bukan cuma titik terakhir yang berubah) ──
        if (revenueChartInstance) {
            revenueChartInstance.data.labels = data.revenue_labels;
            revenueChartInstance.data.datasets[0].data = data.revenue_data;
            revenueChartInstance.update('none'); // 'none' = tanpa animasi supaya tidak "loncat" tiap poll
        }

        // ── Kategori donut chart ──
        const kategoriLabels = data.kategori_produk.map(k => k.label);
        const kategoriData   = data.kategori_produk.map(k => k.jumlah);
        if (kategoriChartInstance) {
            kategoriChartInstance.data.labels = kategoriLabels;
            kategoriChartInstance.data.datasets[0].data = kategoriData;
            kategoriChartInstance.update('none');
        }
        renderKategoriLegend(data.kategori_produk);

        // ── Tabel ──
        renderTransaksiTerbaru(data.transaksi_terbaru);
        renderStokMenipis(data.stok_menipis);
    } catch (e) {
        // Diam-diam gagal (koneksi putus sesaat) — poll berikutnya coba lagi.
    }
}
</script>
@endpush
