<?php
include '../../includes/koneksi.php';
$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM transaksi_keuangan WHERE transaksi_id='$id'"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Struk <?= $id ?></title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 58mm; margin: 0 auto; padding: 10px; color: #000; }
        .center { text-align: center; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        .flex { display: flex; justify-content: space-between; }
        .bold { font-weight: bold; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="center">
        <h3 style="margin:0;">OKSIGEN PGM</h3>
        <p style="margin:0;">Jl. Balam GG.Tiung No. 16, Pekanbaru</p>
        <p style="margin:0;">WA: 0822-8665-0135</p>
    </div>
    
    <div class="line"></div>
    
    <div>
        No: <?= $id ?><br>
        Tgl: <?= date('d/m/Y H:i', strtotime($data['tanggal'])) ?><br>
        Kasir: Admin
    </div>

    <div class="line"></div>

    <div style="margin-bottom: 5px;">
        <div class="bold"><?= $data['kategori'] ?></div>
        <div><?= $data['deskripsi'] ?></div>
    </div>

    <div class="line"></div>

    <div class="flex bold" style="font-size: 14px;">
        <span>TOTAL</span>
        <span>Rp <?= number_format($data['nominal'], 0, ',', '.') ?></span>
    </div>
    
    <div class="flex">
        <span>Bayar:</span>
        <span><?= ucfirst($data['metode_bayar']) ?></span>
    </div>

    <div class="line"></div>
    <div class="center" style="margin-top:10px;">
        Terima Kasih<br>
    </div>

    <div class="center no-print" style="margin-top: 20px;">
        <button onclick="window.print()">Cetak Lagi</button>
    </div>

</body>
</html>