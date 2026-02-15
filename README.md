# Fash Cashier

A simple and efficient web-based cashier (Point of Sale) application built with PHP and MySQL. Designed for fashion stores to manage products, transactions, and users.

---

## 🚀 Features

- **Authentication System**: Secure login and logout for authorized personnel.
- **User Management**: Role-based access control.
- **Product Management**: Manages fashion items with categories and images.
- **Transaction Processing**: Point of Sale interface for sales.
- **Reporting**: Sales reports and transaction history.
- **Security**: Password hashing and environment variables via `.env`.

---

## 🛠️ Project Contents (Technical Documentation)

### 📂 Directory Structure

- **`root/`**
  - `index.php`: Main entry point, handles session and role-based redirection.
  - `login.php` & `register.php`: Authentication pages.
  - `fashion_db.sql`: The primary database schema and initial data.
  - `.env`: Configuration for database connection and environment variables.
- **`admin/`**: Contains the administrative dashboard and reporting tools.
  - `index.php`: Admin overview and statistics.
  - `laporan_penjualan.php`: Tool for viewing sales logs.
  - `export_laporan.php`: Exports reports to external formats.
- **`produk/`**: Core module for item management.
  - `index.php`: List of all products.
  - `add.php`, `edit.php`, `delete.php`: CRUD operations for products.
- **`transaksi/`**: The Point of Sale module.
  - `add.php`: Dashboard for creating a new sale.
  - `invoice.php`: Generates printable receipts (struk).
  - `detail.php`: Detailed view of a specific transaction.
- **`views/`**: UI components and themes (Default & Soft UI).
- **`lib/`**: Core libraries and shared helper functions.
- **`uploads/`**: Stores uploaded product images and user avatars.

### 🗄️ Database Schema (`fashion_db`)

1. **`users`**: Stores user credentials and profile info.
   - `id`, `username`, `password`, `foto`, `role`
2. **`produk`**: Stores item data.
   - `id`, `kode_barang`, `nama_produk`, `harga`, `foto`
3. **`transaksi`**: Stores header data for Every sale.
   - `id`, `nomor_bukti`, `tanggal`, `total_bayar`, `status_bayar`
4. **`detail_transaksi`**: Stores items per transaction.
   - `id`, `transaksi_id`, `produk_id`, `qty`, `subtotal`

---

## 📖 User Guide (Panduan Aplikasi)

### 1. Inisialisasi & Login
- Buka browser dan akses URL `http://localhost/fash-cashier`.
- Gunakan akun default: **Username:** `admin` | **Password:** `admin` (atau sesuai konfigurasi awal).

### 2. Mengelola Produk
- Buka menu **Produk** pada sidebar.
- **Tambah Produk**: Klik tombol **Tambah**, isi nama, harga, dan unggah foto produk.
- **Edit/Hapus**: Gunakan ikon pensil untuk mengubah data atau ikon tempat sampah untuk menghapus.

### 3. Melakukan Transaksi (Kasir)
- Masuk ke menu **Transaksi** > **Tambah Transaksi**.
- Pilih produk yang dibeli dan tentukan jumlahnya (QTY).
- Masukkan jumlah uang bayar. Sistem akan menghitung kembalian secara otomatis.
- Klik **Simpan/Bayar** untuk mencetak struk belanja.

### 4. Laporan Penjualan
- Admin dapat melihat semua riwayat penjualan di menu **Laporan**.
- Gunakan filter tanggal untuk mencari transaksi spesifik.
- Klik **Cetak Struk** pada riwayat jika pelanggan memerlukan salinan nota kembali.

### 5. Pengaturan Profil
- Klik pada nama user di pojok kanan atas untuk mengubah **Profile** atau **Password**.

---

## 🔧 Installation

1. **Clone/Download** repo ke folder `htdocs`.
2. **Database**: Import `fashion_db.sql` ke phpMyAdmin.
3. **Composer**: Jalankan `composer install` di terminal folder proyek.
4. **Konfigurasi**: Sesuaikan file `.env` dengan database lokal Anda.
5. **Akses**: `http://localhost/fash-cashier`.

---

## 📄 License
Internal / Educational Use Only.
