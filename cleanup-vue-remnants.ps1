# cleanup-vue-remnants.ps1
#
# Membersihkan sisa-sisa backend API REST (peninggalan frontend Vue SPA lama)
# di project Niki_Frozen, branch exp. Aman dijalankan berkali-kali (idempotent).
#
# CARA PAKAI:
#   1. Pastikan kamu berada di root folder repo (yang ada folder "backend-laravel"),
#      di branch "exp".
#   2. Taruh file ini di root folder tsb.
#   3. Jalankan:  powershell -ExecutionPolicy Bypass -File .\cleanup-vue-remnants.ps1
#      (atau kalau execution policy sudah longgar, cukup: .\cleanup-vue-remnants.ps1)
#   4. Cek hasilnya dengan:  git status  &&  git diff
#   5. Kalau sudah oke:  git add -A; git commit -m "Bersihkan sisa API Vue lama"; git push
#
# Script ini TIDAK melakukan commit/push otomatis, supaya kamu bisa review dulu.

$ErrorActionPreference = "Stop"
$backend = "backend-laravel"

if (-not (Test-Path $backend)) {
    Write-Host "Folder '$backend' tidak ditemukan di sini." -ForegroundColor Red
    Write-Host "Jalankan script ini dari root folder repo (yang ada folder backend-laravel)."
    exit 1
}

Write-Host "== 1. Menghapus controller API lama (peninggalan Vue SPA) ==" -ForegroundColor Cyan
$filesToDelete = @(
    "$backend\app\Http\Controllers\AuthController.php",
    "$backend\app\Http\Controllers\BranchController.php",
    "$backend\app\Http\Controllers\Dashboardcontroller.php",
    "$backend\app\Http\Controllers\Productcontroller.php",
    "$backend\app\Http\Controllers\ReportController.php",
    "$backend\app\Http\Controllers\SettingController.php",
    "$backend\app\Http\Controllers\ShiftController.php",
    "$backend\app\Http\Controllers\Stockcontroller.php",
    "$backend\app\Http\Controllers\UserController.php",
    "$backend\app\Http\Controllers\Providers\Appserviceprovider.php",
    "$backend\routes\api.php"
)

foreach ($f in $filesToDelete) {
    if (Test-Path $f) {
        Remove-Item $f -Force
        Write-Host "  - dihapus: $f"
    } else {
        Write-Host "  - (skip, sudah tidak ada): $f"
    }
}

# Hapus folder Providers di dalam Controllers kalau sudah kosong
$provDir = "$backend\app\Http\Controllers\Providers"
if (Test-Path $provDir) {
    if ((Get-ChildItem $provDir -Force | Measure-Object).Count -eq 0) {
        Remove-Item $provDir -Force
        Write-Host "  - folder kosong dihapus: $provDir"
    }
}

Write-Host ""
Write-Host "== 2. Membersihkan bootstrap/app.php (hapus registrasi routes/api.php) ==" -ForegroundColor Cyan
$appPhp = "$backend\bootstrap\app.php"
if (Test-Path $appPhp) {
    $content = Get-Content $appPhp
    if ($content -match "routes/api\.php") {
        $newContent = $content | Where-Object { $_ -notmatch "api:\s*__DIR__\.'/\.\./routes/api\.php'," }
        Set-Content -Path $appPhp -Value $newContent
        Write-Host "  - baris 'api: routes/api.php' dihapus dari $appPhp"
    } else {
        Write-Host "  - (skip, sudah bersih): $appPhp"
    }
}

Write-Host ""
Write-Host "== 3. Membersihkan config/cors.php (hapus origin localhost:3000 Vue dev server) ==" -ForegroundColor Cyan
$corsPhp = "$backend\config\cors.php"
if (Test-Path $corsPhp) {
    $corsContent = Get-Content $corsPhp -Raw
    if ($corsContent -match "3000") {
        @'
<?php

return [
    'paths' => [],
    'allowed_methods' => ['*'],
    'allowed_origins' => [],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
'@ | Set-Content -Path $corsPhp
        Write-Host "  - $corsPhp dibersihkan"
    } else {
        Write-Host "  - (skip, sudah bersih atau tidak ada): $corsPhp"
    }
}

Write-Host ""
Write-Host "== 4. Membersihkan config/sanctum.php (hapus localhost:3000 dari stateful domains) ==" -ForegroundColor Cyan
$sanctumPhp = "$backend\config\sanctum.php"
if (Test-Path $sanctumPhp) {
    $sanctumContent = Get-Content $sanctumPhp -Raw
    if ($sanctumContent -match "localhost:3000") {
        $newSanctum = $sanctumContent -replace "'localhost,localhost:3000,127\.0\.0\.1,127\.0\.0\.1:8000,::1'", "'localhost,127.0.0.1,127.0.0.1:8000,::1'"
        Set-Content -Path $sanctumPhp -Value $newSanctum -NoNewline
        Write-Host "  - localhost:3000 dihapus dari $sanctumPhp"
    } else {
        Write-Host "  - (skip, sudah bersih atau tidak ada): $sanctumPhp"
    }
}

Write-Host ""
Write-Host "== 5. Refresh autoload Composer (kalau composer tersedia) ==" -ForegroundColor Cyan
if (Get-Command composer -ErrorAction SilentlyContinue) {
    Push-Location $backend
    try {
        composer dump-autoload
    } catch {
        Write-Host "  Peringatan: composer dump-autoload gagal, jalankan manual nanti." -ForegroundColor Yellow
    }
    Pop-Location
} else {
    Write-Host "  Peringatan: composer tidak ditemukan di PATH, skip langkah ini." -ForegroundColor Yellow
    Write-Host "  Jalankan manual: cd $backend; composer dump-autoload"
}

Write-Host ""
Write-Host "Selesai. Sekarang cek hasilnya:" -ForegroundColor Green
Write-Host "  git status"
Write-Host "  git diff"
Write-Host ""
Write-Host "Kalau sudah oke, baru commit & push:"
Write-Host "  git add -A"
Write-Host "  git commit -m `"Bersihkan sisa API Vue lama`""
Write-Host "  git push"
