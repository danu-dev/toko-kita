# TOKO KITA
### Product Requirement Document — Platform Marketplace UMKM
**Versi 1.0 — Agustus 2026**

---

## 0. Catatan Asumsi

Karena kamu bilang "3 role" tanpa merinci, gue tentuin sendiri komposisinya berdasarkan pola marketplace ala Gojek yang paling masuk akal untuk konteks UMKM:

1. **Admin** — pengelola platform (setara "Gojek internal ops")
2. **Penjual / Mitra UMKM** — pemilik toko yang jualan (setara "Merchant Partner")
3. **Pembeli / Pelanggan** — yang belanja (setara "Customer")

Kalau ternyata kamu maunya ada role Kurir terpisah (bukan penjual yang antar sendiri), tinggal bilang — tinggal gue tambahin jadi 4 role dengan sedikit penyesuaian alur pengiriman.

---

## 1. Ringkasan Proyek

**Nama produk:** Toko Kita
**Positioning:** Marketplace hyperlocal yang menghubungkan UMKM (warung, toko kelontong, kuliner rumahan, kerajinan) dengan pembeli di sekitar area mereka — dengan pengalaman pakai yang cepat, familiar, dan "kerasa Gojek" tapi dengan identitas visual sendiri.

**Kenapa "kerasa Gojek"?** Bukan soal niru warna hijau doang, tapi niru *filosofi UX*-nya:
- Satu warna dominan yang jadi identitas kuat di setiap layar
- Card-based layout, bottom navigation di mobile, banyak *quick action*
- Status pesanan yang hidup (live update, bukan cuma teks statis)
- Semua alur (cari → pesan → bayar → lacak → selesai) diringkas jadi tap sesedikit mungkin

**Target pengguna:** UMKM skala kecil-menengah di area urban/sub-urban Indonesia (mirip konteks Malang/Batu yang kamu kerjain di Tanilens), dan pembeli lokal yang terbiasa pakai aplikasi on-demand.

---

## 2. Tiga Role & Permission Matrix

| Fitur / Akses | Admin | Penjual (Mitra) | Pembeli |
|---|:---:|:---:|:---:|
| Kelola akun sendiri | ✅ | ✅ | ✅ |
| Verifikasi pendaftaran mitra baru | ✅ | ❌ | ❌ |
| Kelola kategori & master data | ✅ | ❌ | ❌ |
| Kelola komisi platform | ✅ | 👁️ (lihat saja) | ❌ |
| Buka/kelola toko sendiri | ❌ | ✅ | ❌ |
| CRUD produk & stok | ❌ | ✅ | ❌ |
| Terima/tolak/proses pesanan | ❌ | ✅ | ❌ |
| Ajukan pencairan dana | ❌ | ✅ | ❌ |
| Approve pencairan dana | ✅ | ❌ | ❌ |
| Browse & cari produk/toko | ✅ | ✅ | ✅ |
| Checkout & bayar | ❌ | ❌ | ✅ |
| Beri rating & ulasan | ❌ | ❌ | ✅ |
| Chat lintas role | ✅ (moderasi) | ✅ (dgn pembeli) | ✅ (dgn penjual) |
| Kelola banner/promo global | ✅ | ❌ (promo toko sendiri saja) | ❌ |
| Lihat laporan & analitik | ✅ (platform-wide) | ✅ (toko sendiri) | ❌ |
| Handle komplain/dispute | ✅ (final decision) | ✅ (respon awal) | ✅ (ajukan) |

Implementasi teknis: pakai **Spatie Laravel-Permission** dengan 3 role dasar (`admin`, `seller`, `buyer`) + granular permission per fitur, supaya kalau nanti butuh role turunan (misal "Admin Keuangan" atau "Seller Staff/kasir toko"), tinggal nambah permission bukan nulis ulang logic.

---

## 3. Alur Pengguna (User Flow) — Detail per Role

### 3.1 Alur Pembeli

