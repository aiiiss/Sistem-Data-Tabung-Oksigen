<?php
// ✅ Auth Check — hanya admin yang boleh akses
$allowed_roles = ['admin'];
include '../../includes/auth_check.php';

include '../../includes/koneksi.php';
include '../../includes/admin_header.php';

// ================================================================
// LOGIKA TAMBAH TABUNG
// ================================================================
if (isset($_POST['tambah'])) {
    $kode      = mysqli_real_escape_string($conn, $_POST['kode_tabung']);
    $ukuran    = mysqli_real_escape_string($conn, $_POST['ukuran']);
    $qty_total = (int)$_POST['qty_total'];
    $h_refill  = (int)$_POST['harga_refill'];
    $h_kosong  = (int)$_POST['harga_tabung_kosong'];
    $h_full    = (int)$_POST['harga_full_set'];
    $foto      = $_FILES['foto']['name'];
    $fotobaru  = "";
    if (!empty($foto)) {
        $tmp      = $_FILES['foto']['tmp_name'];
        $fotobaru = date('dmYHis') . $foto;
        move_uploaded_file($tmp, "../../assets/img/" . $fotobaru);
    }
    $query = "INSERT INTO tabung (kode_tabung, ukuran, qty_total, qty_penuh, qty_kosong, harga_refill, harga_tabung_kosong, harga_full_set, foto) 
              VALUES ('$kode', '$ukuran', '$qty_total', '$qty_total', 0, '$h_refill', '$h_kosong', '$h_full', '$fotobaru')";
    mysqli_query($conn, $query);
    // ✅ Redirect ke admin_data_tabung.php
    echo "<script>window.location='admin_data_tabung.php';</script>";
    exit();
}

// ================================================================
// LOGIKA EDIT TABUNG
// ================================================================
if (isset($_POST['edit'])) {
    $id        = (int)$_POST['tabung_id'];
    $kode      = mysqli_real_escape_string($conn, $_POST['kode_tabung']);
    $ukuran    = mysqli_real_escape_string($conn, $_POST['ukuran']);
    $kapasitas = mysqli_real_escape_string($conn, $_POST['kapasitas'] ?? '');
    $threshold = (int)$_POST['threshold'];
    $status    = mysqli_real_escape_string($conn, $_POST['status']);
    $h_refill  = (int)$_POST['harga_refill'];
    $h_kosong  = (int)$_POST['harga_tabung_kosong'];
    $h_full    = (int)$_POST['harga_full_set'];
    $update_foto = "";
    if (!empty($_FILES['foto']['name'])) {
        $foto        = $_FILES['foto']['name'];
        $tmp         = $_FILES['foto']['tmp_name'];
        $fotobaru    = date('dmYHis') . $foto;
        move_uploaded_file($tmp, "../../assets/img/" . $fotobaru);
        $update_foto = ", foto='$fotobaru'";
    }
    $query = "UPDATE tabung SET 
                kode_tabung='$kode', 
                ukuran='$ukuran', 
                kapasitas='$kapasitas', 
                threshold='$threshold', 
                status='$status', 
                harga_refill='$h_refill', 
                harga_tabung_kosong='$h_kosong', 
                harga_full_set='$h_full' 
                $update_foto 
              WHERE tabung_id='$id'";
    mysqli_query($conn, $query);
    // ✅ Redirect ke admin_data_tabung.php
    echo "<script>window.location='admin_data_tabung.php';</script>";
    exit();
}

// ================================================================
// LOGIKA HAPUS TABUNG
// ================================================================
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM notifikasi_stok WHERE tabung_id='$id_hapus'");
    mysqli_query($conn, "DELETE FROM tabung WHERE tabung_id='$id_hapus'");
    // ✅ Redirect ke admin_data_tabung.php
    echo "<script>window.location='admin_data_tabung.php';</script>";
    exit();
}

// ================================================================
// LOGIKA KOREKSI STOK MANUAL
// ================================================================
if (isset($_POST['koreksi_stok'])) {
    $id   = (int)$_POST['tabung_id'];
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis_koreksi']);
    $jml  = (int)$_POST['jumlah'];
    if ($jenis == 'tambah_penuh')
        mysqli_query($conn, "UPDATE tabung SET qty_penuh = qty_penuh + $jml, qty_total = qty_total + $jml WHERE tabung_id='$id'");
    elseif ($jenis == 'kurangi_penuh')
        mysqli_query($conn, "UPDATE tabung SET qty_penuh = GREATEST(qty_penuh - $jml, 0), qty_total = GREATEST(qty_total - $jml, 0) WHERE tabung_id='$id'");
    elseif ($jenis == 'tambah_kosong')
        mysqli_query($conn, "UPDATE tabung SET qty_kosong = qty_kosong + $jml, qty_total = qty_total + $jml WHERE tabung_id='$id'");
    elseif ($jenis == 'kurangi_kosong')
        mysqli_query($conn, "UPDATE tabung SET qty_kosong = GREATEST(qty_kosong - $jml, 0), qty_total = GREATEST(qty_total - $jml, 0) WHERE tabung_id='$id'");
    // ✅ Redirect ke admin_data_tabung.php
    echo "<script>window.location='admin_data_tabung.php';</script>";
    exit();
}

