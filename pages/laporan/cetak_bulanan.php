<?php
include '../../includes/koneksi.php';

// Halaman KHUSUS pemilik saja — contoh: dashboard, keuangan, laporan, dll
$allowed_roles = ['pemilik'];
include '../../includes/auth_check.php';

// Ambil Input Bulan & Tahun (Default ke bulan saat ini jika kosong)
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Array Nama Bulan Indonesia
$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Query Data Transaksi per Bulan
$query = mysqli_query($conn, "SELECT * FROM transaksi_keuangan 
                              WHERE MONTH(tanggal) = '$bulan_pilih' 
                              AND YEAR(tanggal) = '$tahun_pilih' 
                              ORDER BY tanggal ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Bulan <?= $nama_bulan[(int)$bulan_pilih] ?> <?= $tahun_pilih ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', sans-serif; padding: 20px; }
        .header-laporan { text-align: center; margin-bottom: 30px; border-bottom: 2px solid black; padding-bottom: 20px; }
        .ttd { float: right; text-align: center; margin-top: 50px; margin-right: 50px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="card no-print mb-4 bg-light border-0 shadow-sm">
        <div class="card-body d-flex align-items-center gap-3 justify-content-center">
            <form method="GET" class="d-flex gap-2">
                <select name="bulan" class="form-select" required>
                    <?php foreach($nama_bulan as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $k==$bulan_pilih ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="tahun" class="form-control" value="<?= $tahun_pilih ?>" placeholder="Tahun" style="width: 100px;">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </form>
            <button onclick="window.print()" class="btn btn-success"><i class="bi bi-printer"></i> Cetak</button>
        </div>
    </div>

    <div class="header-laporan">
        <h2 style="margin:0;">OKSIGEN PGM</h2>
        <p style="margin:0;">Jl. Balam GG.Tiung No. 6, Pekanbaru</p>
        <hr style="margin: 10px 0;">
        <h4 style="margin-top:20px; text-decoration: underline;">LAPORAN KEUANGAN BULANAN</h4>
        <p>Periode: <strong><?= $nama_bulan[(int)$bulan_pilih] ?> <?= $tahun_pilih ?></strong></p>
    </div>

    <table class="table table-bordered border-dark align-middle">
        <thead class="table-secondary text-center">
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th>Kategori & Deskripsi</th>
                <th width="15%">Pemasukan (Debet)</th>
                <th width="15%">Pengeluaran (Kredit)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $total_masuk = 0;
            $total_keluar = 0;

            if(mysqli_num_rows($query) > 0):
                while($row = mysqli_fetch_assoc($query)):
                    // Pisahkan Masuk & Keluar
                    $masuk = ($row['tipe'] == 'pemasukan') ? $row['nominal'] : 0;
                    $keluar = ($row['tipe'] == 'pengeluaran') ? $row['nominal'] : 0;
                    
                    $total_masuk += $masuk;
                    $total_keluar += $keluar;
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                <td>
                    <strong><?= $row['kategori'] ?></strong><br>
                    <small class="text-muted"><?= $row['deskripsi'] ?></small>
                    <small class="text-muted d-block fst-italic">(<?= ucfirst($row['metode_bayar']) ?>)</small>
                </td>
                <td class="text-end"><?= $masuk > 0 ? 'Rp '.number_format($masuk,0,',','.') : '-' ?></td>
                <td class="text-end"><?= $keluar > 0 ? 'Rp '.number_format($keluar,0,',','.') : '-' ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="5" class="text-center py-4 fst-italic">Tidak ada transaksi pada bulan ini.</td>
            </tr>
            <?php endif; ?>
        </tbody>
        <tfoot class="table-light fw-bold">
            <tr>
                <td colspan="3" class="text-end">TOTAL</td>
                <td class="text-end text-success">Rp <?= number_format($total_masuk,0,',','.') ?></td>
                <td class="text-end text-danger">Rp <?= number_format($total_keluar,0,',','.') ?></td>
            </tr>
            <tr class="bg-warning bg-opacity-25">
                <td colspan="3" class="text-end">SALDO BERSIH (Laba/Rugi)</td>
                <td colspan="2" class="text-center fs-5">
                    Rp <?= number_format($total_masuk - $total_keluar, 0, ',', '.') ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="ttd">
        <p>Pekanbaru, <?= date('d F Y') ?></p>
        <p style="margin-bottom: 70px;">Admin / Pemilik,</p>
        <p class="fw-bold text-decoration-underline">Aries Gunawan</p>
    </div>

</body>
</html>