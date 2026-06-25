<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Niki Frozen</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --nk-primary: #0F4C81;
            --nk-accent:  #00B4D8;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            font-size: .875rem;
            margin: 0;
            min-height: 100vh;
            display: flex;
        }

        /* ── LEFT PANEL ── */
        .login-left {
            width: 50%;
            background: linear-gradient(145deg, #0d3d6b 0%, var(--nk-primary) 50%, #1a6bb0 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: rgba(0,180,216,.15);
        }
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
        }
        .left-content { position: relative; z-index: 1; text-align: center; max-width: 380px; }
        .left-logo {
            width: 72px; height: 72px;
            background: var(--nk-accent);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; color: #fff;
            margin: 0 auto 24px;
            box-shadow: 0 8px 24px rgba(0,180,216,.35);
        }
        .left-content h1 { color: #fff; font-size: 1.85rem; font-weight: 700; margin-bottom: 8px; }
        .left-content p  { color: rgba(255,255,255,.6); font-size: .875rem; line-height: 1.6; margin-bottom: 36px; }

        .left-stats { display: flex; gap: 12px; }
        .left-stat {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 12px;
            padding: 12px 16px;
            text-align: center;
            flex: 1;
        }
        .left-stat .val { font-size: 1.2rem; font-weight: 700; color: var(--nk-accent); display: block; line-height: 1; }
        .left-stat .lbl { font-size: .65rem; color: rgba(255,255,255,.5); margin-top: 4px; display: block; text-transform: uppercase; letter-spacing: .05em; }

        /* ── RIGHT PANEL ── */
        .login-right {
            width: 50%;
            background: #f0f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 32px;
        }
        .login-box { width: 100%; max-width: 380px; }
        .login-box h2     { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
        .login-box .subtitle { color: #64748b; font-size: .82rem; margin-bottom: 28px; }

        .form-label { font-weight: 500; color: #374151; font-size: .8rem; margin-bottom: 5px; }
        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 10px 14px;
            font-size: .85rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus {
            border-color: var(--nk-accent);
            box-shadow: 0 0 0 3px rgba(0,180,216,.15);
            outline: none;
        }
        .form-control.is-invalid { border-color: #e63946; }
        .input-group-text {
            background: #f8fafc;
            border: 1px solid #d1d5db;
            border-radius: 8px 0 0 8px;
            color: #9ca3af;
        }
        .input-group .form-control { border-left: none; border-radius: 0 8px 8px 0; }
        .input-group .form-control:focus { z-index: 3; }

        .btn-login {
            background: var(--nk-primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px;
            font-size: .875rem;
            font-weight: 600;
            width: 100%;
            transition: background .15s, transform .1s;
        }
        .btn-login:hover  { background: #0a3a6a; }
        .btn-login:active { transform: scale(.98); }

        .eye-toggle {
            cursor: pointer;
            background: #f8fafc;
            border: 1px solid #d1d5db;
            border-left: none;
            border-radius: 0 8px 8px 0;
            color: #9ca3af;
            padding: 0 12px;
        }
        .eye-toggle:hover { color: #64748b; }

        @media (max-width: 767.98px) {
            body { flex-direction: column; }
            .login-left  { width: 100%; padding: 32px 24px; }
            .left-content h1 { font-size: 1.4rem; }
            .left-stats  { display: none; }
            .login-right { width: 100%; padding: 32px 20px; }
        }
    </style>
</head>
<body>

<div class="login-left">
    <div class="left-content">
        <div class="left-logo"><i class="bi bi-snow2"></i></div>
        <h1>Niki Frozen</h1>
        <p>Sistem manajemen toko frozen food terpadu — kontrol stok, transaksi, dan laporan dari satu tempat.</p>
        <div class="left-stats">
            <div class="left-stat"><span class="val">POS</span><span class="lbl">Kasir cepat</span></div>
            <div class="left-stat"><span class="val">Stok</span><span class="lbl">Real-time</span></div>
            <div class="left-stat"><span class="val">Cabang</span><span class="lbl">Multi-lokasi</span></div>
        </div>
    </div>
</div>

<div class="login-right">
    <div class="login-box">
        <h2>Selamat Datang 👋</h2>
        <p class="subtitle">Masuk ke akun Niki Frozen Anda</p>

        @if($errors->any())
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 py-2 px-3"
                 style="font-size:.8rem;border-radius:8px;">
                <i class="bi bi-exclamation-circle-fill"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/login">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope" style="font-size:.85rem;"></i></span>
                    <input type="email" id="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="nama@domain.com"
                           autocomplete="email" autofocus required>
                </div>
                @error('email')
                    <div class="text-danger mt-1" style="font-size:.75rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock" style="font-size:.85rem;"></i></span>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="••••••••"
                           autocomplete="current-password" required>
                    <button type="button" class="eye-toggle" id="togglePwd" tabindex="-1">
                        <i class="bi bi-eye" id="eyeIcon" style="font-size:.85rem;"></i>
                    </button>
                </div>
                @error('password')
                    <div class="text-danger mt-1" style="font-size:.75rem;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-arrow-right-circle me-1"></i> Masuk
            </button>
        </form>

        <p class="text-center mt-4 mb-0" style="color:#94a3b8;font-size:.72rem;">
            Niki Frozen &copy; {{ date('Y') }} — Sistem POS Internal
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const pwd = document.getElementById('password');
    const eye = document.getElementById('eyeIcon');
    document.getElementById('togglePwd')?.addEventListener('click', () => {
        const show = pwd.type === 'password';
        pwd.type = show ? 'text' : 'password';
        eye.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        eye.style.fontSize = '.85rem';
    });
</script>
</body>
</html>