@extends('layouts.app')
@section('title', 'Kode Diskon')
@section('page-title', 'Kode Diskon')

@section('content')

@php
$statusBadge = [
    'aktif'       => ['bg-success', 'Aktif'],
    'nonaktif'    => ['bg-secondary', 'Nonaktif'],
    'belum_mulai' => ['bg-info text-dark', 'Belum Mulai'],
    'kadaluarsa'  => ['bg-danger', 'Kadaluarsa'],
    'kuota_habis' => ['bg-warning text-dark', 'Kuota Habis'],
];
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Daftar Kode Diskon</span>
        <button class="btn btn-sm btn-primary" onclick="openDiscountModal()">
            <i class="bi bi-plus-lg me-1"></i> Buat Kode Diskon
        </button>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Tipe & Nilai</th>
                        <th>Min. Belanja</th>
                        <th>Kuota</th>
                        <th>Berlaku</th>
                        <th>Cabang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discounts as $i => $d)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td><span class="fw-semibold font-monospace">{{ $d->code }}</span></td>
                        <td class="small">
                            @if($d->type === 'percentage')
                                {{ rtrim(rtrim(number_format($d->value, 2, ',', '.'), '0'), ',') }}%
                                @if($d->max_discount)
                                    <div class="text-muted" style="font-size:.7rem;">maks Rp{{ number_format($d->max_discount, 0, ',', '.') }}</div>
                                @endif
                            @else
                                Rp{{ number_format($d->value, 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="small">Rp{{ number_format($d->min_purchase, 0, ',', '.') }}</td>
                        <td class="small">
                            {{ $d->used_count }} / {{ $d->quota ?? '∞' }}
                        </td>
                        <td class="small text-muted">
                            {{ $d->valid_from->format('d/m/y') }} – {{ $d->valid_until->format('d/m/y') }}
                        </td>
                        <td class="small">{{ $d->branch?->name ?? 'Semua Cabang' }}</td>
                        <td>
                            <span class="badge {{ $statusBadge[$d->status][0] }}">{{ $statusBadge[$d->status][1] }}</span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick='openDiscountModal({{ json_encode([
                                    "id" => $d->id,
                                    "code" => $d->code,
                                    "type" => $d->type,
                                    "value" => (float) $d->value,
                                    "min_purchase" => (float) $d->min_purchase,
                                    "max_discount" => $d->max_discount ? (float) $d->max_discount : null,
                                    "quota" => $d->quota,
                                    "branch_id" => $d->branch_id,
                                    "valid_from" => $d->valid_from->format("Y-m-d\TH:i"),
                                    "valid_until" => $d->valid_until->format("Y-m-d\TH:i"),
                                    "is_active" => $d->is_active,
                                ]) }})'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('owner.discounts.destroy', $d->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirmDelete(this, 'Kode diskon {{ $d->code }} akan dihapus.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Belum ada kode diskon</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah/Edit Kode Diskon --}}
<div class="modal fade" id="discountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="discountModalTitle">Buat Kode Diskon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="discountForm" method="POST" action="{{ route('owner.discounts.store') }}">
                @csrf
                <div id="discountMethodField"></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="d_code" class="form-control text-uppercase" style="text-transform:uppercase;"
                               placeholder="Contoh: PROMO10" maxlength="30" required>
                        <div class="form-text">Huruf, angka, strip (-), underscore (_) saja. Otomatis jadi huruf besar.</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Tipe Diskon <span class="text-danger">*</span></label>
                            <select name="type" id="d_type" class="form-select" onchange="toggleMaxDiscountField()" required>
                                <option value="percentage">Persen (%)</option>
                                <option value="fixed">Potongan Tetap (Rp)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" id="d_value_label">Nilai (%) <span class="text-danger">*</span></label>
                            <input type="number" name="value" id="d_value" class="form-control" min="0.01" step="0.01" required>
                        </div>
                    </div>

                    <div class="mb-3" id="d_max_discount_wrap">
                        <label class="form-label fw-semibold">Maks. Potongan (Rp)</label>
                        <input type="number" name="max_discount" id="d_max_discount" class="form-control" min="0" step="1">
                        <div class="form-text">Kosongkan kalau tidak ingin dibatasi. Hanya berlaku untuk tipe Persen.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Minimal Belanja (Rp)</label>
                        <input type="number" name="min_purchase" id="d_min_purchase" class="form-control" min="0" step="1" value="0">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Berlaku Dari <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="valid_from" id="d_valid_from" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Berlaku Sampai <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="valid_until" id="d_valid_until" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Kuota Pemakaian</label>
                            <input type="number" name="quota" id="d_quota" class="form-control" min="1" step="1" placeholder="Kosongkan = tanpa batas">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Cabang</label>
                            <select name="branch_id" id="d_branch_id" class="form-select">
                                <option value="">Semua Cabang</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="d_is_active" value="1" checked>
                        <label class="form-check-label fw-semibold" for="d_is_active">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const discountModal = new bootstrap.Modal(document.getElementById('discountModal'));

function toggleMaxDiscountField() {
    const type = document.getElementById('d_type').value;
    const wrap = document.getElementById('d_max_discount_wrap');
    const label = document.getElementById('d_value_label');

    if (type === 'percentage') {
        wrap.style.display = 'block';
        label.textContent = 'Nilai (%) *';
    } else {
        wrap.style.display = 'none';
        document.getElementById('d_max_discount').value = '';
        label.textContent = 'Nilai (Rp) *';
    }
}

function openDiscountModal(data = null) {
    const form = document.getElementById('discountForm');
    const methodField = document.getElementById('discountMethodField');
    const title = document.getElementById('discountModalTitle');

    if (data) {
        title.textContent = 'Edit Kode Diskon';
        form.action = `{{ url('owner/discounts') }}/${data.id}`;
        methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('d_code').value = data.code;
        document.getElementById('d_type').value = data.type;
        document.getElementById('d_value').value = data.value;
        document.getElementById('d_max_discount').value = data.max_discount ?? '';
        document.getElementById('d_min_purchase').value = data.min_purchase;
        document.getElementById('d_valid_from').value = data.valid_from;
        document.getElementById('d_valid_until').value = data.valid_until;
        document.getElementById('d_quota').value = data.quota ?? '';
        document.getElementById('d_branch_id').value = data.branch_id ?? '';
        document.getElementById('d_is_active').checked = !!data.is_active;
    } else {
        title.textContent = 'Buat Kode Diskon';
        form.action = '{{ route("owner.discounts.store") }}';
        methodField.innerHTML = '';
        form.reset();
        document.getElementById('d_is_active').checked = true;
    }

    toggleMaxDiscountField();
    discountModal.show();
}
</script>
@endpush
