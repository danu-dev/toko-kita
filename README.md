# 🏪 TokoKita. — Platform Marketplace Hyperlocal UMKM

**TokoKita.** adalah platform e-commerce hyperlocal yang menghubungkan usaha mikro, kecil, dan menengah (UMKM) seperti warung kelontong, kuliner rumahan, dan kriya lokal dengan pembeli di sekitar area mereka dengan pengalaman cepat dan modern (*Gojek-like UX*).

---

## 🌟 Fitur Utama

### 1. Tiga Role & Akses Terisolasi
- **Pembeli (Customer)**:
  - Deteksi lokasi otomatis & pemilihan titik pengantaran via peta interaktif (*OpenStreetMap Leaflet*).
  - Kalkulasi jarak presisi real-time dari posisi pengguna ke warung mitra (dalam meter / km).
  - Keranjang belanja multi-toko (*multi-cart Gojek style*).
  - Checkout fleksibel: **Pesan Antar / Ambil Sendiri (Pickup)**.
  - Pembayaran online (**QRIS Instan, GoPay, DANA, OVO, Virtual Account**) atau **Cash / Tunai di Tempat (COD)**.
  - Penukaran **Poin Loyalitas** untuk diskon belanja langsung.
  - Pelacakan pesanan real-time dengan status pulse berdenyut.
  - Chat langsung (*peer-to-peer*) dengan pemilik toko.
- **Penjual (Mitra UMKM)**:
  - Notifikasi pesanan masuk secara real-time (*Accept / Reject*).
  - Manajemen status order: *Diproses* &rarr; *Siap Diambil/Dikirim* &rarr; *Selesai*.
  - CRUD produk & varian harga dengan dukungan upload file langsung atau via URL.
  - Dompet digital (*Wallet*) & pengajuan pencairan dana (*Withdrawal*) ke rekening bank.
  - Laporan omset penjualan dan manajemen balasan ulasan pembeli.
- **Admin (Operations)**:
  - Dashboard analitik platform: GMV (*Gross Merchandise Value*), omset komisi platform (5%), total transaksi.
  - Antrean verifikasi berkas pendaftaran mitra baru (*Approve / Reject*).
  - Monitoring transaksi agregat platform.
  - Pusat resolusi dispute & komplain pesanan resmi.
  - Otorisasi dan persetujuan pencairan saldo dompet mitra.
  - Manajemen master kategori dan banner promosi homepage (upload file atau URL).

---

## 🎨 Design System & Palette
- **Kita Green** (`#0E9F6E`): Warna primer tombol dan nav aktif.
- **Deep Teal** (`#0B5A45`): Header dan sidebar admin/penjual.
- **Pasar Amber** (`#F2A93B`): Aksen promo dan rating bintang.
- **Warm Paper** (`#FAF8F2`): Latar belakang aplikasi yang ramah dan hangat.
- **Ink Charcoal** (`#1E2723`): Tipografi netral.

---

## 🚀 Loop Otomatisasi Kerja & Live Dev

Tersedia skrip automasi untuk mempermudah alur kerja harian Anda:

### 1. Menjalankan Dev Loop Lengkap (Server + Vite + DB + Auto-Test)
```bash
./bin/dev-loop.sh
```
Perintah ini akan secara otomatis:
- Menyiapkan database SQLite & migrasi.
- Menjalankan automated test suite.
- Menjalankan server Laravel di `http://localhost:8000`.
- Menjalankan Vite hot-module reload compiler.

### 2. CI/CD Otomatis GitHub Actions & Vercel
Setiap kali Anda melakukan `git push origin main`:
- GitHub Actions (`.github/workflows/ci-cd.yml`) otomatis memvalidasi dependensi PHP, Node.js, migrasi, dan seluruh 12 test suite.
- Vercel otomatis mengompilasi aset dan mendeploy pembaruan secara instan ke URL live: **[https://toko-kita-phi.vercel.app](https://toko-kita-phi.vercel.app)**.

---

## 🛠️ Instalasi & Menjalankan Aplikasi Lokal

1. **Clone repository & masuk ke folder project**:
   ```bash
   git clone https://github.com/danu-dev/toko-kita.git
   cd toko-kita
   ```

2. **Install dependensi PHP & Node.js**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi Database & Seeder**:
   ```bash
   php artisan migrate:fresh --seed
   php artisan storage:link
   ```

5. **Build Aset Frontend**:
   ```bash
   npm run build
   ```

6. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Buka browser di `http://localhost:8000`.

---

## 🔑 Akun Demo Pengujian

| Role | Email | Password | URL Dashboard |
|---|---|---|---|
| **Admin Operations** | `admin@tokokita.id` | `password` | `/admin/dashboard` |
| **Mitra Penjual** | `seller@tokokita.id` | `password` | `/mitra/dashboard` |
| **Pembeli** | `buyer@tokokita.id` | `password` | `/` |

---

## 🧪 Menjalankan Test Suite
```bash
php artisan test
```

---

## ☁️ Tautan Deployment

- **GitHub Repository**: [https://github.com/danu-dev/toko-kita](https://github.com/danu-dev/toko-kita)
- **Live Production Vercel**: [https://toko-kita-phi.vercel.app](https://toko-kita-phi.vercel.app)
