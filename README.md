# Dynamic Auction Engine

Aplikasi lelang sederhana dengan kategori listing dinamis.

> **Status:** Work in progress.

---

## Tech Stack

| Tool | Fungsi |
|---|---|
| Laravel 13 (PHP 8.3+) | Framework utama |
| MySQL | Database |
| Laravel Breeze (Livewire — Volt Class API) | Scaffolding autentikasi |
| Livewire + Volt | Komponen interaktif tanpa AJAX/JS manual |
| Pest | Testing framework |
| Laravel Reverb | WebSocket server self-hosted untuk real-time broadcasting 
| Vite | Compile asset CSS (Tailwind) & JS (Alpine.js, Echo) |

---

## Cara Instalasi & Menjalankan

```bash
# 1. Install dependency PHP & JS
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate
# lalu isi kredensial database MySQL di .env

# 3. Migrate & seed
php artisan migrate:fresh --seed

# 4. Jalankan (butuh 3 proses berjalan bersamaan)
php artisan serve
php artisan reverb:start
npm run dev
```

Akun percobaan (dari seeder), semua pakai password `password`:
- asep@example.com
- budi@example.com
- citra@example.com

---

## Struktur Database

### `categories`
| Kolom | Alasan |
|---|---|
| `name` | Nama tampil, misal "Ikan Hias", "Burung" |
| `slug` | Key stabil untuk filter/logic, tidak terpengaruh perubahan kapital/spasi pada `name` |

### `listings`
| Kolom | Alasan |
|---|---|
| `user_id` (FK, cascade delete) | Seller/pemilik listing. Cascade karena listing tidak punya arti tanpa pemiliknya |
| `category_id` (FK) | Menentukan field dinamis mana yang berlaku pada `specs` |
| `specs` (JSON) | Field spesifikasi dinamis sesuai kategori (lihat penjelasan di bawah) |
| `photo_path` | Foto listing (upload lokal atau URL) |
| `starting_price` | Harga awal, tetap sebagai acuan, tidak berubah selama lelang berjalan |
| `current_price` | Harga tertinggi saat ini, di-update tiap ada bid valid — menghindari query `MAX()` ke tabel `bids` tiap kali halaman list diakses |
| `current_winner_id` (FK, nullable) | Bidder dengan bid tertinggi saat ini, dikunci saat status berubah jadi `ended` |
| `auction_start` / `auction_end` | Waktu mulai/berakhir lelang, dasar perhitungan "waktu tersisa" dan trigger auto-close |
| `status` (enum: `active`, `ended`) | Status lelang saat ini |

### `bids`
| Kolom | Alasan |
|---|---|
| `listing_id` (FK, cascade delete) | Bid ini ditujukan untuk listing yang mana |
| `user_id` (FK) | Bidder — kolom `user_id` di sini bermakna berbeda dari `listings.user_id` (seller), meski sama-sama merujuk ke tabel `users`. Satu akun bisa berperan sebagai keduanya |
| `amount` (decimal 12,2) | Nominal bid. Pakai `decimal`, bukan `float`, untuk menghindari masalah presisi pembulatan pada nilai uang |

---

## Keputusan Teknis & Asumsi

### Kenapa Breeze untuk autentikasi?
Soal hanya meminta register & login (email + password) tanpa OTP/verifikasi email, sehingga Breeze dipilih karena scaffolding-nya ringan — tidak overkill seperti Jetstream yang membawa fitur tim, 2FA, dll yang tidak dibutuhkan di sini. Menggunakan session-based auth bawaan Laravel karena aplikasi ini full-stack (Blade/Livewire), bukan API terpisah.

Satu akun dapat berperan sebagai **seller maupun bidder** tanpa sistem role terpisah — cukup pakai tabel `users` default tanpa kolom `role` tambahan. Perannya ditentukan dari konteks: pemilik listing (seller) vs pihak yang mengajukan bid (bidder), bukan dari atribut tetap pada user.

### Kenapa kolom JSON (`specs`) untuk field dinamis, bukan EAV atau kolom terpisah?

| Pendekatan | Kenapa tidak dipilih |
|---|---|
| Kolom terpisah per field | Banyak kolom `NULL` tergantung kategori, tidak scalable — setiap kategori baru butuh migration baru |
| EAV (tabel key-value) | Query jadi kompleks (butuh banyak JOIN untuk menampilkan satu listing), overkill untuk kasus 2 kategori dengan field terbatas |
| **JSON (dipilih)** | Satu kolom, fleksibel menampung struktur berbeda per kategori, cepat diimplementasikan. MySQL mendukung tipe `JSON` secara native, dan Eloquent memiliki cast bawaan (`'specs' => 'array'`) sehingga tetap ditangani sebagai array PHP biasa di level aplikasi |

