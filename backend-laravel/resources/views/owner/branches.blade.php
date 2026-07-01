@extends('layouts.app')
@section('title', 'Cabang')
@section('page-title', 'Manajemen Cabang')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Daftar Cabang</span>
        <button class="btn btn-sm btn-primary" onclick="openBranchModal()">
            <i class="bi bi-plus-lg me-1"></i> Tambah Cabang
        </button>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Cabang</th>
                        <th>Alamat</th>
                        <th>Telepon</th>
                        <th>Pengguna</th>
                        <th>Produk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $i => $b)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $b->name }}</td>
                        <td class="small text-muted">{{ $b->address ?? '-' }}</td>
                        <td class="small">{{ $b->phone ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $b->users_count }}</span></td>
                        <td><span class="badge bg-light text-dark border">{{ $b->products_count }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick='openBranchModal({{ json_encode([
                                    "id" => $b->id,
                                    "name" => $b->name,
                                    "address" => $b->address,
                                    "phone" => $b->phone,
                                ]) }})'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('owner.branches.destroy', $b->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirmDelete(this, 'Cabang {{ $b->name }} akan dihapus. Cabang dengan data terkait tidak dapat dihapus.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada cabang</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah/Edit Cabang --}}
<div class="modal fade" id="branchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="branchModalTitle">Tambah Cabang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="branchForm" method="POST" action="{{ route('owner.branches.store') }}">
                @csrf
                <div id="branchMethodField"></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Cabang <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="b_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat</label>
                        <textarea name="address" id="b_address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Telepon</label>
                        <input type="text" name="phone" id="b_phone" class="form-control" placeholder="Contoh: 081234567890">
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
const branchModal = new bootstrap.Modal(document.getElementById('branchModal'));

function openBranchModal(data = null) {
    const form = document.getElementById('branchForm');
    const methodField = document.getElementById('branchMethodField');
    const title = document.getElementById('branchModalTitle');

    if (data) {
        title.textContent = 'Edit Cabang';
        form.action = `{{ url('owner/branches') }}/${data.id}`;
        methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('b_name').value = data.name;
        document.getElementById('b_address').value = data.address ?? '';
        document.getElementById('b_phone').value = data.phone ?? '';
    } else {
        title.textContent = 'Tambah Cabang';
        form.action = '{{ route("owner.branches.store") }}';
        methodField.innerHTML = '';
        form.reset();
    }

    branchModal.show();
}
</script>
@endpush