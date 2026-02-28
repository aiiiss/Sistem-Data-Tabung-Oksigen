<?php
session_start();
include 'includes/koneksi.php';

// Halaman KHUSUS pemilik saja — contoh: dashboard, keuangan, laporan, dll
$allowed_roles = ['pemilik'];
include 'includes/auth_check.php'; // ✅ BENAR

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Arahkan ke login.php jika belum login
    exit();
}
$username = $_SESSION['username'];

// --- LOGIKA GRAFIK DINAMIS ---
$pemasukan_per_bulan = array_fill(1, 12, 0);
$pengeluaran_per_bulan = array_fill(1, 12, 0);
$thn_ini = date('Y');

// Query Pemasukan
$q_masuk = mysqli_query($conn, "SELECT MONTH(tanggal) as bulan, SUM(nominal) as total FROM transaksi_keuangan WHERE tipe='pemasukan' AND YEAR(tanggal)='$thn_ini' GROUP BY MONTH(tanggal)");
while ($r = mysqli_fetch_assoc($q_masuk)) { $pemasukan_per_bulan[$r['bulan']] = $r['total']; }

// Query Pengeluaran
$q_keluar = mysqli_query($conn, "SELECT MONTH(tanggal) as bulan, SUM(nominal) as total FROM transaksi_keuangan WHERE tipe='pengeluaran' AND YEAR(tanggal)='$thn_ini' GROUP BY MONTH(tanggal)");
while ($r = mysqli_fetch_assoc($q_keluar)) { $pengeluaran_per_bulan[$r['bulan']] = $r['total']; }

$json_pemasukan = json_encode(array_values($pemasukan_per_bulan));
$json_pengeluaran = json_encode(array_values($pengeluaran_per_bulan));

// --- DATA RINGKASAN ---
$total_pemasukan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(nominal),0) AS total FROM transaksi_keuangan WHERE tipe='pemasukan'"))['total'];
$total_pengeluaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(nominal),0) AS total FROM transaksi_keuangan WHERE tipe='pengeluaran'"))['total'];
$total_tabung = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(qty_total),0) AS total FROM tabung"))['total'];
$saldo = $total_pemasukan - $total_pengeluaran;

// --- MEMANGGIL HEADER (NAVBAR & CSS) ---
include 'includes/header.php'; 
?>

<style>
    /* Banner Selamat Datang */
    .welcome-banner {
        background: linear-gradient(45deg, #0ea5e9, #3b82f6);
        color: white;
        border-radius: 20px;
        padding: 40px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(14, 165, 233, 0.3);
    }
    .welcome-banner h2 { font-weight: 700; position: relative; z-index: 2; }
    .welcome-banner p { position: relative; z-index: 2; opacity: 0.9; }
    .welcome-decoration {
        position: absolute;
        right: -20px;
        bottom: -50px;
        font-size: 10rem;
        opacity: 0.2;
        transform: rotate(-10deg);
        z-index: 1;
    }

    /* Kartu Statistik */
    .card-stat {
        position: relative;
        overflow: hidden;
        border: none;
        border-radius: 16px;
        color: white;
        transition: transform 0.3s ease;
    }
    .card-stat:hover { transform: translateY(-5px); }
    .card-stat .card-body { position: relative; z-index: 2; }
    .card-stat .icon-bg {
        position: absolute;
        right: -15px;
        bottom: -15px;
        font-size: 6rem;
        opacity: 0.25;
        transform: rotate(-20deg);
        z-index: 1;
    }
    
    /* Warna Gradasi Kartu */
    .bg-gradient-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .bg-gradient-danger  { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .bg-gradient-info    { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
</style>

<div class="container">
    
    <div class="welcome-banner mb-5">
        <h2>Halo, <?= ucfirst($username) ?>!</h2>
        <p class="mb-0 fs-5">Selamat datang kembali di Sistem Manajemen Oksigen PGM</p>
        <i class=""></i>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-grid-1x2-fill text-primary"></i> Ringkasan Bisnis</h4>
        <div class="btn-group shadow-sm">
            <a href="pages/laporan/cetak_harian.php" target="_blank" class="btn btn-light border text-primary fw-bold"><i class="bi bi-printer"></i> Harian</a>
            <a href="pages/laporan/cetak_bulanan.php" target="_blank" class="btn btn-light border text-success fw-bold"><i class="bi bi-calendar-month"></i> Bulanan</a>
            <a href="pages/laporan/cetak_tahunan.php" target="_blank" class="btn btn-light border text-danger fw-bold"><i class="bi bi-file-earmark-pdf"></i> Tahunan</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card card-stat bg-gradient-primary h-100 shadow-lg">
                <div class="card-body p-4">
                    <h6 class="text-uppercase opacity-75 ls-1">Saldo Bersih</h6>
                    <h3 class="fw-bold mt-2">Rp <?= number_format($saldo, 0, ',', '.') ?></h3>
                    <i class=""></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stat bg-gradient-success h-100 shadow">
                <div class="card-body p-4">
                    <h6 class="text-uppercase opacity-75 ls-1">Pemasukan</h6>
                    <h3 class="fw-bold mt-2">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h3>
                    <i class=""></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stat bg-gradient-danger h-100 shadow">
                <div class="card-body p-4">
                    <h6 class="text-uppercase opacity-75 ls-1">Pengeluaran</h6>
                    <h3 class="fw-bold mt-2">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h3>
                    <i class=""></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stat bg-gradient-info h-100 shadow">
                <div class="card-body p-4">
                    <h6 class="text-uppercase opacity-75 ls-1">Total Aset Tabung</h6>
                    <h3 class="fw-bold mt-2"><?= $total_tabung ?> <span class="fs-6 opacity-75">Unit</span></h3>
                    <i class=""></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title fw-bold text-primary"><i class="bi bi-bar-chart-line-fill"></i> Analisis Keuangan <?= date('Y') ?></h5>
                        <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">Update Realtime</span>
                    </div>
                    <div style="height: 350px;">
                        <canvas id="chartKeuangan"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="mt-5 text-center text-muted small pb-4">
    &copy; <?= date('Y') ?> Sistem Oksigen ACA v2.0
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Setup Font Chart agar sesuai dengan Website (Poppins)
    Chart.defaults.font.family = "'Poppins', sans-serif";
    
    const ctx = document.getElementById('chartKeuangan');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [
                {
                    label: 'Pemasukan',
                    data: <?= $json_pemasukan ?>,
                    backgroundColor: '#10b981', // Warna Hijau Modern
                    borderRadius: 6,
                    barPercentage: 0.6
                },
                {
                    label: 'Pengeluaran',
                    data: <?= $json_pengeluaran ?>,
                    backgroundColor: '#ef4444', // Warna Merah Modern
                    borderRadius: 6,
                    barPercentage: 0.6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 10 } },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#333',
                    bodyColor: '#666',
                    borderColor: '#ddd',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: true
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#f3f4f6', borderDash: [5, 5] },
                    ticks: { color: '#9ca3af' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#9ca3af' }
                }
            }
        }
    });
</script>

</body>
</html>