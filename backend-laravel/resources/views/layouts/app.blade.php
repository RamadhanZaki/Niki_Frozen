<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Niki Frozen') — Niki Frozen</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --nk-primary:     #0F4C81;
            --nk-accent:      #00B4D8;
            --nk-accent-soft: #e0f7fc;
            --nk-danger:      #e63946;
            --nk-success:     #2dc653;
            --nk-warning:     #f4a261;
            --nk-sidebar-bg:  #0d3d6b;
            --nk-sidebar-w:   240px;
            --nk-topbar-h:    56px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
            background: #f0f4f8;
            color: #1e293b;
        }

        /* ── SIDEBAR ── */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--nk-sidebar-w);
            height: 100vh;
            background: var(--nk-sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: transform .25s ease;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 20px 14px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            text-decoration: none;
        }
        .sidebar-brand .brand-icon {
            width: 36px; height: 36px;
            background: var(--nk-accent);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: #fff;
            flex-shrink: 0;
        }
        .sidebar-brand .brand-text { line-height: 1.2; }
        .sidebar-brand .brand-text strong {
            display: block;
            color: #fff;
            font-size: .95rem;
            font-weight: 700;
        }
        .sidebar-brand .brand-text span {
            color: var(--nk-accent);
            font-size: .68rem;
            font-weight: 500;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 12px 10px;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }

        .nav-label {
            font-size: .65rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255,255,255,.35);
            padding: 12px 10px 4px;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: rgba(255,255,255,.65);
            font-size: .82rem;
            font-weight: 500;
            transition: background .15s, color .15s;
            text-decoration: none;
        }
        .sidebar-nav .nav-link i {
            font-size: 1rem;
            width: 18px;
            text-align: center;
            flex-shrink: 0;
        }
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            background: rgba(255,255,255,.1);
            color: #fff;
        }
        .sidebar-nav .nav-link.active {
            background: var(--nk-accent);
            color: #fff;
        }

        .sidebar-footer {
            padding: 14px 12px;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-footer .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .user-avatar {
            width: 32px; height: 32px;
            background: var(--nk-accent);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: .75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .user-name  { color: #fff; font-size: .8rem; font-weight: 600; line-height: 1.2; }
        .user-role  { color: rgba(255,255,255,.45); font-size: .68rem; text-transform: capitalize; }

        /* ── TOPBAR ── */
        #topbar {
            position: fixed;
            top: 0;
            left: var(--nk-sidebar-w);
            right: 0;
            height: var(--nk-topbar-h);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 24px;
            z-index: 1030;
            gap: 12px;
        }
        #topbar .page-title {
            font-size: .9rem;
            font-weight: 600;
            color: #1e293b;
            flex: 1;
        }
        #topbar .topbar-badge {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: #f1f5f9;
            display: flex; align-items: center; justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: background .15s;
            text-decoration: none;
        }
        #topbar .topbar-badge:hover { background: #e2e8f0; }
        #sidebar-toggle {
            display: none;
            background: none; border: none;
            color: #64748b; font-size: 1.2rem;
            cursor: pointer;
        }

        /* ── MAIN CONTENT ── */
        #main-content {
            margin-left: var(--nk-sidebar-w);
            padding-top: var(--nk-topbar-h);
            min-height: 100vh;
        }
        .content-inner { padding: 24px; }

        /* ── CARDS ── */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 12px 12px 0 0 !important;
            padding: 14px 18px;
            font-weight: 600;
            color: #1e293b;
        }

        /* ── STAT CARDS ── */
        .stat-card {
            border-radius: 12px;
            padding: 18px 20px;
            border: 1px solid #e2e8f0;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .stat-card .stat-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem;
            margin-bottom: 12px;
        }
        .stat-card .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 3px;
        }
        .stat-card .stat-label {
            font-size: .73rem;
            color: #64748b;
            font-weight: 500;
        }

        /* ── BUTTONS ── */
        .btn-primary { background: var(--nk-primary); border-color: var(--nk-primary); }
        .btn-primary:hover { background: #0a3a6a; border-color: #0a3a6a; }
        .btn-outline-primary { color: var(--nk-primary); border-color: var(--nk-primary); }
        .btn-outline-primary:hover { background: var(--nk-primary); border-color: var(--nk-primary); }

        /* ── TABLES ── */
        .table { font-size: .825rem; }
        .table thead th {
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 14px;
        }
        .table tbody td { padding: 11px 14px; vertical-align: middle; }
        .table tbody tr:hover { background: #f8fafc; }

        /* ── ALERTS ── */
        .alert { border-radius: 10px; font-size: .82rem; }

        /* ── BADGES ── */
        .badge { font-weight: 500; font-size: .7rem; border-radius: 6px; }

        /* ── OVERLAY (mobile) ── */
        #sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 1039;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(calc(-1 * var(--nk-sidebar-w))); }
            #sidebar.open { transform: translateX(0); }
            #topbar { left: 0; }
            #main-content { margin-left: 0; }
            #sidebar-toggle { display: block; }
            #sidebar-overlay.show { display: block; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ══ SIDEBAR ══ --}}
<aside id="sidebar">
    <a href="#" class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-snow2"></i></div>
        <div class="brand-text">
            <strong>Niki Frozen</strong>
            <span>{{ session('role', 'sistem') }}</span>
        </div>
    </a>

    <nav class="sidebar-nav">
        @if(session('role') === 'owner')
            <div class="nav-label">Utama</div>
            <a href="{{ route('owner.dashboard') }}"
               class="nav-link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>

            <div class="nav-label">Manajemen</div>
            <a href="{{ route('owner.products') }}"
               class="nav-link {{ request()->routeIs('owner.products') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Produk
            </a>
            <a href="{{ route('owner.stocks') }}"
               class="nav-link {{ request()->routeIs('owner.stocks') ? 'active' : '' }}">
                <i class="bi bi-archive"></i> Stok
            </a>
            <a href="{{ route('owner.branches') }}"
               class="nav-link {{ request()->routeIs('owner.branches') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Cabang
            </a>
            <a href="{{ route('owner.shifts') }}"
               class="nav-link {{ request()->routeIs('owner.shifts') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Shift
            </a>

            <div class="nav-label">Laporan & Sistem</div>
            <a href="{{ route('owner.reports') }}"
               class="nav-link {{ request()->routeIs('owner.reports') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Laporan
            </a>
            <a href="{{ route('owner.settings') }}"
               class="nav-link {{ request()->routeIs('owner.settings') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Pengaturan
            </a>

        @elseif(session('role') === 'kasir')
            <div class="nav-label">Kasir</div>
            <a href="{{ route('kasir.pos') }}"
               class="nav-link {{ request()->routeIs('kasir.pos') ? 'active' : '' }}">
                <i class="bi bi-bag-check"></i> Point of Sale
            </a>
            <a href="{{ route('kasir.shift') }}"
               class="nav-link {{ request()->routeIs('kasir.shift') ? 'active' : '' }}">
                <i class="bi bi-play-circle"></i> Shift Saya
            </a>
            <a href="{{ route('kasir.transactions') }}"
               class="nav-link {{ request()->routeIs('kasir.transactions') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Transaksi
            </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">{{ strtoupper(substr(session('name', 'U'), 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ session('name', 'Pengguna') }}</div>
                <div class="user-role">{{ session('role', '-') }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm w-100"
                    style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.12);border-radius:8px;">
                <i class="bi bi-box-arrow-right me-1"></i> Keluar
            </button>
        </form>
    </div>
</aside>

<div id="sidebar-overlay"></div>

{{-- ══ TOPBAR ══ --}}
<header id="topbar">
    <button id="sidebar-toggle"><i class="bi bi-list"></i></button>
    <div class="page-title">@yield('page-title', 'Beranda')</div>

    @hasSection('breadcrumb')
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-0" style="font-size:.75rem;">
                @yield('breadcrumb')
            </ol>
        </nav>
    @endif

    <a href="#" class="topbar-badge">
        <i class="bi bi-bell" style="font-size:.9rem;"></i>
    </a>
</header>

{{-- ══ MAIN ══ --}}
<main id="main-content">
    <div class="content-inner">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Ada kesalahan input:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebar-overlay');
    const toggleBtn = document.getElementById('sidebar-toggle');

    function openSidebar()  { sidebar.classList.add('open');    overlay.classList.add('show'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('show'); }

    toggleBtn?.addEventListener('click', () =>
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar()
    );
    overlay.addEventListener('click', closeSidebar);
</script>

@stack('scripts')
</body>
</html>