```
Buka Toko Kita
     │
     ▼
[Deteksi lokasi otomatis] ──► Home: toko UMKM terdekat + kategori + rekomendasi
     │
     ▼
Belum login? ──► Daftar/Login (Google Sign-In / OTP WhatsApp / Email+Password)
     │
     ▼
Cari produk (search bar / filter kategori / jelajah per toko)
     │
     ▼
Buka detail produk ──► Tambah ke Keranjang  ATAU  Beli Sekarang
     │
     ▼
Keranjang: cek item dari 1+ toko (dipisah per toko, checkout terpisah — mirip multi-cart Gojek)
     │
     ▼
Checkout: pilih alamat (antar) / pickup di toko → pilih metode bayar (QRIS, e-wallet, VA, COD)
     │
     ▼
Konfirmasi pesanan terkirim ──► menunggu respon Penjual
     │
     ▼
Status pesanan berjalan (live, lihat state machine di bagian 3.4):
   Menunggu Konfirmasi → Diproses → Siap Diambil/Dikirim → Selesai
     │
     ▼
Pesanan diterima ──► Beri rating & ulasan ──► Poin loyalitas bertambah
     │
     ▼
(Opsional) Ajukan komplain/retur dalam 24 jam jika ada masalah
```

**Halaman pendukung:** Wishlist, Riwayat Transaksi, Chat dengan penjual, Poin & Voucher, Profil & Alamat Tersimpan.

### 3.2 Alur Penjual (Mitra UMKM)

```
Daftar sebagai Mitra
     │
     ▼
Isi data toko: nama, kategori, alamat, jam operasional, (opsional) NIB/dokumen legalitas
     │
     ▼
Status: PENDING ──► Admin verifikasi ──► APPROVED / REJECTED (dengan alasan)
     │
     ▼ (jika approved)
Onboarding: setup profil toko, area layanan, upload produk pertama (min. 1 produk utk aktif)
     │
     ▼
Toko LIVE di marketplace
     │
     ▼
Notifikasi pesanan masuk (push notif + suara, mirip notif order Gojek)
     │
     ▼
Terima (dalam batas waktu X menit, auto-reject jika tidak direspon) atau Tolak (wajib isi alasan)
     │
     ▼
Update status: Diproses → Siap Diambil / Dikirim (pilih kurir sendiri atau serahkan ke pembeli)
     │
     ▼
Pesanan Selesai ──► Dana masuk ke Saldo Toko (belum cair, status "tertahan" 1x24 jam anti-fraud)
     │
     ▼
Kelola: Produk & Stok | Promo Toko | Laporan Penjualan | Balas Chat & Ulasan
     │
     ▼
Ajukan Pencairan Dana ──► Admin approve ──► Dana cair ke rekening
```

### 3.3 Alur Admin

```
Login Admin (+ 2FA opsional untuk keamanan)
     │
     ▼
Dashboard: GMV, jumlah transaksi, mitra aktif, grafik pertumbuhan, komplain masuk
     │
     ▼
Antrean Verifikasi Mitra Baru ──► Review dokumen ──► Approve / Reject
     │
     ▼
Kelola Master Data: kategori produk, area layanan, metode pembayaran aktif
     │
     ▼
Monitoring Transaksi Real-time ──► Deteksi anomali (order stuck, komplain berulang)
     │
     ▼
Tangani Dispute: lihat riwayat chat & bukti ──► Putuskan (refund / tolak / mediasi)
     │
     ▼
Kelola Konten: banner promo homepage, notifikasi broadcast, komisi platform (%)
     │
     ▼
Approve/Reject pencairan dana mitra
     │
     ▼
Export Laporan (harian/bulanan) untuk keperluan bisnis/audit
```

### 3.4 State Machine Status Pesanan (inti dari "rasa Gojek")

Ini yang bikin UX-nya kerasa hidup — satu order punya satu status yang sinkron real-time ke 2 role sekaligus (pembeli & penjual):

```
 MENUNGGU_KONFIRMASI ──(penjual terima)──► DIPROSES ──► SIAP_DIAMBIL/DIKIRIM ──► SELESAI
         │                                    │                                      │
         │                                    │                                      └──► (komplain <24 jam) ──► RETUR_REFUND
         │                                    │
         └──(penjual tolak / timeout)──► DIBATALKAN ◄──(pembeli batalkan, hanya boleh sebelum diproses)
```

Setiap perpindahan status dicatat di tabel `order_status_histories` (audit trail) dan di-broadcast via WebSocket (Laravel Reverb) supaya pembeli lihat progress bar bergerak tanpa refresh — ini yang di desain gue sebut **"Status Pulse"** (lihat bagian Signature Element).

---

## 4. Design System

### 4.1 Filosofi Warna

