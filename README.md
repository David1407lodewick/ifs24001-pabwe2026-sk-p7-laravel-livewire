# ifs24010-pabwe2026-sk-p7-laravel-livewire

> Varian tampilan **StokKu Gudang**: navbar atas, tema teal/amber, halaman auth dua kolom, daftar stok bergaya kartu. Logika, struktur Feature-First, dan tes sama dengan studi kasus.

# Studi Kasus: Aplikasi Stock Barang - PABWE 2026 Praktikum 7 (Laravel 13 & Livewire 4)

Aplikasi Manajemen Inventaris & Stok Barang modern dengan arsitektur **Clean Architecture & Feature-First Approach** sesuai standar industri, dibangun menggunakan **Laravel 13**, **Livewire 4**, basis data **MySQL**, upload/export spreadsheet via **PhpOffice/PhpSpreadsheet**, integrasi kecerdasan buatan (**OpenAI AI Stock Content Generation**), rate limiting (1000 per detik), proteksi kepemilikan middleware, force HTTPS, dan **100% Test Coverage**.

---

## 🚀 Fitur Utama

1. **Clean Architecture & Feature-First Approach**:
   - Modul diorganisir per feature/domain (`App\Features\Auth`, `App\Features\Stock`, `App\Features\Profile`, `App\Features\Admin`, `App\Features\Ai`).
   - Menerapkan Dependency Inversion Principle (DIP) dengan repository interface dan client contracts.
   - Pemisahan lapisan yang jelas antara Actions, Repositories, Services, DTOs, Policies, dan UI (Livewire).

2. **Autentikasi & Autorisasi Role-Based**:
   - **Login Fleksibel**: Pengguna dapat masuk menggunakan **Username** atau **Email** (dilengkapi atribut `id="login-username"`, `id="login-password"`, dan `id="login-submit"`).
   - **Role `admin`**:
     - Mengakses halaman Manajemen Pengguna (`/admin/users`).
     - Melihat daftar seluruh pengguna beserta username, email, dan jumlah stok barang mereka (`stocks_count`).
     - Melihat detail stok barang pengguna lain dalam mode **Read-Only** (`/admin/users/{user}/stocks`) serta ekspor ke Excel.
     - Admin **tidak dapat** memodifikasi/menghapus stok barang pengguna lain.
   - **Role `member`**:
     - User yang mendaftar melalui form registrasi otomatis memiliki role `member`.
     - Hanya dapat mengelola (CRUD), menyesuaikan jumlah stok (`+` / `−`), mengekspor/mengimpor, serta generate AI untuk stok barang miliknya sendiri (`/stocks`).
   - **Middleware & Policy**:
     - `EnsureRole`: Membatasi rute khusus admin.
     - `EnsureStockOwnership`: Memastikan member tidak dapat mengakses atau memodifikasi stok barang milik orang lain.
     - `StockPolicy`: Memvalidasi otorisasi di tingkat model.

3. **Manajemen Stok & Sinkronisasi Status Otomatis**:
   - Setiap item memiliki nama (`name`), kode `sku`, `description` (Markdown via Toast UI Editor), `quantity`, `unit`, `price`, `min_stock`, dan `status`.
   - Status stok otomatis tersinkronisasi berdasarkan `quantity` dan `min_stock` melalui `StockStatus::fromQuantity()`:
     - `out_of_stock` (Habis) jika `quantity <= 0`
     - `low_stock` (Stok Menipis) jika `0 < quantity <= min_stock`
     - `available` (Tersedia) jika `quantity > min_stock`
   - Tombol cepat tambah/kurang kuantitas (`+` / `−`) tersedia pada halaman daftar maupun detail barang.

4. **Spreadsheet Excel Import & Export**:
   - Menggunakan library aman dari kerentanan: **`phpoffice/phpspreadsheet`**.
   - **Export**: Menghasilkan file `.xlsx` dengan styling profesional (header berwarna, border, text wrap, auto size).
   - **Import**: Mengunggah file `.xlsx`, `.xls`, `.csv` untuk melakukan bulk insert stok barang secara aman dengan validasi kuantitas, harga, satuan, dan status.

5. **AI Stock Content Generator (OpenAI)**:
   - Dikonfigurasi secara dinamis melalui `.env`:
     - `OPENAI_ENDPOINT`
     - `OPENAI_KEY`
     - `OPENAI_MODEL`
   - Kemampuan generate multiple item stok barang secara otomatis berdasarkan prompt/permintaan pengguna.
   - Output diformat dan divalidasi ke dalam DTO `GeneratedStockItem`.

6. **Rate Limiting & Keamanan**:
   - Skrip pembatas akses: **1000 request per detik** via `RateLimiter::for('1000-per-second', ...)`.
   - **Force HTTPS**: Dikonfigurasi melalui `FORCE_HTTPS=true` di `.env` atau otomatis aktif pada environment `production`.
   - Bebas dari kerentanan keamanan (`composer audit` clean 0 advisories).

7. **100% Test Coverage**:
   - Seluruh classes, methods, dan baris kode pada direktori `app/` memiliki **100% coverage**.

---

## 📁 Struktur Direktori Feature-First

```
app/
├── Features/
│   ├── Admin/
│   │   ├── Livewire/
│   │   │   ├── AdminUserStockList.php
│   │   │   └── UserList.php
│   │   └── Services/
│   │       └── AdminUserService.php
│   ├── Ai/
│   │   ├── Contracts/
│   │   │   └── AiClientInterface.php
│   │   ├── DTO/
│   │   │   └── GeneratedStockItem.php
│   │   └── Services/
│   │       └── OpenAiStockGeneratorService.php
│   ├── Auth/
│   │   ├── Actions/
│   │   │   ├── LoginUserAction.php
│   │   │   └── RegisterUserAction.php
│   │   └── Livewire/
│   │       ├── Login.php
│   │       └── Register.php
│   ├── Profile/
│   │   ├── Livewire/
│   │   │   └── Profile.php
│   │   └── Services/
│   │       └── ProfileService.php
│   └── Stock/
│       ├── Contracts/
│       │   └── StockRepositoryInterface.php
│       ├── Enums/
│       │   └── StockStatus.php
│       ├── Livewire/
│       │   ├── StockDetail.php
│       │   └── StockList.php
│       ├── Models/
│       │   └── Stock.php
│       ├── Policies/
│       │   └── StockPolicy.php
│       ├── Repositories/
│       │   └── StockRepository.php
│       └── Services/
│           ├── StockExcelService.php
│           └── StockService.php
├── Http/
│   └── Middleware/
│       ├── EnsureRole.php
│       └── EnsureStockOwnership.php
├── Models/
│   └── User.php
└── Providers/
    └── AppServiceProvider.php
```
