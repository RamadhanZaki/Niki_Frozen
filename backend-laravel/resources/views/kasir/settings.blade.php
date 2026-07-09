@extends('layouts.app')
@section('title', 'Pengaturan Akun')
@section('page-title', 'Pengaturan Akun')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <span class="fw-semibold">Ubah Profil & Password</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('kasir.settings.update') }}" id="settingsForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', auth()->user()->name) }}" maxlength="100" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', auth()->user()->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr class="my-4">

                    <p class="text-muted small mb-3">
                        Kosongkan bagian di bawah ini kalau kamu tidak ingin mengganti password.
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimal 8 karakter">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">
                            Harus mengandung huruf besar, huruf kecil, angka, dan simbol (mis. <code>!@#$%</code>).
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Verifikasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control" placeholder="Ulangi password baru">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Form tersembunyi khusus untuk logout paksa setelah profil berhasil
     diupdate — dipicu dari tombol OK di pop-up SweetAlert di bawah. --}}
<form method="POST" action="{{ route('logout') }}" id="forceLogoutForm" class="d-none">
    @csrf
    {{-- Tandai ini sebagai logout demi keamanan, supaya tidak diblokir
         validasi "shift masih terbuka" di AuthWebController::logout() --}}
    <input type="hidden" name="reason" value="security">
</form>

@endsection

@push('scripts')
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon  = btn.querySelector('i');
        const isHidden = input.type === 'password';

        input.type = isHidden ? 'text' : 'password';
        icon.classList.toggle('bi-eye', !isHidden);
        icon.classList.toggle('bi-eye-slash', isHidden);
    }

    @if(session('profile_updated'))
        // Profil berhasil diupdate. Kasir WAJIB menekan OK sebelum bisa
        // melanjutkan (allowOutsideClick/Escape dimatikan), lalu otomatis
        // logout & diarahkan ke halaman login — supaya kasir langsung login
        // ulang memakai email/password barunya.
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data akun kamu berhasil diperbarui. Silakan login ulang.',
            confirmButtonText: 'OK',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(() => {
            document.getElementById('forceLogoutForm').submit();
        });
    @endif
</script>
@endpush
