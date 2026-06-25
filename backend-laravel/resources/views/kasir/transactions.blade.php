@extends('layouts.app')
@section('title', 'Transaksi')
@section('page-title', 'Riwayat Transaksi')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3">
        <span class="fw-semibold">Transaksi Saya</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Invoice</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Bayar</th>
                        <th>Kembalian</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr>
                        <td class="fw-semibold small">{{ $t->invoice_number }}</td>
                        <td class="small text-muted">{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="small fw-semibold">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                        <td class="small">Rp {{ number_format($t->payment, 0, ',', '.') }}</td>
                        <td class="small">Rp {{ number_format($t->change_amount, 0, ',', '.') }}</td>
                        <td>
                            @if($t->status === 'sukses')
                                <span class="badge bg-success">Sukses</span>
                            @elseif($t->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @else
                                <span class="badge bg-danger">Batal</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="showDetail({{ $t->id }})">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $transactions->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- Modal Detail Transaksi --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailBody">
                <div class="text-center text-muted py-4">
                    <div class="spinner-border spinner-border-sm me-2"></div> Memuat...
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Data detail per transaksi disiapkan di server (tanpa request tambahan) --}}
<script>
    const transactionDetails = {
        @foreach($transactions as $t)
            {{ $t->id }}: {
                invoice: @json($t->invoice_number),
                tanggal: @json(\Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i')),
                total: {{ $t->total }},
                payment: {{ $t->payment }},
                change: {{ $t->change_amount }},
                items: [
                    @foreach($t->details as $d)
                    {
                        name: @json($d->product->name ?? 'Produk dihapus'),
                        qty: {{ $d->qty }},
                        price: {{ $d->price_at_sale }},
                        subtotal: {{ $d->subtotal }}
                    },
                    @endforeach
                ]
            },
        @endforeach
    };

    function formatRp(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    function showDetail(id) {
        const data = transactionDetails[id];
        const body = document.getElementById('detailBody');

        if (!data) {
            body.innerHTML = '<div class="text-center text-muted py-4">Data tidak ditemukan</div>';
        } else {
            body.innerHTML = `
                <div class="mb-3">
                    <div class="d-flex justify-content-between"><span class="text-muted small">No. Invoice</span><span class="fw-semibold small">${data.invoice}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">Tanggal</span><span class="small">${data.tanggal}</span></div>
                </div>
                <table class="table table-sm">
                    <thead><tr><th>Produk</th><th>Qty</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                        ${data.items.map(i => `
                            <tr>
                                <td class="small">${i.name}</td>
                                <td class="small">${i.qty}</td>
                                <td class="small text-end">${formatRp(i.subtotal)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <hr>
                <div class="d-flex justify-content-between"><span class="text-muted small">Total</span><span class="fw-bold">${formatRp(data.total)}</span></div>
                <div class="d-flex justify-content-between"><span class="text-muted small">Bayar</span><span class="small">${formatRp(data.payment)}</span></div>
                <div class="d-flex justify-content-between"><span class="text-muted small">Kembalian</span><span class="small">${formatRp(data.change)}</span></div>
            `;
        }

        new bootstrap.Modal(document.getElementById('detailModal')).show();
    }
</script>

@endsection