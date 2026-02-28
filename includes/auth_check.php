<?php
/**
 * =====================================================
 *  AUTH CHECK — Proteksi Halaman Berdasarkan Role
 * =====================================================
 * Cara pakai: include file ini di BARIS PALING ATAS
 * setiap halaman yang ingin diproteksi.
 *
 * Contoh penggunaan:
 *
 *   // Halaman boleh diakses SEMUA role (admin & pemilik):
 *   $allowed_roles = ['admin', 'pemilik'];
 *   include '../../includes/auth_check.php';
 *
 *   // Halaman HANYA untuk pemilik:
 *   $allowed_roles = ['pemilik'];
 *   include '../../includes/auth_check.php';
 *
 *   // Halaman HANYA untuk admin:
 *   $allowed_roles = ['admin'];
 *   include '../../includes/auth_check.php';
 * =====================================================
 */

// Jalankan session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Belum login → redirect ke halaman login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: /oksigen_aca/login.php");
    exit();
}

// 2. Cek apakah role user diizinkan di halaman ini
if (isset($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {

    // Admin mencoba akses halaman yang bukan haknya → kembalikan ke halaman admin
    if ($_SESSION['role'] === 'admin') {
        header("Location: /oksigen_aca/pages/stok_tabung/admin_data_tabung.php");
    } else {
        // Pemilik mencoba akses halaman lain yang tidak diizinkan → ke dashboard
        header("Location: /oksigen_aca/dashboard.php");
    }
    exit();
}