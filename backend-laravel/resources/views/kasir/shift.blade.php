@extends('layouts.app')
@section('title', 'Shift Saya')
@section('page-title', 'Shift Saya')

@section('content')

<div class="row g-3">
    {{-- ══ KIRI: STATUS SHIFT ══ --}}
    <div class="col-lg-5">
        @if($shift)
            {{-- Shift sedang aktif --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-success">Aktif</span>
                        <span class="text-muted small">Dibuka {{ \Carbon\Carbon::parse($shift->opened_at)->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted small">Modal Awal</span>
                        <span class="fw-semibold">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted small">Total Penjualan</span>
                        <span class="fw-semibold text-success">Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between ps-3">
                        <span class="text-muted small"><i class="bi bi-cash-coin me-1"></i>— Tunai</span>
                        <span class="small">Rp {{ number_format($shift->total_cash_sales, 0, ',', '.') }}</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between ps-3">
                        <span class="text-muted small"><i class="bi bi-qr-code me-1"></i>— QRIS</span>
                        <span class="small">Rp {{ number_format($shift->total_qris_sales, 0, ',', '.') }}</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <span class="text-muted small">Jumlah Transaksi</span>
                        <span class="fw-semibold">{{ $shift->total_transactions }}</span>
                    </div>

                    <hr>

                    <div class="mb-1 d-flex justify-content-between">
                        <span class="text-muted small">Estimasi Kas Saat Ini</span>
                        <span class="fw-bold fs-5 text-primary">
                            Rp {{ number_format($shift->opening_cash + $shift->total_cash_sales, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted" style="font-size:.7rem;">
                            Hanya dari pembayaran tunai — QRIS tidak masuk laci kas fisik.
                        </span>
                    </div>

                    <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
                        <i class="bi bi-stop-circle me-1"></i> Tutup Shift
                    </button>
                </div>
            </div>
        @else
            {{-- Tidak ada shift aktif → form buka shift --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <span class="fw-semibold"><i class="bi bi-play-circle me-1"></i> Buka Shift Baru</span>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Anda belum memiliki shift aktif. Buka shift untuk mulai berjualan.</p>
                    <form method="POST" action="{{ route('kasir.shift.open') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Modal Awal (Kas)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" inputmode="numeric" id="openingCashInput" class="form-control" placeholder="Contoh: 100.000" required>
                            </div>
                            <input type="hidden" name="opening_cash" id="openingCashHidden">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-play-circle me-1"></i> Buka Shift
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    {{-- ══ KANAN: RIWAYAT SHIFT ══ --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <span class="fw-semibold">Riwayat Shift</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Modal</th>
                                <th>Penjualan</th>
                                <th>Kas Akhir</th>
                                <th>Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayat as $r)
                            <tr>
                                <td class="small">
                                    {{ \Carbon\Carbon::parse($r->opened_at)->format('d/m/Y') }}<br>
                                    <span class="text-muted" style="font-size:.7rem;">
                                        {{ \Carbon\Carbon::parse($r->opened_at)->format('H:i') }} – {{ \Carbon\Carbon::parse($r->closed_at)->format('H:i') }}
                                    </span>
                                </td>
                                <td class="small">Rp {{ number_format($r->opening_cash, 0, ',', '.') }}</td>
                                <td class="small text-success">
                                    Rp {{ number_format($r->total_sales, 0, ',', '.') }}
                                    <br>
                                    <span class="text-muted" style="font-size:.65rem;">
                                        Tunai Rp {{ number_format($r->total_cash_sales, 0, ',', '.') }} · QRIS Rp {{ number_format($r->total_qris_sales, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="small">Rp {{ number_format($r->closing_cash, 0, ',', '.') }}</td>
                                <td>
                                    @php $diff = $r->difference ?? 0; @endphp
                                    <span class="badge {{ $diff == 0 ? 'bg-success' : ($diff > 0 ? 'bg-info' : 'bg-danger') }}">
                                        {{ $diff == 0 ? 'Pas' : ($diff > 0 ? '+' : '') . number_format($diff, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada riwayat shift</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tutup Shift --}}
@if($shift)
<div class="modal fade" id="closeShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tutup Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('kasir.shift.close') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info small">
                        Estimasi kas seharusnya (tunai saja): <strong>Rp {{ number_format($shift->opening_cash + $shift->total_cash_sales, 0, ',', '.') }}</strong>
                        <br>
                        <span style="font-size:.75rem;">
                            Penjualan QRIS (Rp {{ number_format($shift->total_qris_sales, 0, ',', '.') }}) tidak dihitung karena tidak masuk laci kas fisik.
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kas Akhir (Hasil Hitung Fisik)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" inputmode="numeric" id="closingCashInput" class="form-control" placeholder="Masukkan jumlah kas riil" required>
                        </div>
                        <input type="hidden" name="closing_cash" id="closingCashHidden">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tutup Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    // Format input dengan titik ribuan sambil diketik (mis. 100000 -> 100.000),
    // sementara nilai mentahnya (tanpa titik) dikirim lewat hidden input supaya
    // form tetap submit angka murni ke server.
    function formatRupiahInput(input, hidden) {
        const digitsBeforeCursor = input.value.slice(0, input.selectionStart).replace(/\D/g, '').length;

        const rawDigits = input.value.replace(/\D/g, '');
        const formatted = rawDigits ? Number(rawDigits).toLocaleString('id-ID') : '';
        input.value = formatted;
        hidden.value = rawDigits;

        let seenDigits = 0;
        let pos = formatted.length;
        for (let i = 0; i < formatted.length; i++) {
            if (/\d/.test(formatted[i])) seenDigits++;
            if (seenDigits === digitsBeforeCursor) {
                pos = i + 1;
                break;
            }
        }
        input.setSelectionRange(pos, pos);
    }

    function setupRupiahInput(inputId, hiddenId) {
        const input  = document.getElementById(inputId);
        const hidden = document.getElementById(hiddenId);
        if (!input || !hidden) return;
        input.addEventListener('input', () => formatRupiahInput(input, hidden));
    }

    setupRupiahInput('openingCashInput', 'openingCashHidden');
    setupRupiahInput('closingCashInput', 'closingCashHidden');
</script>
@endpush

@endsection