Brief kamu eksplisit minta "kerasa Gojek", jadi gue pertahankan **hijau sebagai warna dominan** (itu bagian dari brief, bukan default yang harus gue hindari) — tapi gue kasih karakter berbeda: bukan hijau Gojek yang cenderung "stabilo", tapi hijau yang lebih dalam dan earthy (kerasa "pasar/UMKM segar", bukan korporat), dipadu aksen amber hangat yang ngingetin warna terpal/spanduk warung — biar identitasnya kerasa lokal, bukan cuma "Gojek versi lain".

| Token | Hex | Peran |
|---|---|---|
| **Kita Green** (Primary) | `#0E9F6E` | Tombol utama, nav aktif, logo, ikon aksi utama |
| **Deep Teal** (Primary Dark) | `#0B5A45` | Header, pressed state, teks di atas background terang, dark surface |
| **Pasar Amber** (Secondary/Accent) | `#F2A93B` | CTA sekunder, badge promo, highlight "Siap Diambil", rating bintang |
| **Ember Red** (Alert) | `#E15554` | Error, badge "Live"/mendesak, tombol batalkan/tolak |
| **Warm Paper** (Background) | `#FAF8F2` | Background utama app (hangat, bukan putih steril) |
| **Ink Charcoal** (Teks/Neutral) | `#1E2723` | Teks utama, ikon, border gelap — punya undertone hijau tipis biar nyatu sama palet |

Status warna semantik (turunan dari token di atas, konsisten dipakai di semua role):
- Sukses / Selesai → Kita Green
- Diproses / Menunggu → Pasar Amber
- Dibatalkan / Error → Ember Red
- Info netral → Deep Teal versi 20% opacity sebagai background chip

### 4.2 Tipografi

Tiga peran font, jangan campur lebih dari ini biar konsisten:

| Peran | Font | Weight | Dipakai untuk |
|---|---|---|---|
| **Display/Heading** | Plus Jakarta Sans | 700–800 | Judul halaman, nama toko, harga besar di kartu produk |
| **Body/UI** | Inter | 400–600 | Paragraf, label form, deskripsi produk, teks tabel admin |
| **Utility/Numerik** | JetBrains Mono | 500 | Nomor pesanan, kode struk, timestamp, nominal transaksi di laporan |

Kenapa kombinasi ini: Plus Jakarta Sans punya bentuk geometris-rounded yang ramah (mirip nuansa font custom yang dipakai app on-demand Indonesia), Inter tetap jadi standar keterbacaan tinggi di layar padat data (dashboard admin/penjual), dan JetBrains Mono dipakai khusus untuk angka/kode supaya nomor pesanan/nominal uang gampang di-scan mata — kerasa kayak struk kasir, cocok sama konteks UMKM.

Skala tipografi (mobile-first, pakai `rem`):
```
Display XL   2.25rem / 800   — judul promo homepage
Heading L    1.5rem  / 700   — judul halaman ("Toko Saya", "Pesanan")
Heading M    1.25rem / 700   — nama toko/produk di kartu
Body L       1rem    / 500   — teks utama
Body M       0.875rem/ 400   — deskripsi, caption
Label S      0.75rem / 600   — badge status, label form (uppercase, letter-spacing 0.03em)
Mono S       0.8125rem/ 500  — nomor order, nominal
```

### 4.3 Komponen & Layout

- **Bottom navigation** (mobile web, buyer-facing): Beranda | Pesanan | Chat | Akun — 4 item, ikon + label, item aktif pakai Kita Green dengan indikator pil di atas ikon.
- **Card produk**: rounded-2xl (16px), shadow tipis, foto rasio 1:1 di atas, harga pakai font Mono, badge "Terlaris"/"Promo" pojok kiri atas pakai Pasar Amber.
- **Sidebar dashboard** (Penjual & Admin, desktop): fixed left, background Deep Teal, item aktif highlight Kita Green tipis.
- **Signature Element — "Status Pulse"**: titik berdenyut (pulse animation halus, bukan berkedip agresif) di samping label status pesanan, warnanya ikut status semantik (amber saat diproses, hijau saat selesai). Elemen ini konsisten muncul di 3 tempat: kartu pesanan pembeli, list pesanan masuk penjual, dan monitoring transaksi admin — jadi satu bahasa visual yang menyatukan pengalaman 3 role sekaligus, sekaligus jadi elemen paling "kerasa hidup" di seluruh produk (analog ke titik lokasi live tracking driver Gojek, tapi di sini merepresentasikan progres pesanan).
- **Border radius**: konsisten 12–16px untuk card/button (bukan 0 ala broadsheet, bukan full-round ala pill semua) — biar kerasa "app" bukan "dokumen".
- **Motion**: transisi status pakai fade+slide halus 200ms, progress bar order pakai animasi width yang smooth saat status berubah (di-trigger dari WebSocket event, bukan polling).

