<?php
include '../../includes/koneksi.php';

// Halaman KHUSUS pemilik saja — contoh: dashboard, keuangan, laporan, dll
$allowed_roles = ['pemilik'];
include '../../includes/auth_check.php';

// Set default tanggal (awal bulan sampai hari ini)
$dari_tanggal = isset($_GET['dari_tanggal']) ? $_GET['dari_tanggal'] : date('Y-m-01');
$sampai_tanggal = isset($_GET['sampai_tanggal']) ? $_GET['sampai_tanggal'] : date('Y-m-d');

// Ambil data transaksi pada rentang tanggal
$query = mysqli_query($conn, "SELECT * FROM transaksi_keuangan 
                               WHERE DATE(tanggal) BETWEEN '$dari_tanggal' AND '$sampai_tanggal' 
                               ORDER BY tanggal ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="p-4">

    <div class="no-print mb-4">
        <div class="card">
            <div class="card-body">
                
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="dari_tanggal" value="<?= $dari_tanggal ?>" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="sampai_tanggal" value="<?= $sampai_tanggal ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            <i class=""></i> Filter
                        </button>
                        <button type="button" onclick="window.print()" class="btn btn-success">
                            <i class=""></i> Cetak
                        </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <h3 class="text-center fw-bold mb-4">LAPORAN TRANSAKSI KEUANGAN</h3>
    <p><strong>Periode:</strong> <?= date('d F Y', strtotime($dari_tanggal)) ?> - <?= date('d F Y', strtotime($sampai_tanggal)) ?></p>

    <table class="table table-bordered border-dark" id="tabelTransaksi">
        <thead>
            <tr class="table-secondary">
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th class="text-end">Nominal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_pemasukan = 0;
            $total_pengeluaran = 0;
            
            if(mysqli_num_rows($query) > 0):
                while($row = mysqli_fetch_assoc($query)): 
                    if($row['tipe'] == 'pemasukan') {
                        $total_pemasukan += $row['nominal'];
                    } else {
                        $total_pengeluaran += $row['nominal'];
                    }
            ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                <td>
                    <span class="badge bg-<?= $row['tipe']=='pemasukan' ? 'success' : 'danger' ?>">
                        <?= ucfirst($row['tipe']) ?>
                    </span>
                </td>
                <td><?= $row['kategori'] ?></td>
                <td><?= $row['deskripsi'] ?></td>
                <td class="text-end">Rp <?= number_format($row['nominal'],0,',','.') ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="5" class="text-center">Tidak ada transaksi pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="table-light fw-bold">
                <td colspan="4" class="text-end">Total Pemasukan:</td>
                <td class="text-end text-success">Rp <?= number_format($total_pemasukan,0,',','.') ?></td>
            </tr>
            <tr class="table-light fw-bold">
                <td colspan="4" class="text-end">Total Pengeluaran:</td>
                <td class="text-end text-danger">Rp <?= number_format($total_pengeluaran,0,',','.') ?></td>
            </tr>
            <tr class="table-secondary fw-bold">
                <td colspan="4" class="text-end">Saldo:</td>
                <td class="text-end">Rp <?= number_format($total_pemasukan - $total_pengeluaran,0,',','.') ?></td>
            </tr>
        </tfoot>
    </table>

    <script>
        function exportExcel() {
            // Buat form untuk export
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export_excel.php';
            
            const input1 = document.createElement('input');
            input1.type = 'hidden';
            input1.name = 'dari_tanggal';
            input1.value = '<?= $dari_tanggal ?>';
            
            const input2 = document.createElement('input');
            input2.type = 'hidden';
            input2.name = 'sampai_tanggal';
            input2.value = '<?= $sampai_tanggal ?>';
            
            form.appendChild(input1);
            form.appendChild(input2);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>