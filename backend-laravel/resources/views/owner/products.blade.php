@extends('layouts.app')
@section('title', 'Produk')
@section('page-title', 'Manajemen Produk')

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#e8f4fd"><i class="bi bi-box fs-4 text-primary"></i></div>
                <div>
                    <div class="text-muted small">Total Produk</div>
                    <div class="fw-bold fs-5">{{ $products->total() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#fef9e7"><i class="bi bi-clock-history fs-4 text-warning"></i></div>
                <div>
                    <div class="text-muted small">Akan Expired (7 hari)</div>
                    <div class="fw-bold fs-5">{{ $expiring_soon }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#fdf2f8"><i class="bi bi-x-circle fs-4 text-danger"></i></div>
                <div>
                    <div class="text-muted small">Sudah Expired</div>
                    <div class="fw-bold fs-5">{{ $expired_count }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#eafaf1"><i class="bi bi-archive fs-4 text-success"></i></div>
                <div>
                    <div class="text-muted small">Stok Menipis</div>
                    <div class="fw-bold fs-5">{{ $low_stock }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold">Daftar Produk</span>
        <button class="btn btn-sm btn-primary" onclick="openProductModal()">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk
        </button>
    </div>

    {{-- Filter --}}
    <div class="card-body pb-0">
        <form method="GET" action="{{ route('owner.products') }}" class="row g-2">
            <div class="col-sm-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama produk..." value="{{ request('search') }}">
            </div>
            <div class="col-sm-3">
                <select name="category" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach(['Frozen', 'Snack', 'Dessert', 'Minuman', 'Lainnya'] as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('owner.products') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>

    <div class="card-body p-0 mt-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Cabang</th>
                        <th>Stok</th>
                        <th>Expired</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $i => $p)
                    @php
                        $expDate = \Carbon\Carbon::parse($p->expired_date);
                        $isExpired = $expDate->isPast();
                        $isSoon = !$isExpired && $expDate->diffInDays(now()) <= 7;
                    @endphp
                    <tr>
                        <td>
                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}"
                                 class="rounded-2" style="width:40px;height:40px;object-fit:cover;">
                        </td>
                        <td class="text-muted small">{{ $products->firstItem() + $i }}</td>
                        <td class="fw-semibold">{{ $p->name }}</td>
                        <td><span class="badge bg-secondary">{{ $p->category }}</span></td>
                        <td class="small">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                        <td class="small">{{ $p->branch?->name ?? '-' }}</td>
                        <td class="small">{{ $p->stock?->quantity ?? 0 }}</td>
                        <td>
                            <span class="badge {{ $isExpired ? 'bg-danger' : ($isSoon ? 'bg-warning text-dark' : 'bg-light text-dark border') }}">
                                {{ $expDate->format('d/m/Y') }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick='openProductModal({{ json_encode([
                                    "id" => $p->id,
                                    "name" => $p->name,
                                    "category" => $p->category,
                                    "price" => (float)$p->price,
                                    "expired_date" => $p->expired_date,
                                    "branch_id" => $p->branch_id,
                                    "stock" => $p->stock?->quantity ?? 0,
                                    "min_stock" => $p->stock?->min_stock ?? 10,
                                    "image_url" => $p->image_url,
                                ]) }})'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('owner.products.destroy', $p->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus produk {{ $p->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada produk</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $products->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- Modal Tambah/Edit Produk --}}
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">Tambah Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm" method="POST" action="{{ route('owner.products.store') }}" enctype="multipart/form-data">
                @csrf
                <div id="methodField"></div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <img id="f_preview" src="{{ asset('images/no-product.svg') }}" alt="Preview"
                             class="rounded-2 mb-2" style="width:90px;height:90px;object-fit:cover;border:1px solid #dee2e6;">
                        <input type="file" name="image" id="f_image" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp">
                        <div class="form-text">Opsional. Format JPG/PNG/WEBP, maks 2MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="f_name" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select name="category" id="f_category" class="form-select" required>
                                @foreach(['Frozen', 'Snack', 'Dessert', 'Minuman', 'Lainnya'] as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Harga <span class="text-danger">*</span></label>
                            <input type="number" name="price" id="f_price" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Tanggal Expired <span class="text-danger">*</span></label>
                            <input type="date" name="expired_date" id="f_expired_date" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Cabang <span class="text-danger">*</span></label>
                            <select name="branch_id" id="f_branch_id" class="form-select" required>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Stok Awal <span class="text-danger">*</span></label>
                            <input type="number" name="stock" id="f_stock" class="form-control" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Stok Minimum</label>
                            <input type="number" name="min_stock" id="f_min_stock" class="form-control" min="0" placeholder="Default: 10">
                        </div>
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
const productModal = new bootstrap.Modal(document.getElementById('productModal'));

function openProductModal(data = null) {
    const form = document.getElementById('productForm');
    const methodField = document.getElementById('methodField');
    const title = document.getElementById('productModalTitle');
    const preview = document.getElementById('f_preview');

    if (data) {
        title.textContent = 'Edit Produk';
        form.action = `{{ url('owner/products') }}/${data.id}`;
        // Form file upload tidak bisa kirim method PUT asli, jadi pakai method spoofing
        methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('f_name').value = data.name;
        document.getElementById('f_category').value = data.category;
        document.getElementById('f_price').value = data.price;
        document.getElementById('f_expired_date').value = data.expired_date.substring(0, 10);
        document.getElementById('f_branch_id').value = data.branch_id;
        document.getElementById('f_stock').value = data.stock;
        document.getElementById('f_min_stock').value = data.min_stock;
        preview.src = data.image_url;
    } else {
        title.textContent = 'Tambah Produk';
        form.action = '{{ route("owner.products.store") }}';
        methodField.innerHTML = '';
        form.reset();
        preview.src = '{{ asset("images/no-product.svg") }}';
    }

    document.getElementById('f_image').value = '';
    productModal.show();
}

// Preview gambar saat user pilih file baru
document.getElementById('f_image').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
        document.getElementById('f_preview').src = ev.target.result;
    };
    reader.readAsDataURL(file);
});
</script>
@endpush