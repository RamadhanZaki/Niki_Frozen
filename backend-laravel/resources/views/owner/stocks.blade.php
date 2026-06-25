@extends('layouts.app')
@section('title', 'Stok')
@section('page-title', 'Manajemen Stok')

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#e8f4fd"><i class="bi bi-box fs-4 text-primary"></i></div>
                <div>
                    <div class="text-muted small">Total Produk</div>
                    <div class="fw-bold fs-5">{{ $total_products }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#fef9e7"><i class="bi bi-exclamation-triangle fs-4 text-warning"></i></div>
                <div>
                    <div class="text-muted small">Stok Menipis</div>
                    <div class="fw-bold fs-5">{{ $low_stock }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#fdf2f8"><i class="bi bi-x-circle fs-4 text-danger"></i></div>
                <div>
                    <div class="text-muted small">Stok Habis</div>
                    <div class="fw-bold fs-5">{{ $critical_stock }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#eafaf1"><i class="bi bi-currency-dollar fs-4 text-success"></i></div>
                <div>
                    <div class="text-muted small">Nilai Total Stok</div>
                    <div class="fw-bold fs-5">Rp {{ number_format($total_value, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table Card --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold">Daftar Stok</span>
        <button class="btn btn-sm btn-dark" onclick="openAdjustModal()">
            <i class="bi bi-arrow-left-right me-1"></i> Sesuaikan Stok
        </button>
    </div>

    {{-- Filter --}}
    <div class="card-body pb-0">
        <form method="GET" action="{{ route('owner.stocks') }}" class="row g-2">
            <div class="col-sm-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama produk..." value="{{ request('search') }}">
            </div>
            <div class="col-sm-3">
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <select name="stock_filter" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="normal"   {{ request('stock_filter') == 'normal'   ? 'selected' : '' }}>Normal</option>
                    <option value="low"      {{ request('stock_filter') == 'low'      ? 'selected' : '' }}>Menipis</option>
                    <option value="critical" {{ request('stock_filter') == 'critical' ? 'selected' : '' }}>Habis</option>
                </select>
            </div>
            <div class="col-sm-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('owner.stocks') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>

    <div class="card-body p-0 mt-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Cabang</th>
                        <th>Stok</th>
                        <th>Min. Stok</th>
                        <th>Status</th>
                        <th>Diperbarui</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $i => $s)
                    @php
                        $qty = $s->stock?->quantity ?? 0;
                        $min = $s->stock?->min_stock ?? 10;
                    @endphp
                    <tr>
                        <td class="text-muted small">{{ $stocks->firstItem() + $i }}</td>
                        <td class="fw-semibold">{{ $s->name }}</td>
                        <td><span class="badge bg-secondary">{{ $s->category }}</span></td>
                        <td class="small">{{ $s->branch?->name ?? '-' }}</td>
                        <td class="fw-semibold {{ $qty == 0 ? 'text-danger' : ($qty <= $min ? 'text-warning' : '') }}">
                            {{ $qty }}
                        </td>
                        <td class="text-muted small">{{ $min }}</td>
                        <td>
                            @if($qty == 0)
                                <span class="badge bg-danger">Habis</span>
                            @elseif($qty <= $min)
                                <span class="badge bg-warning text-dark">Menipis</span>
                            @else
                                <span class="badge bg-success">Normal</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $s->stock?->updated_at ? \Carbon\Carbon::parse($s->stock->updated_at)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="openAdjustModal({{ json_encode(['id' => $s->id, 'name' => $s->name, 'stock' => $qty]) }})">
                                <i class="bi bi-arrow-left-right"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data stok</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $stocks->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- Modal Sesuaikan Stok --}}
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sesuaikan Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('owner.stocks.adjust') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="adj_product_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Produk</label>
                        <input type="text" id="adj_product_name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Stok Saat Ini</label>
                        <input type="text" id="adj_current_stock" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipe <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="add">Tambah Stok</option>
                            <option value="reduce">Kurangi Stok</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan</label>
                        <input type="text" name="note" class="form-control" placeholder="Opsional...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const adjustModal = new bootstrap.Modal(document.getElementById('adjustModal'));

function openAdjustModal(data = null) {
    if (data) {
        document.getElementById('adj_product_id').value    = data.id;
        document.getElementById('adj_product_name').value  = data.name;
        document.getElementById('adj_current_stock').value = data.stock;
    } else {
        document.getElementById('adj_product_id').value    = '';
        document.getElementById('adj_product_name').value  = '';
        document.getElementById('adj_current_stock').value = '';
    }
    adjustModal.show();
}
</script>
@endpush