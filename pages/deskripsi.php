<?php
include '../includes/koneksi.php';
// Header mungkin perlu path adjustment jika pakai include relative, 
// tapi karena di header pakai path absolut (/oksigen_aca/...), aman.
include '../includes/header.php'; 
?>

<div class="container mt-5">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold text-primary mb-3">Tentang Sistem Oksigen PGM</h2>
                    <p>
                        Aplikasi Manajemen Stok & Keuangan Tabung Oksigen
                    </p>
                    <p>
                        Sistem ini dirancang untuk mempermudah pengelolaan bisnis tabung oksigen secara digital. 
                        Fitur utama meliputi pencatatan stok real-time, pemantauan tabung masuk/keluar, 
                        serta rekapitulasi keuangan harian dan bulanan.
                    </p>
                    
                    <hr class="my-4">
                    
                    <h5 class="fw-bold"><i class="bi bi-check-circle-fill text-success"></i> Fitur Unggulan:</h5>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><i class="bi bi-box-seam text-primary me-2"></i> Monitoring Stok Tabung (Penuh/Kosong)</li>
                        <li class="mb-2"><i class="bi bi-cash-coin text-primary me-2"></i> Pencatatan Transaksi Keuangan</li>
                        <li class="mb-2"><i class="bi bi-printer text-primary me-2"></i> Cetak Laporan Harian & Tahunan</li>
                        <li class="mb-2"><i class="bi bi-graph-up text-primary me-2"></i> Grafik Keuangan Interaktif</li>
                    </ul>
                </div>
                <div class="col-md-4 text-center">
                    <i class="bi bi-lungs text-primary" style="font-size: 8rem; opacity: 0.8;"></i>
                    <p> Versi 1.0</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>