---

## 5. Arsitektur Teknis (Laravel Modern)

### 5.1 Stack Utama

| Layer | Teknologi | Alasan |
|---|---|---|
| Framework | Laravel 11.x | LTS terbaru, fitur modern (invokable validation, `Number` helper, dll) |
| Reactivity | Livewire 3 + Volt | SPA-like tanpa perlu API terpisah untuk web, cocok tim kecil |
| Styling | Tailwind CSS v4 | Utility-first, gampang mapping ke design token di atas |
| Interaksi ringan | Alpine.js | Modal, dropdown, animasi Status Pulse |
| Real-time | Laravel Reverb | WebSocket native Laravel, buat live order status & chat |
| Role & Permission | Spatie Laravel-Permission | Standar industri, gampang di-extend |
| Search | Laravel Scout + Meilisearch | Pencarian produk/toko cepat & typo-tolerant |
| Payment Gateway | Midtrans / Xendit SDK | QRIS, VA, e-wallet — paling umum dipakai UMKM Indonesia |
| Notifikasi Push | OneSignal (Laravel Notification channel) | Konsisten dengan pengalaman kamu di SmartRT |
| Queue & Monitoring | Laravel Horizon + Redis | Handle broadcast, notif, export laporan async |
| Export Laporan | Laravel Excel (Maatwebsite) | Laporan penjualan penjual & admin |
| Image Processing | Intervention Image | Resize/optimasi foto produk otomatis |
| Testing | Pest | Testing yang lebih readable |
| API (future-proof) | Laravel Sanctum | Kalau nanti ada companion app Flutter (konsisten sama skillset kamu) |

### 5.2 Struktur Folder (Repository-Service-Controller — konsisten sama pola Tanilens)

```
app/
├── Models/
│   ├── User.php
│   ├── Store.php
│   ├── Product.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── OrderStatusHistory.php
│   ├── Payment.php
│   ├── Withdrawal.php
│   ├── Review.php
│   └── Wallet.php
├── Http/
│   └── Controllers/
│       ├── Admin/        (VerificationController, CategoryController, ReportController, ...)
│       ├── Seller/       (ProductController, OrderController, StoreProfileController, ...)
│       └── Buyer/        (CartController, CheckoutController, OrderTrackingController, ...)
├── Livewire/
│   ├── Admin/Dashboard.php
│   ├── Seller/IncomingOrders.php
│   ├── Buyer/ProductCatalog.php
│   └── Shared/StatusPulse.php        (komponen reusable lintas role)
├── Services/
│   ├── OrderService.php              (state machine transisi status)
│   ├── PaymentService.php            (integrasi Midtrans/Xendit)
│   ├── CommissionService.php         (hitung potongan platform)
│   └── NotificationService.php
├── Repositories/
│   ├── OrderRepository.php
│   ├── ProductRepository.php
│   └── StoreRepository.php
├── Policies/
│   ├── OrderPolicy.php
│   └── StorePolicy.php
├── Events/
│   └── OrderStatusUpdated.php        (broadcast ke Reverb)
└── Notifications/
    ├── OrderReceivedNotification.php
    └── WithdrawalApprovedNotification.php
```

### 5.3 Struktur Routing per Role

