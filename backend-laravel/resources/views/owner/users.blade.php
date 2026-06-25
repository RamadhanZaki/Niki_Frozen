@extends('layouts.app')
@section('title', 'Pengguna')
@section('page-title', 'Manajemen Pengguna (Kasir)')

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#e8f4fd"><i class="bi bi-people fs-4 text-primary"></i></div>
                <div>
                    <div class="text-muted small">Total Kasir</div>
                    <div class="fw-bold fs-5">{{ $total_users }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#eafaf1"><i class="bi bi-person-check fs-4 text-success"></i></div>
                <div>
                    <div class="text-muted small">Kasir Aktif</div>
                    <div class="fw-bold fs-5">{{ $active_cashiers }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold">Daftar Kasir</span>
        <button class="btn btn-sm btn-primary" onclick="openUserModal()">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kasir
        </button>
    </div>

    {{-- Filter --}}
    <div class="card-body pb-0">
        <form method="GET" action="{{ route('owner.users') }}" class="row g-2">
            <div class="col-sm-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama / email..." value="{{ request('search') }}">
            </div>
            <div class="col-sm-4">
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('owner.users') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>

    <div class="card-body p-0 mt-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Cabang</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $u)
                    <tr>
                        <td class="text-muted small">{{ $users->firstItem() + $i }}</td>
                        <td class="fw-semibold">{{ $u->name }}</td>
                        <td class="small">{{ $u->email }}</td>
                        <td class="small">{{ $u->branch?->name ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $u->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $u->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ \Carbon\Carbon::parse($u->created_at)->format('d/m/Y') }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick='openUserModal({{ json_encode([
                                    "id" => $u->id,
                                    "name" => $u->name,
                                    "email" => $u->email,
                                    "branch_id" => $u->branch_id,
                                    "status" => $u->status,
                                ]) }})'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning"
                                onclick='openResetModal({{ $u->id }}, {{ json_encode($u->name) }})'>
                                <i class="bi bi-key"></i>
                            </button>
                            <form action="{{ route('owner.users.destroy', $u->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus akun kasir {{ $u->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada akun kasir</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $users->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- Modal Tambah/Edit Kasir --}}
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Tambah Kasir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm" method="POST" action="{{ route('owner.users.store') }}">
                @csrf
                <div id="userMethodField"></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="u_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="u_email" class="form-control" required>
                    </div>
                    <div class="mb-3" id="u_password_wrapper">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="u_password" class="form-control" minlength="6">
                        <div class="form-text">Minimal 6 karakter. Kosongkan saat edit jika tidak ingin mengubah.</div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Cabang</label>
                            <select name="branch_id" id="u_branch_id" class="form-select">
                                <option value="">- Tanpa Cabang -</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" id="u_status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
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

{{-- Modal Reset Password --}}
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resetForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted">Reset password untuk <strong id="reset_user_name"></strong>.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" minlength="6" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const userModal  = new bootstrap.Modal(document.getElementById('userModal'));
const resetModal = new bootstrap.Modal(document.getElementById('resetModal'));

function openUserModal(data = null) {
    const form = document.getElementById('userForm');
    const methodField = document.getElementById('userMethodField');
    const title = document.getElementById('userModalTitle');
    const passwordField = document.getElementById('u_password');
    const passwordWrapper = document.getElementById('u_password_wrapper');

    if (data) {
        title.textContent = 'Edit Kasir';
        form.action = `{{ url('owner/users') }}/${data.id}`;
        methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('u_name').value = data.name;
        document.getElementById('u_email').value = data.email;
        document.getElementById('u_branch_id').value = data.branch_id ?? '';
        document.getElementById('u_status').value = data.status;

        passwordField.required = false;
        passwordWrapper.style.display = 'none';
    } else {
        title.textContent = 'Tambah Kasir';
        form.action = '{{ route("owner.users.store") }}';
        methodField.innerHTML = '';
        form.reset();

        passwordField.required = true;
        passwordWrapper.style.display = 'block';
    }

    userModal.show();
}

function openResetModal(id, name) {
    document.getElementById('resetForm').action = `{{ url('owner/users') }}/${id}/reset-password`;
    document.getElementById('reset_user_name').textContent = name;
    resetModal.show();
}
</script>
@endpush