// ================================================================
// LOGIKA TAMBAH TRANSAKSI
// ================================================================
if (isset($_POST['tambah_transaksi'])) {
    $tipe    = mysqli_real_escape_string($conn, $_POST['tipe']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $metode  = mysqli_real_escape_string($conn, $_POST['metode_bayar']);
    $nominal = 0; $deskripsi = ""; $kategori = "";

    if ($tipe == 'pemasukan') {
        $id_tabung  = (int)$_POST['id_tabung_jual'];
        $jenis_jual = mysqli_real_escape_string($conn, $_POST['jenis_jual']);
        $qty        = (int)$_POST['qty_jual'];
        $tabung     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tabung WHERE tabung_id='$id_tabung'"));

        if ($jenis_jual == 'refill') {
            $nominal     = $tabung['harga_refill'] * $qty;
            $deskripsi   = "Penjualan Isi Ulang " . $tabung['ukuran'] . " ($qty Pcs)";
            $kategori    = "Penjualan Gas";
            mysqli_query($conn, "UPDATE tabung SET qty_penuh = qty_penuh - $qty, qty_kosong = qty_kosong + $qty WHERE tabung_id='$id_tabung'");
        } elseif ($jenis_jual == 'tabung_kosong') {
            $nominal     = $tabung['harga_tabung_kosong'] * $qty;
            $deskripsi   = "Penjualan Tabung Kosong " . $tabung['ukuran'] . " ($qty Pcs)";
            $kategori    = "Penjualan Aset";
            mysqli_query($conn, "UPDATE tabung SET qty_total = qty_total - $qty, qty_kosong = qty_kosong - $qty WHERE tabung_id='$id_tabung'");
        } elseif ($jenis_jual == 'full_set') {
            $nominal     = $tabung['harga_full_set'] * $qty;
            $deskripsi   = "Penjualan Tabung+Isi " . $tabung['ukuran'] . " ($qty Pcs)";
            $kategori    = "Penjualan Lengkap";
            mysqli_query($conn, "UPDATE tabung SET qty_total = qty_total - $qty, qty_penuh = qty_penuh - $qty WHERE tabung_id='$id_tabung'");
        }

    } elseif ($tipe == 'pengeluaran') {
        $jenis_pengeluaran = mysqli_real_escape_string($conn, $_POST['jenis_pengeluaran']);

        if ($jenis_pengeluaran == 'operasional') {
            $nominal    = (int)$_POST['nominal_manual'];
            $kategori   = mysqli_real_escape_string($conn, $_POST['kategori_ops']);
            $deskripsi  = mysqli_real_escape_string($conn, $_POST['deskripsi_ops']);
        } else {
            $id_tabung  = (int)$_POST['id_tabung_beli'];
            $qty        = (int)$_POST['qty_beli'];
            $nominal    = (int)$_POST['nominal_beli'];
            $tabung     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tabung WHERE tabung_id='$id_tabung'"));

            if ($jenis_pengeluaran == 'restok_refill') {
                $kategori  = "Restok Gas (Refill)";
                $deskripsi = "Kulakan Isi Ulang " . $tabung['ukuran'] . " ($qty Pcs)";
                mysqli_query($conn, "UPDATE tabung SET qty_kosong = qty_kosong - $qty, qty_penuh = qty_penuh + $qty WHERE tabung_id='$id_tabung'");
            } elseif ($jenis_pengeluaran == 'restok_baru') {
                $kategori  = "Beli Aset Tabung+Isi";
                $deskripsi = "Beli Tabung Baru+Isi " . $tabung['ukuran'] . " ($qty Pcs)";
                mysqli_query($conn, "UPDATE tabung SET qty_total = qty_total + $qty, qty_penuh = qty_penuh + $qty WHERE tabung_id='$id_tabung'");
            } elseif ($jenis_pengeluaran == 'restok_kosong') {
                $kategori  = "Beli Aset Tabung Kosong";
                $deskripsi = "Beli Tabung Kosong " . $tabung['ukuran'] . " ($qty Pcs)";
                mysqli_query($conn, "UPDATE tabung SET qty_total = qty_total + $qty, qty_kosong = qty_kosong + $qty WHERE tabung_id='$id_tabung'");
            }
        }
    }

    $query = "INSERT INTO transaksi_keuangan (tanggal, tipe, kategori, deskripsi, nominal, metode_bayar) 
              VALUES ('$tanggal', '$tipe', '$kategori', '$deskripsi', '$nominal', '$metode')";

    if (mysqli_query($conn, $query)) {
        $id_transaksi = mysqli_insert_id($conn);
        if ($tipe == 'pemasukan') {
            // ✅ Redirect ke admin_data_tabung.php
            echo "<script>if(confirm('Transaksi Berhasil! Cetak Struk?')){ window.open('../keuangan/cetak_struk.php?id=$id_transaksi', '_blank'); } window.location='admin_data_tabung.php';</script>";
        } else {
            echo "<script>window.location='admin_data_tabung.php';</script>";
        }
        exit();
    }
}

// ================================================================
// LOGIKA NOTIFIKASI STOK MENIPIS
// ================================================================
mysqli_query($conn, "
    DELETE n FROM notifikasi_stok n
    JOIN tabung t ON n.tabung_id = t.tabung_id
    WHERE t.qty_penuh > t.threshold
");

$q_cek_threshold = mysqli_query($conn, "SELECT * FROM tabung WHERE qty_penuh <= threshold AND status = 'aktif'");
while ($t = mysqli_fetch_assoc($q_cek_threshold)) {
    $tid       = $t['tabung_id'];
    $cek_notif = mysqli_query($conn, "SELECT notif_id FROM notifikasi_stok WHERE tabung_id='$tid' AND status='belum_dibaca'");
    if (mysqli_num_rows($cek_notif) == 0) {
        $pesan = "Stok tabung {$t['kode_tabung']} ({$t['ukuran']}) menipis! Stok penuh tersisa {$t['qty_penuh']} unit (batas: {$t['threshold']} unit).";
        mysqli_query($conn, "INSERT INTO notifikasi_stok (tabung_id, pesan, status) VALUES ('$tid', '$pesan', 'belum_dibaca')");
    }
}

$q_notif = mysqli_query($conn, "
    SELECT n.*, t.kode_tabung, t.ukuran, t.qty_penuh, t.threshold 
    FROM notifikasi_stok n 
    JOIN tabung t ON n.tabung_id = t.tabung_id 
    WHERE n.status = 'belum_dibaca' 
    ORDER BY n.tanggal DESC
");
$jumlah_notif = mysqli_num_rows($q_notif);

// ================================================================
// AMBIL DATA UTAMA
// ================================================================
$data         = mysqli_query($conn, "SELECT * FROM tabung ORDER BY created_at ASC");
// ✅ Ambil data_tabung sebagai array agar bisa dipakai berkali-kali tanpa data_seek
$data_tabung  = [];
$res_tabung   = mysqli_query($conn, "SELECT * FROM tabung WHERE status='aktif'");
while ($row_t = mysqli_fetch_assoc($res_tabung)) {
    $data_tabung[] = $row_t;
}

$total_aset   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(qty_total) as total FROM tabung"))['total'] ?? 0;
$total_penuh  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(qty_penuh) as total FROM tabung"))['total'] ?? 0;
$total_kosong = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(qty_kosong) as total FROM tabung"))['total'] ?? 0;
?>

<style>
    .img-thumb {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        border: 2px solid rgba(255,255,255,0.1);
    }
    .badge.bg-info      { background-color: rgba(56, 189, 248, 0.2) !important; color: #38bdf8 !important; border: 1px solid rgba(56, 189, 248, 0.3); }
    .badge.bg-success   { background-color: rgba(52, 211, 153, 0.2) !important; color: #34d399 !important; border: 1px solid rgba(52, 211, 153, 0.3); }
    .badge.bg-secondary { background-color: rgba(148, 163, 184, 0.2) !important; color: #94a3b8 !important; }

    .price-card {
        background: rgba(255,255,255,0.05);
        border-radius: 12px;
        padding: 8px;
        border: 1px solid rgba(255,255,255,0.05);
    }
    .label-form { color: #e5e7eb !important; font-weight: 600; }

    /* ---- NOTIFIKASI ---- */
    .notif-wrapper { position: relative; display: inline-block; }
    .notif-badge {
        position: absolute;
        top: -6px; right: -6px;
        background: #ef4444; color: white;
        border-radius: 50%;
        width: 20px; height: 20px;
        font-size: 11px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        animation: pulse-red 1.5s infinite;
        border: 2px solid #1e293b;
    }
    @keyframes pulse-red {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.5); }
        50%       { box-shadow: 0 0 0 6px rgba(239,68,68,0); }
    }
    .alert-stok-menipis {
        background: linear-gradient(135deg, rgba(239,68,68,0.12), rgba(251,146,60,0.08));
        border: 1px solid rgba(239,68,68,0.35);
        border-left: 4px solid #ef4444;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 8px;
        display: flex; align-items: center; justify-content: space-between;
        transition: all 0.3s ease;
    }
    .alert-stok-menipis:hover {
        background: linear-gradient(135deg, rgba(239,68,68,0.18), rgba(251,146,60,0.12));
        transform: translateX(3px);
    }
    .alert-stok-menipis .notif-icon {
        width: 40px; height: 40px;
        background: rgba(239,68,68,0.15);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .panel-notifikasi {
        background: rgba(15,23,42,0.95);
        border: 1px solid rgba(239,68,68,0.3);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .panel-notifikasi .panel-header {
        background: linear-gradient(135deg, rgba(239,68,68,0.2), rgba(251,146,60,0.1));
        border-bottom: 1px solid rgba(239,68,68,0.2);
        padding: 16px 20px;
    }
</style>

<div class="container mt-4">

    <!-- HEADER HALAMAN -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-white mb-1">Inventaris Tabung</h4>
            <p class="text-muted mb-0 small">Kelola stok fisik, update harga jual, dan catat transaksi.</p>
        </div>

        <div class="d-flex gap-2 align-items-center flex-wrap">
            <!-- Tombol Notifikasi -->
            <?php if ($jumlah_notif > 0): ?>
            <div class="notif-wrapper">
                <button class="btn btn-outline-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNotifikasi">
                    <i class="bi bi-bell-fill me-1"></i> Notifikasi
                </button>
                <span class="notif-badge"><?= $jumlah_notif ?></span>
            </div>
            <?php else: ?>
            <button class="btn btn-outline-secondary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNotifikasi" title="Tidak ada notifikasi baru">
                <i class="bi bi-bell me-1"></i> Notifikasi
            </button>
            <?php endif; ?>

            <!-- Tombol Transaksi Baru -->
            <button class="btn btn-success shadow-lg" data-bs-toggle="modal" data-bs-target="#modalTransaksi">
                <i class="bi bi-cash-coin me-1"></i> Transaksi Baru
            </button>

            <!-- Tombol Tambah Tabung -->
            <button class="btn btn-primary shadow-lg" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i> Tambah Tabung
            </button>
        </div>
    </div>

    <!-- BANNER PERINGATAN STOK KRITIS -->
    <?php if ($jumlah_notif > 0): mysqli_data_seek($q_notif, 0); ?>
    <div class="alert d-flex align-items-center gap-3 mb-4"
         style="background: linear-gradient(135deg,rgba(239,68,68,.15),rgba(251,146,60,.08)); border: 1px solid rgba(239,68,68,.4); border-radius:14px; padding: 14px 20px;">
        <div style="width:42px;height:42px;background:rgba(239,68,68,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
        </div>
        <div class="flex-grow-1">
            <div class="fw-bold text-danger mb-1" style="font-size:.9rem;">
                Peringatan: <?= $jumlah_notif ?> Tabung Stok Menipis!
            </div>
            <div class="">Segera lakukan pengisian ulang.</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- KARTU STATISTIK -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-stat bg-gradient-primary h-100 p-3 border-0">
                <div class="d-flex align-items-center" style="z-index:2;">
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle me-3 text-white">
                        <i class="bi bi-layers-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-white text-opacity-75 mb-0 text-uppercase" style="font-size:.8rem;">Total Aset Tabung</h6>
                        <h2 class="fw-bold text-white mb-0"><?= $total_aset ?> <span class="fs-6 fw-normal">Unit</span></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat h-100 p-3 border-0" style="background: linear-gradient(135deg, #065f46, #047857);">
                <div class="d-flex align-items-center" style="z-index:2;">
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle me-3 text-white">
                        <i class="bi bi-battery-full fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-white text-opacity-75 mb-0 text-uppercase" style="font-size:.8rem;">Stok Penuh</h6>
                        <h2 class="fw-bold text-white mb-0"><?= $total_penuh ?> <span class="fs-6 fw-normal">Unit</span></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat bg-gradient-danger h-100 p-3 border-0">
                <div class="d-flex align-items-center" style="z-index:2;">
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle me-3 text-white">
                        <i class="bi bi-battery-half fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-white text-opacity-75 mb-0 text-uppercase" style="font-size:.8rem;">Stok Kosong (Butuh Refill)</h6>
                        <h2 class="fw-bold text-white mb-0"><?= $total_kosong ?> <span class="fs-6 fw-normal">Unit</span></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL DATA TABUNG -->
    <div class="card shadow-lg">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Info Barang</th>
                            <th>Status Stok</th>
                            <th>Harga Jual</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($data)):
                            $persen    = ($row['qty_total'] > 0) ? ($row['qty_penuh'] / $row['qty_total']) * 100 : 0;
                            $is_kritis = ($row['qty_penuh'] <= $row['threshold'] && $row['status'] == 'aktif');
                        ?>
                        <tr <?= $is_kritis ? 'style="background: rgba(239,68,68,0.04); border-left: 3px solid rgba(239,68,68,0.5);"' : '' ?>>

                            <!-- INFO BARANG -->
                            <td style="width:25%;">
                                <div class="d-flex align-items-center">
                                    <?php $img = !empty($row['foto']) ? "/oksigen_aca/assets/img/" . $row['foto'] : "https://via.placeholder.com/60"; ?>
                                    <img src="<?= $img ?>" class="img-thumb me-3">
                                    <div>
                                        <div class="fw-bold text-black mb-1"><?= htmlspecialchars($row['kode_tabung']) ?></div>
                                        <span class="badge bg-info me-1"><?= htmlspecialchars($row['ukuran']) ?></span>
                                        <span class="badge bg-<?= $row['status'] == 'aktif' ? 'success' : 'secondary' ?>"><?= ucfirst($row['status']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <!-- STATUS STOK -->
                            <td style="width:25%;">
                                <div class="d-flex justify-content-between small fw-bold mb-2">
                                    <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Penuh: <?= $row['qty_penuh'] ?></span>
                                    <span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Kosong: <?= $row['qty_kosong'] ?></span>
                                </div>
                                <div class="progress" style="height:8px; background-color:rgba(255,255,255,0.1);">
                                    <div class="progress-bar bg-gradient-success" style="width:<?= $persen ?>%"></div>
                                    <div class="progress-bar bg-gradient-danger"  style="width:<?= 100 - $persen ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mt-2">
                                    <span>Total Fisik: <strong><?= $row['qty_total'] ?></strong></span>
                                    <?php if ($row['threshold'] > 0): ?>
                                    <span class="<?= $is_kritis ? 'text-danger fw-bold' : '' ?>">
                                        Min: <?= $row['threshold'] ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- HARGA JUAL -->
                            <td style="width:35%;">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="price-card text-center">
                                            <small class="text-muted d-block" style="font-size:10px;">REFILL</small>
                                            <span class="text-success fw-bold small">Rp <?= number_format($row['harga_refill'], 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="price-card text-center">
                                            <small class="text-muted d-block" style="font-size:10px;">KOSONG</small>
                                            <span class="text-info fw-bold small">Rp <?= number_format($row['harga_tabung_kosong'], 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="price-card text-center border-primary">
                                            <small class="text-muted d-block" style="font-size:10px;">PAKET FULL SET</small>
                                            <span class="text-black fw-bold">Rp <?= number_format($row['harga_full_set'], 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- AKSI -->
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm btn-warning text-white shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#edit<?= $row['tabung_id'] ?>" title="Edit Data">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-secondary shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#koreksi<?= $row['tabung_id'] ?>" title="Koreksi Stok">
                                        <i class="bi bi-sliders"></i>
                                    </button>
                                    <a href="?hapus=<?= $row['tabung_id'] ?>"
                                       onclick="return confirm('Hapus tabung ini beserta notifikasinya secara permanen?')"
                                       class="btn btn-sm btn-danger shadow-sm" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: TRANSAKSI BARU                                        -->
<!-- ============================================================ -->
<div class="modal fade" id="modalTransaksi" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold text-white"><i class="bi bi-cash-coin me-2 text-success"></i>Input Transaksi Baru</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pt-4">

            <!-- PILIH TIPE TRANSAKSI -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <input type="radio" class="btn-check" name="tipe" id="tipeMasuk" value="pemasukan" checked onclick="toggleForm('pemasukan')">
                    <label class="btn btn-outline-success w-100 py-3 fw-bold rounded-4 border-2 h-100 d-flex flex-column align-items-center justify-content-center gap-2" for="tipeMasuk">
                        <i class="bi bi-cart-check-fill fs-3"></i>
                        <span>PENJUALAN (Masuk)</span>
                    </label>
                </div>
                <div class="col-6">
                    <input type="radio" class="btn-check" name="tipe" id="tipeKeluar" value="pengeluaran" onclick="toggleForm('pengeluaran')">
                    <label class="btn btn-outline-danger w-100 py-3 fw-bold rounded-4 border-2 h-100 d-flex flex-column align-items-center justify-content-center gap-2" for="tipeKeluar">
                        <i class="bi bi-box-seam-fill fs-3"></i>
                        <span>RESTOK / BELANJA (Keluar)</span>
                    </label>
                </div>
            </div>

            <!-- INPUT TANGGAL -->
            <div class="mb-4">
                <label class="small label-form">Tanggal Transaksi</label>
                <input type="date" name="tanggal" class="form-control bg-dark text-white border-secondary"
                       value="<?= date('Y-m-d') ?>" required>
            </div>

            <!-- FORM PEMASUKAN -->
            <div id="form-pemasukan" class="p-4 rounded-4" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2);">
                <h6 class="text-success fw-bold mb-3">Keranjang Belanja Pelanggan</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small label-form">Pilih Tabung</label>
                        <select name="id_tabung_jual" id="pilihTabungJual" class="form-select bg-dark text-white border-secondary" onchange="hitungTotalJual()">
                            <option value="">-- Pilih Produk --</option>
                            <?php foreach ($data_tabung as $tbg): ?>
                                <option value="<?= $tbg['tabung_id'] ?>"
                                    data-refill="<?= $tbg['harga_refill'] ?>"
                                    data-kosong="<?= $tbg['harga_tabung_kosong'] ?>"
                                    data-full="<?= $tbg['harga_full_set'] ?>">
                                    <?= htmlspecialchars($tbg['kode_tabung']) ?> - <?= htmlspecialchars($tbg['ukuran']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small label-form">Jenis Transaksi</label>
                        <select name="jenis_jual" id="jenisJual" class="form-select bg-dark text-white border-secondary" onchange="hitungTotalJual()">
                            <option value="refill">Isi Ulang (Refill)</option>
                            <option value="full_set">Beli Tabung + Isi</option>
                            <option value="tabung_kosong">Beli Tabung Kosong</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small label-form">Qty</label>
                        <input type="number" name="qty_jual" id="qtyJual" class="form-control bg-dark text-white border-secondary" value="1" min="1" oninput="hitungTotalJual()">
                    </div>
                    <div class="col-md-6 text-end">
                        <label class="small label-form">Total Estimasi</label>
                        <h3 class="fw-bold text-success mb-0" id="totalJual">Rp 0</h3>
                    </div>
                </div>
            </div>

            <!-- FORM PENGELUARAN -->
            <div id="form-pengeluaran" class="d-none p-4 rounded-4" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);">
                <h6 class="text-danger fw-bold mb-3"><i class="bi bi-bag-x-fill me-2"></i>Detail Pengeluaran Toko</h6>

                <div class="mb-3">
                    <label class="small label-form">Kategori Pengeluaran</label>
                    <select name="jenis_pengeluaran" id="jenisKeluar" class="form-select bg-dark text-white border-secondary" onchange="toggleRestok()">
                        <option value="restok_refill">Kulakan Gas (Refill)</option>
                        <option value="restok_baru">Beli Tabung Baru</option>
                        <option value="restok_kosong">Beli Tabung Kosong</option>
                        <option value="operasional">Biaya Operasional</option>
                    </select>
                </div>

                <div id="input-restok">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="small label-form">Produk yang Dibeli</label>
                            <select name="id_tabung_beli" class="form-select bg-dark text-white border-secondary">
                                <!-- ✅ Pakai array $data_tabung, tidak perlu data_seek -->
                                <?php foreach ($data_tabung as $tbg): ?>
                                    <option value="<?= $tbg['tabung_id'] ?>"><?= htmlspecialchars($tbg['kode_tabung']) ?> - <?= htmlspecialchars($tbg['ukuran']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small label-form">Qty</label>
                            <input type="number" name="qty_beli" class="form-control bg-dark text-white border-secondary" min="1">
                        </div>
                        <div class="col-12">
                            <label class="small label-form">Total Biaya Kulakan (Manual)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-secondary border-secondary text-white">Rp</span>
                                <input type="number" name="nominal_beli" class="form-control bg-dark text-white border-secondary" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="input-ops" class="d-none">
                    <div class="mb-2">
                        <label class="small label-form">Nama Pengeluaran</label>
                        <input type="text" name="kategori_ops" class="form-control bg-dark text-white border-secondary" placeholder="Contoh: Bayar Listrik">
                    </div>
                    <div class="mb-2">
                        <label class="small label-form">Catatan</label>
                        <textarea name="deskripsi_ops" class="form-control bg-dark text-white border-secondary" rows="2"></textarea>
                    </div>
                    <div>
                        <label class="small label-form">Nominal</label>
                        <input type="number" name="nominal_manual" class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>
            </div>

            <!-- METODE BAYAR -->
            <div class="mt-4">
                <label class="small label-form">Metode Pembayaran</label>
                <select name="metode_bayar" class="form-select bg-dark text-white border-secondary">
                    <option value="tunai">Tunai (Cash)</option>
                    <option value="transfer">Transfer Bank</option>
                </select>
            </div>

        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
            <button type="button" class="btn btn-outline-light text-muted border-0" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="tambah_transaksi" class="btn btn-success px-5">
                <i class="bi bi-floppy-fill me-1"></i> Simpan Transaksi
            </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: NOTIFIKASI STOK MENIPIS                              -->
<!-- ============================================================ -->
<div class="modal fade" id="modalNotifikasi" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content panel-notifikasi">
      <div class="panel-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-white mb-0">
                <i class="bi bi-bell-fill text-danger me-2"></i> Notifikasi Stok
            </h5>
            <small class="text-muted">
                <?= $jumlah_notif > 0 ? "$jumlah_notif peringatan belum dibaca" : "Semua stok dalam kondisi aman" ?>
            </small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <?php if ($jumlah_notif == 0): ?>
            <div class="text-center py-5">
                <div style="width:70px;height:70px;background:rgba(52,211,153,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="bi bi-shield-check text-success fs-2"></i>
                </div>
                <h6 class="text-white mb-1">Semua Stok Aman</h6>
                <p class="text-muted small mb-0">Tidak ada tabung yang stoknya di bawah batas minimum.</p>
            </div>
        <?php else: mysqli_data_seek($q_notif, 0);
              while ($notif = mysqli_fetch_assoc($q_notif)): ?>
            <div class="alert-stok-menipis">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <div class="notif-icon">
                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white mb-1" style="font-size:.88rem;"><?= htmlspecialchars($notif['pesan']) ?></div>
                        <div class="d-flex gap-3 small">
                            <span style="color:rgba(255,255,255,.45);">
                                <i class="bi bi-clock me-1"></i>
                                <?= date('d M Y, H:i', strtotime($notif['tanggal'])) ?>
                            </span>
                            <span class="text-danger">
                                <i class="bi bi-bar-chart-fill me-1"></i>
                                Stok: <?= $notif['qty_penuh'] ?> / Min: <?= $notif['threshold'] ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: TAMBAH TABUNG                                         -->
<!-- ============================================================ -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
            <h5 class="modal-title fw-bold text-white"><i class="bi bi-plus-circle me-2"></i> Tambah Tabung Baru</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="small label-form">Kode Tabung</label>
                <input type="text" name="kode_tabung" class="form-control" required>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="small label-form">Ukuran</label>
                    <input type="text" name="ukuran" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="small label-form">Stok Awal</label>
                    <input type="number" name="qty_total" class="form-control" required min="0">
                </div>
            </div>
            <h6 class="text-success mt-4 mb-2 pb-1 border-bottom border-secondary">Setting Harga Awal</h6>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="small label-form">Harga Refill</label>
                    <input type="number" name="harga_refill" class="form-control" placeholder="0" min="0">
                </div>
                <div class="col-6">
                    <label class="small label-form">Harga Kosong</label>
                    <input type="number" name="harga_tabung_kosong" class="form-control" placeholder="0" min="0">
                </div>
                <div class="col-12">
                    <label class="small label-form">Harga Full Set</label>
                    <input type="number" name="harga_full_set" class="form-control" placeholder="0" min="0">
                </div>
            </div>
            <div class="mb-2">
                <label class="small label-form">Foto Produk</label>
                <input type="file" name="foto" class="form-control" accept="image/*">
            </div>
        </div>
        <div class="modal-footer border-top border-secondary">
            <button type="submit" name="tambah" class="btn btn-primary w-100">
                <i class="bi bi-floppy-fill me-1"></i> Simpan Data
            </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: EDIT & KOREKSI (per tabung)                          -->
<!-- ============================================================ -->
<?php mysqli_data_seek($data, 0); while ($row = mysqli_fetch_assoc($data)): ?>

<!-- Modal Edit -->
<div class="modal fade" id="edit<?= $row['tabung_id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title fw-bold text-white">Edit Data Tabung</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-body" style="overflow-y:auto; max-height:65vh;">
            <input type="hidden" name="tabung_id" value="<?= $row['tabung_id'] ?>">

            <h6 class="fw-bold mb-3 pb-2" style="color:#60a5fa; border-bottom:1px solid rgba(96,165,250,.25); font-size:.8rem; text-transform:uppercase; letter-spacing:.08em;">
                Identitas Barang
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <label class="small label-form">Kode Tabung</label>
                    <input type="text" name="kode_tabung" value="<?= htmlspecialchars($row['kode_tabung']) ?>" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="small label-form">Ukuran</label>
                    <input type="text" name="ukuran" value="<?= htmlspecialchars($row['ukuran']) ?>" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="small label-form">Batas Minimum Stok <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="threshold" value="<?= $row['threshold'] ?>" class="form-control" min="0" required>
                        <span class="input-group-text" style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.15);color:#94a3b8;font-size:.8rem;">unit</span>
                    </div>
                    <div class="form-text text-muted" style="font-size:11px;">Notifikasi muncul jika stok penuh ≤ nilai ini</div>
                </div>
                <div class="col-6">
                    <label class="small label-form">Status</label>
                    <select name="status" class="form-select">
                        <option value="aktif"    <?= $row['status'] == 'aktif'    ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= $row['status'] == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
            </div>

            <h6 class="fw-bold mb-3 pb-2" style="color:#34d399; border-bottom:1px solid rgba(52,211,153,.25); font-size:.8rem; text-transform:uppercase; letter-spacing:.08em;">
                Harga Jual
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label class="small label-form">Harga Refill</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.15);color:#94a3b8;font-size:.8rem;">Rp</span>
                        <input type="number" name="harga_refill" value="<?= $row['harga_refill'] ?>" class="form-control" min="0">
                    </div>
                </div>
                <div class="col-6">
                    <label class="small label-form">Harga Tabung Kosong</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.15);color:#94a3b8;font-size:.8rem;">Rp</span>
                        <input type="number" name="harga_tabung_kosong" value="<?= $row['harga_tabung_kosong'] ?>" class="form-control" min="0">
                    </div>
                </div>
                <div class="col-12">
                    <label class="small label-form">Harga Full Set (Tabung + Isi)</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.15);color:#94a3b8;font-size:.8rem;">Rp</span>
                        <input type="number" name="harga_full_set" value="<?= $row['harga_full_set'] ?>" class="form-control" min="0">
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-3 pb-2" style="color:#c084fc; border-bottom:1px solid rgba(192,132,252,.25); font-size:.8rem; text-transform:uppercase; letter-spacing:.08em;">
                Foto Produk
            </h6>
            <div class="d-flex align-items-start gap-3">
                <?php $foto_lama = !empty($row['foto']) ? "/oksigen_aca/assets/img/" . $row['foto'] : "https://via.placeholder.com/80?text=No+Foto"; ?>
                <div class="flex-shrink-0 text-center">
                    <img id="preview-foto-<?= $row['tabung_id'] ?>" src="<?= $foto_lama ?>"
                         style="width:80px;height:80px;object-fit:cover;border-radius:12px;border:2px solid rgba(255,255,255,.1);">
                    <div class="text-muted mt-1" style="font-size:10px;">Foto saat ini</div>
                </div>
                <div class="flex-grow-1">
                    <label class="small label-form">Ganti Foto <span class="text-muted fw-normal">(opsional)</span></label>
                    <input type="file" name="foto" class="form-control" accept="image/*"
                           onchange="previewFoto(this, 'preview-foto-<?= $row['tabung_id'] ?>')">
                    <div class="form-text text-muted" style="font-size:11px;">Kosongkan jika tidak ingin mengganti foto.</div>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.08);">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="edit" class="btn btn-warning text-white fw-bold flex-grow-1">
                <i class="bi bi-floppy-fill me-2"></i>Simpan Perubahan
            </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Koreksi Stok -->
<div class="modal fade" id="koreksi<?= $row['tabung_id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-secondary">
            <h6 class="modal-title text-white">Koreksi Stok Manual</h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <p class="small text-muted mb-3">Gunakan hanya jika ada selisih/hilang.</p>
            <input type="hidden" name="tabung_id" value="<?= $row['tabung_id'] ?>">
            <div class="mb-2">
                <label class="small text-muted">Jenis Koreksi</label>
                <select name="jenis_koreksi" class="form-select">
                    <option value="tambah_penuh">Tambah Stok Penuh (+)</option>
                    <option value="kurangi_penuh">Kurangi Stok Penuh (-)</option>
                    <option value="tambah_kosong">Tambah Stok Kosong (+)</option>
                    <option value="kurangi_kosong">Kurangi Stok Kosong (-)</option>
                </select>
            </div>
            <div>
                <label class="small text-muted">Jumlah</label>
                <input type="number" name="jumlah" class="form-control" required min="1">
            </div>
        </div>
        <div class="modal-footer border-top border-secondary">
            <button type="submit" name="koreksi_stok" class="btn btn-secondary w-100">Update Stok</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php endwhile; ?>

<script>
function previewFoto(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleForm(tipe) {
    if (tipe === 'pemasukan') {
        document.getElementById('form-pemasukan').classList.remove('d-none');
        document.getElementById('form-pengeluaran').classList.add('d-none');
    } else {
        document.getElementById('form-pemasukan').classList.add('d-none');
        document.getElementById('form-pengeluaran').classList.remove('d-none');
    }
}

function toggleRestok() {
    let jenis = document.getElementById('jenisKeluar').value;
    if (jenis === 'operasional') {
        document.getElementById('input-restok').classList.add('d-none');
        document.getElementById('input-ops').classList.remove('d-none');
    } else {
        document.getElementById('input-restok').classList.remove('d-none');
        document.getElementById('input-ops').classList.add('d-none');
    }
}

function hitungTotalJual() {
    let tabung = document.getElementById('pilihTabungJual');
    let jenis  = document.getElementById('jenisJual').value;
    let qty    = document.getElementById('qtyJual').value;
    let selectedOption = tabung.options[tabung.selectedIndex];
    let harga  = 0;

    if (selectedOption.value !== "") {
        if (jenis === 'refill')             harga = selectedOption.getAttribute('data-refill');
        else if (jenis === 'tabung_kosong') harga = selectedOption.getAttribute('data-kosong');
        else if (jenis === 'full_set')      harga = selectedOption.getAttribute('data-full');
    }

    let total = harga * qty;
    document.getElementById('totalJual').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(total);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>