```
/                          → Landing/Home (buyer, publik)
/toko/{slug}               → Halaman toko publik
/produk/{slug}             → Detail produk

/akun/*                    → Profil, alamat, riwayat (buyer, auth)
/keranjang, /checkout      → Cart & checkout (buyer, auth)
/pesanan/{id}/lacak        → Live tracking status (buyer, auth)

/mitra/daftar              → Form pendaftaran mitra (public)
/mitra/dashboard           → Dashboard penjual (seller, auth)
/mitra/produk/*            → CRUD produk (seller)
/mitra/pesanan/*           → Kelola pesanan masuk (seller)
/mitra/laporan             → Laporan penjualan (seller)
/mitra/pencairan           → Ajukan withdrawal (seller)

/admin/dashboard           → Dashboard analitik (admin)
/admin/mitra/verifikasi    → Antrean verifikasi (admin)
/admin/kategori            → Master data kategori (admin)
/admin/transaksi           → Monitoring transaksi (admin)
/admin/dispute             → Penanganan komplain (admin)
/admin/pencairan           → Approve withdrawal mitra (admin)
/admin/pengaturan          → Komisi platform, banner, broadcast (admin)
```

Middleware per group pakai role guard dari Spatie (`role:admin`, `role:seller`, `role:buyer`), plus Policy untuk object-level authorization (misal penjual cuma bisa edit produk toko miliknya sendiri).

### 5.4 Struktur Database Inti (ERD ringkas)

```
users (id, name, email, phone, password, role, ...)
   │
   ├──< stores (id, user_id, name, slug, category_id, status[pending/approved/rejected], ...)
   │        │
   │        └──< products (id, store_id, name, slug, price, stock, category_id, ...)
   │                 └──< product_variants
   │
   ├──< addresses (id, user_id, label, lat, lng, detail, is_default)
   ├──< carts ──< cart_items (id, cart_id, product_id, qty)
   │
   └──< orders (id, buyer_id, store_id, status, subtotal, commission_fee, total, ...)
            ├──< order_items (id, order_id, product_id, qty, price)
            ├──< order_status_histories (id, order_id, from_status, to_status, changed_by, timestamp)
            ├──< payments (id, order_id, method, status, reference_id)
            └──< reviews (id, order_id, rating, comment)

wallets (id, store_id, balance, held_balance)
   └──< withdrawals (id, wallet_id, amount, status[pending/approved/rejected], bank_account)

categories (id, name, parent_id)      -- master data admin
banners (id, title, image, link, is_active)
chats ──< messages (id, chat_id, sender_id, body, read_at)
```

---

## 6. Fitur Utama per Role (Ringkasan)

**Pembeli:** Cari toko/produk terdekat • Multi-toko cart (checkout terpisah per toko) • Live order tracking • QRIS/e-wallet/VA/COD • Wishlist • Poin loyalitas • Rating & ulasan • Chat dengan penjual • Riwayat & invoice digital.

**Penjual (Mitra):** Onboarding & verifikasi toko • CRUD produk + varian & stok • Terima/tolak pesanan real-time • Update status pengiriman • Promo toko sendiri • Laporan penjualan (grafik harian/bulanan, export Excel) • Saldo & pencairan dana • Chat & balas ulasan.

**Admin:** Dashboard analitik platform (GMV, growth, retensi) • Verifikasi mitra baru • Kelola kategori & area layanan • Monitoring & dispute resolution • Kelola komisi platform • Approve pencairan dana • Banner & broadcast notifikasi • Export laporan & audit log.

---

## 7. Roadmap Pengembangan

| Fase | Fokus | Estimasi |
|---|---|---|
| **Fase 1 — MVP** | Auth 3 role, verifikasi mitra, CRUD produk, order dasar (tanpa real-time), pembayaran manual/transfer | 3–4 minggu |
| **Fase 2 — Core Experience** | Live tracking (Reverb), payment gateway (Midtrans/Xendit), chat, notifikasi push (OneSignal), Status Pulse | 3–4 minggu |
| **Fase 3 — Growth Features** | Promo/voucher, poin loyalitas, laporan & analitik lanjutan, dispute resolution admin | 2–3 minggu |
| **Fase 4 — Scale & Polish** | Search (Meilisearch), optimasi performa, wallet & withdrawal otomatis, audit log, hardening keamanan | 2–3 minggu |

---

## 8. Catatan Penutup

Dokumen ini dirancang sebagai fondasi — kalau kamu mau, gue bisa lanjutin ke salah satu dari ini:
- Breakdown **skema database lengkap** (migration-ready, dengan tipe kolom & index)
- **Wireframe visual** (mockup halaman kunci: Home buyer, Dashboard penjual, Dashboard admin)
- Kode starter Laravel (struktur project, Livewire component pertama, atau state machine `OrderService`)

Tinggal bilang mau lanjut ke bagian yang mana.
