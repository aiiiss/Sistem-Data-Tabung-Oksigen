-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Nov 2025 pada 14.49
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_oksigen`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `laporan_id` int(11) NOT NULL,
  `periode` varchar(50) DEFAULT NULL,
  `total_pemasukan` decimal(15,2) DEFAULT NULL,
  `total_pengeluaran` decimal(15,2) DEFAULT NULL,
  `saldo_akhir` decimal(15,2) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi_stok`
--

CREATE TABLE `notifikasi_stok` (
  `notif_id` int(11) NOT NULL,
  `tabung_id` int(11) NOT NULL,
  `pesan` varchar(255) DEFAULT NULL,
  `status` enum('belum_dibaca','dibaca') DEFAULT 'belum_dibaca',
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok_movements`
--

CREATE TABLE `stok_movements` (
  `movement_id` int(11) NOT NULL,
  `tabung_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `tipe` enum('masuk','keluar') NOT NULL,
  `jumlah` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tabung`
--

CREATE TABLE `tabung` (
  `tabung_id` int(11) NOT NULL,
  `kode_tabung` varchar(50) DEFAULT NULL,
  `ukuran` varchar(50) NOT NULL,
  `kapasitas` varchar(50) DEFAULT NULL,
  `qty_total` int(11) DEFAULT 0,
  `qty_penuh` int(11) DEFAULT 0,
  `qty_kosong` int(11) DEFAULT 0,
  `threshold` int(11) DEFAULT 5,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto` varchar(255) DEFAULT 'default.png',
  `harga_refill` decimal(10,0) DEFAULT 0,
  `harga_tabung_kosong` decimal(10,0) DEFAULT 0,
  `harga_full_set` decimal(10,0) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tabung`
--

INSERT INTO `tabung` (`tabung_id`, `kode_tabung`, `ukuran`, `kapasitas`, `qty_total`, `qty_penuh`, `qty_kosong`, `threshold`, `status`, `created_at`, `foto`, `harga_refill`, `harga_tabung_kosong`, `harga_full_set`) VALUES
(1, 'TBG-001', '1 m³', '1 meter kubik', 24, 24, 0, 5, 'aktif', '2025-11-06 13:58:56', '181120251306241 m.jpeg', 35000, 550000, 585000),
(2, 'TBG-002', '2 m³', '2 meter kubik', 15, 13, 2, 5, 'aktif', '2025-11-06 13:58:56', 'default.png', 55000, 750000, 805000),
(3, 'TBG-003', '6 m³', '6 meter kubik', 40, 35, 5, 10, 'aktif', '2025-11-06 13:58:56', 'default.png', 100000, 1600000, 1700000),
(4, 'TBG-004', '10 m³', '10 meter kubik', 10, 8, 2, 3, 'aktif', '2025-11-06 13:58:56', 'default.png', 150000, 2200000, 2350000),
(5, 'TBG-005', '12 m³', '12 meter kubik', 20, 18, 2, 5, 'aktif', '2025-11-06 13:58:56', 'default.png', 180000, 2500000, 2680000),
(6, 'TBG-006', '1.5 m³', '1,5 meter kubik', 12, 10, 2, 3, 'aktif', '2025-11-06 13:58:56', 'default.png', 45000, 650000, 695000),
(7, 'TBG-007', '8 m³', '8 meter kubik', 8, 6, 2, 2, 'aktif', '2025-11-06 13:58:56', 'default.png', 130000, 1900000, 2030000),
(8, 'TBG-008', '4 m³', '4 meter kubik', 18, 14, 4, 5, 'aktif', '2025-11-06 13:58:56', 'default.png', 80000, 1200000, 1280000),
(9, 'TBG-009', '5 m³', '5 meter kubik', 25, 23, 2, 5, 'aktif', '2025-11-06 13:58:56', 'default.png', 90000, 1400000, 1490000),
(10, 'TBG-010', '15 m³', '15 meter kubik', 5, 4, 1, 2, 'aktif', '2025-11-06 13:58:56', 'default.png', 220000, 3000000, 3220000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_keuangan`
--

CREATE TABLE `transaksi_keuangan` (
  `transaksi_id` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `tipe` enum('pemasukan','pengeluaran') NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode_bayar` enum('tunai','transfer') DEFAULT 'tunai',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_keuangan`
--

INSERT INTO `transaksi_keuangan` (`transaksi_id`, `tanggal`, `tipe`, `kategori`, `deskripsi`, `nominal`, `metode_bayar`, `user_id`) VALUES
(1, '2025-11-06 21:10:52', 'pemasukan', 'Penjualan Tabung 6m³', 'Penjualan 10 tabung ke Rumah Sakit A', 1500000.00, 'tunai', NULL),
(2, '2025-11-06 21:10:52', 'pengeluaran', 'Isi Ulang Tabung', 'Pengisian ulang oksigen 20 tabung', 400000.00, 'transfer', NULL),
(3, '2025-11-06 21:10:52', 'pengeluaran', 'Transportasi', 'Biaya kirim ke pelanggan', 150000.00, 'tunai', NULL),
(4, '2025-11-18 14:05:00', 'pemasukan', 'Penjualan Aset', 'Jual Tabung Kosong 1 m³ (1 Pcs)', 550000.00, 'tunai', NULL),
(5, '2025-11-18 14:09:00', 'pengeluaran', 'belanja', 'jajan acaa', 100000.00, 'tunai', NULL),
(6, '2025-11-18 14:29:00', 'pengeluaran', 'Restok Gas (Refill)', 'Kulakan Isi Ulang 1 m³ (1 Pcs)', 20000.00, 'tunai', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','pemilik') DEFAULT 'pemilik',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Admin Aca', NULL, 'admin', '2025-11-06 12:50:08');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`laporan_id`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`);

--
-- Indeks untuk tabel `notifikasi_stok`
--
ALTER TABLE `notifikasi_stok`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `tabung_id` (`tabung_id`);

--
-- Indeks untuk tabel `stok_movements`
--
ALTER TABLE `stok_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `tabung_id` (`tabung_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `tabung`
--
ALTER TABLE `tabung`
  ADD PRIMARY KEY (`tabung_id`),
  ADD UNIQUE KEY `kode_tabung` (`kode_tabung`);

--
-- Indeks untuk tabel `transaksi_keuangan`
--
ALTER TABLE `transaksi_keuangan`
  ADD PRIMARY KEY (`transaksi_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `laporan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `notifikasi_stok`
--
ALTER TABLE `notifikasi_stok`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stok_movements`
--
ALTER TABLE `stok_movements`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tabung`
--
ALTER TABLE `tabung`
  MODIFY `tabung_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `transaksi_keuangan`
--
ALTER TABLE `transaksi_keuangan`
  MODIFY `transaksi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `notifikasi_stok`
--
ALTER TABLE `notifikasi_stok`
  ADD CONSTRAINT `notifikasi_stok_ibfk_1` FOREIGN KEY (`tabung_id`) REFERENCES `tabung` (`tabung_id`);

--
-- Ketidakleluasaan untuk tabel `stok_movements`
--
ALTER TABLE `stok_movements`
  ADD CONSTRAINT `stok_movements_ibfk_1` FOREIGN KEY (`tabung_id`) REFERENCES `tabung` (`tabung_id`),
  ADD CONSTRAINT `stok_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `transaksi_keuangan`
--
ALTER TABLE `transaksi_keuangan`
  ADD CONSTRAINT `transaksi_keuangan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