Validasi struktur field per kategori dilakukan di **level aplikasi** (Livewire component), bukan di level database, karena MySQL JSON tidak melakukan enforce skema internal.

Sementara tabel `bids` tetap dirancang relasional biasa (bukan JSON) karena strukturnya seragam di setiap baris dan membutuhkan query agregat yang efisien (`MAX`, `ORDER BY`, `COUNT`) untuk riwayat bid dan penentuan pemenang.

### Kenapa cascade delete pada relasi user → listing → bid?
Mencegah data "yatim" (orphan) yang merujuk ke record yang sudah tidak ada. Secara bisnis, listing dan riwayat bid tidak memiliki arti berdiri sendiri tanpa pemilik/konteksnya — jika seller dihapus, listing dan bid terkait ikut dihapus. Untuk kebutuhan production sesungguhnya, pendekatan yang lebih aman adalah soft delete agar riwayat transaksi tetap bisa diaudit; namun untuk kebutuhan test ini, hard delete + cascade dipilih karena lebih sederhana dan tetap mendemonstrasikan pemahaman foreign key constraint.

### Kenapa Reverb?
Laravel Reverb memiliki dukungan queue database secara native, sehingga fitur real-time dapat berjalan tanpa dependency Redis — menyederhanakan setup untuk aplikasi skala kecil ini, karena hanya membutuhkan MySQL yang memang sudah digunakan.

---

## Sistem Lelang & Bidding

### Alur kerja
1. Setiap listing otomatis menjadi sesi lelang saat dibuat, dengan `auction_start` = waktu pembuatan dan `auction_end` = waktu pembuatan + durasi yang ditentukan seller.
2. Logika validasi & eksekusi bid dipisahkan ke `app/Actions/PlaceBidAction.php` (bukan ditulis langsung di Livewire component), supaya:
   - Bisa di-unit test tanpa perlu render component.
   - Livewire component tetap fokus ke urusan UI saja.
3. Setiap bid masuk dibungkus dalam `DB::transaction()` dengan `lockForUpdate()` pada row listing — mencegah **race condition** ketika dua bid diajukan hampir bersamaan (mencegah dua bid "menang" di waktu yang sama karena keduanya membaca harga lama sebelum salah satu ter-update).
4. Aturan kenaikan minimum bid: **+5% dari harga saat ini** (dapat diubah di `PlaceBidAction::MIN_INCREMENT_PERCENT`). Nilai persentase dipilih (bukan nominal tetap) agar tetap proporsional baik untuk listing harga rendah maupun tinggi.
5. Validasi yang dijalankan sebelum bid diterima:
   - Lelang masih `active` dan belum melewati `auction_end`.
   - Bidder bukan pemilik listing (`user_id` seller ≠ bidder — mencegah self-bid).
   - Nominal bid ≥ harga saat ini + 5%.
6. Setiap bid valid memicu event `NewBidPlaced` (broadcast lewat Reverb) ke channel publik `listing.{id}`, sehingga siapa pun yang sedang membuka halaman detail listing tersebut menerima update harga secara real-time tanpa refresh.

### Auto-close lelang (`app/Console/Commands/CloseExpiredAuctions.php`)
Command `auctions:close-expired` men-scan seluruh listing berstatus `active` yang `auction_end`-nya sudah lewat, lalu mengubah statusnya menjadi `ended`. Pemenang **tidak dihitung ulang** di sini — nilai `current_winner_id` sudah terkunci secara otomatis dari bid tertinggi terakhir yang tercatat oleh `PlaceBidAction`, sehingga command ini murni bertugas mengunci status saja.

Command ini didaftarkan untuk berjalan otomatis setiap menit lewat scheduler (`routes/console.php`), dan saat development scheduler dijalankan lewat:
```bash
php artisan schedule:work
```

### Testing manual mempercepat waktu lelang (untuk demo/skenario uji)
Karena durasi lelang bisa mencapai belasan menit, untuk keperluan demo/testing lebih cepat digunakan `php artisan tinker` (REPL interaktif bawaan Laravel) untuk memundurkan `auction_end` secara manual tanpa perlu menunggu waktu asli habis:
```bash
php artisan tinker
```
```php
$listing = \App\Models\Listing::first();
$listing->update(['auction_end' => now()->subMinute()]);
exit
```
Lalu jalankan command penutup lelang secara manual (tanpa menunggu giliran scheduler):
```bash
php artisan auctions:close-expired
```
Tinker dan pemanggilan manual command ini murni alat bantu development/testing — tidak digunakan oleh alur aplikasi yang sebenarnya (di production, command berjalan otomatis lewat scheduler).

---