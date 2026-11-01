# Sistem Informasi Manajemen Data Tabung Oksigen

Repositori ini berisi kode sumber aplikasi berbasis web *Full-Stack* yang dikembangkan untuk mengelola manajemen data tabung oksigen secara digital, terintegrasi dengan database relasional, dan dilengkapi sistem kendali akses yang aman.

## 🚀 Fitur Utama
- **Manajemen Inventaris Tabung:** Pencatatan dan pengelolaan data stok tabung oksigen secara terstruktur.
- **Sistem Autentikasi Aman:** Fitur *login*, *logout*, dan manajemen sesi pengguna yang dilindungi dengan enkripsi *password hashing*.
- **Antarmuka Modern (AdminLTE):** Menggunakan templat dasbor interaktif berbasis Bootstrap/AdminLTE untuk kemudahan navigasi pengguna.
- **Manajemen Basis Data:** Terintegrasi penuh dengan database MySQL untuk penyimpanan data transaksi dan inventaris yang andal.

## 📁 Struktur Direktori Utama
- `dashboard.php`: Halaman utama dasbor setelah pengguna berhasil masuk.
- `login.php` & `logout.php`: Modul autentikasi dan kontrol sesi.
- `includes/`: Berisi file konfigurasi koneksi database (`koneksi.php`), skrip *schema* SQL (`db_oksigen.sql`), serta templat komponen *header/footer*.
- `pages/`: Berisi modul halaman fungsional aplikasi (manajemen stok tabung, transaksi, dan laporan).
- `assets/`: Kumpulan aset antarmuka seperti CSS kustom dan gambar dokumentasi.

## 🛠️ Teknologi yang Digunakan
- **Backend:** PHP (Native)
- **Database:** MySQL
- **Frontend / UI:** HTML5, CSS3, JavaScript, Bootstrap, AdminLTE
