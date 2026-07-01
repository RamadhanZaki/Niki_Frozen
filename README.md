# 🧊 Niki Frozen

> **Sistem POS & Monitoring Keuangan Multi-Cabang**
> Tim NICE GANK | AMIKOM 2025/2026

Sistem POS & Monitoring Keuangan Niki Frozen dirancang menggunakan arsitektur **web monolith berbasis Laravel**. Arsitektur ini dipilih karena sistem harus dapat diakses dari beberapa cabang secara bersamaan melalui browser tanpa perlu instalasi perangkat lunak khusus di tiap perangkat kasir.

Dengan **Laravel Blade** sebagai server-rendered frontend dan **Laravel** sebagai backend dalam satu aplikasi yang sama, setiap transaksi penjualan, laporan keuangan, dan data stok produk frozen food dapat dipantau secara *real-time* dari seluruh cabang dalam satu dashboard terpadu — kapan saja dan di mana saja.

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|---|---|
| Frontend | Laravel Blade |
| Backend | Laravel (PHP) |
| Database | MySQL / MariaDB |
| Local Server | XAMPP |
| Auth | Laravel Session-based Auth |

---

## 👥 Anggota Kelompok

| No | Nama Lengkap | NIM | GitHub |
|---|---|---|---|
| 1 | Fata Salma Putra Pureka | 23.11.5470 | — |
| 2 | Sandi Setiawan | 23.11.5443 | — |
| 3 | Ramadhan Zaki Attamimi | 23.11.5500 | [RamadhanZaki](https://github.com/RamadhanZaki) |
| 4 | Irvanza Darwandha Kusuma | 23.11.5784 | — |
| 5 | Nathan Farros Kusuma Barracuda | 23.11.5780 | — |

---

## 📁 Struktur Repository

```
Niki_Frozen/
├── backend-laravel/     # Laravel App (Backend + Blade Frontend)
├── database/            # File SQL database
└── README.md
```

---

## ⚙️ Requirements

Pastikan tools berikut sudah terinstal sebelum memulai:

- [XAMPP](https://www.apachefriends.org/) (PHP >= 8.1 + MySQL)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) >= 18.x & npm (untuk build asset Vite bawaan Laravel — CSS/JS)
- [Git](https://git-scm.com/)

---

## 📘 Panduan Instalasi — XAMPP & Setup Project Laravel

### 1. Persiapan XAMPP

1. Download dan install **XAMPP** dari [apachefriends.org](https://www.apachefriends.org/)
2. Buka **XAMPP Control Panel**
3. Jalankan modul **Apache** dan **MySQL** dengan klik tombol **Start**

> ✅ Pastikan kedua modul berstatus **Running** (hijau) sebelum lanjut

---

### 2. Clone Repository

Buka terminal / Git Bash, lalu jalankan:

```bash
git clone https://github.com/RamadhanZaki/Niki_Frozen.git .
```

---

### 3. Setup Database

1. Buka browser, akses **phpMyAdmin**: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Klik **New** → buat database baru bernama `niki_frozen`
3. Pilih database `niki_frozen` → klik tab **Import**
4. Klik **Choose File** → pilih file dari `database/niki_frozen.sql`
5. Klik **Go** / **Import**

> 💡 Atau import via terminal:
> ```bash
> mysql -u root -p niki_frozen < database/niki_frozen.sql
> ```

---

### 4. Setup Backend (Laravel)

Masuk ke folder backend:

```bash
cd backend-laravel
```

Install dependencies PHP via Composer:

```bash
composer install
```

Salin file konfigurasi environment:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Buka file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=niki_frozen
DB_USERNAME=root
DB_PASSWORD=
```

> ⚠️ Untuk XAMPP, `DB_USERNAME` default adalah `root` dan `DB_PASSWORD` dikosongkan.

Install dependencies Node.js untuk build asset frontend (CSS/JS Blade via Vite):

```bash
npm install
npm run build
```

> 💡 Untuk mode development dengan hot-reload, jalankan `npm run dev` di terminal terpisah.

Jalankan server Laravel:

```bash
php artisan serve
```

✅ Aplikasi (backend + tampilan Blade) berjalan di: `http://127.0.0.1:8000`

---

## 🔗 Ringkasan Akses

| Service | URL |
|---|---|
| Aplikasi (Login, Owner, Kasir) | http://127.0.0.1:8000 |
| phpMyAdmin | http://localhost/phpmyadmin |

---

## 📄 Lisensi

Project ini dibuat untuk keperluan akademik — Final Project Mata Kuliah Pemrograman Web.
**AMIKOM Yogyakarta 2025/2026**