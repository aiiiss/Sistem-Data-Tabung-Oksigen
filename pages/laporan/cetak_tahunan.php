<?php
include '../../includes/koneksi.php';

// Halaman KHUSUS pemilik saja — contoh: dashboard, keuangan, laporan, dll
$allowed_roles = ['pemilik'];
include '../../includes/auth_check.php';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$query = mysqli_query($conn, "
    SELECT MONTH(tanggal) as bulan, 
    SUM(CASE WHEN tipe='pemasukan' THEN nominal ELSE 0 END) as pemasukan,
    SUM(CASE WHEN tipe='pengeluaran' THEN nominal ELSE 0 END) as pengeluaran
    FROM transaksi_keuangan 
    WHERE YEAR(tanggal) = '$tahun'
    GROUP BY MONTH(tanggal)
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Tahunan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> @media print { .no-print { display: none; } } </style>
</head>
<body class="p-4">
    <div class="no-print mb-3">
        <form method="GET" class="d-flex gap-2 w-25">
            <input type="number" name="tahun" value="<?= $tahun ?>" class="form-control">
            <button type="submit" class="btn btn-primary">Lihat</button>
            <button type="button" onclick="window.print()" class="btn btn-success">Cetak</button>
        </form>
    </div>

    <h3 class="text-center fw-bold mb-4">LAPORAN REKAPITULASI TAHUNAN <?= $tahun ?></h3>
    
    <table class="table table-bordered border-dark text-center">
        <thead>
            <tr class="table-secondary">
                <th>Bulan</th>
                <th>Total Pemasukan</th>
                <th>Total Pengeluaran</th>
                <th>Laba / Rugi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $months = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            while($row = mysqli_fetch_assoc($query)): 
                $selisih = $row['pemasukan'] - $row['pengeluaran'];
            ?>
            <tr>
                <td class="text-start"><?= $months[$row['bulan']] ?></td>
                <td class="text-success">Rp <?= number_format($row['pemasukan'],0,',','.') ?></td>
                <td class="text-danger">Rp <?= number_format($row['pengeluaran'],0,',','.') ?></td>
                <td class="fw-bold">Rp <?= number_format($selisih,0,',','.') ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>