<?php
include '../../includes/koneksi.php';
include '../../includes/header.php';

// Halaman KHUSUS pemilik saja — contoh: dashboard, keuangan, laporan, dll
$allowed_roles = ['pemilik'];
include '../../includes/auth_check.php';

// ================================================================
// HAPUS TRANSAKSI
// ================================================================
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM transaksi_keuangan WHERE transaksi_id='$id_hapus'");
    echo "<script>window.location='data_transaksi.php';</script>";
}

// ================================================================
// STATISTIK KEUANGAN — ALL TIME
// ================================================================
$pemasukan   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) AS total FROM transaksi_keuangan WHERE tipe='pemasukan'"))['total'] ?? 0;
$pengeluaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) AS total FROM transaksi_keuangan WHERE tipe='pengeluaran'"))['total'] ?? 0;
$laba_kotor  = $pemasukan - $pengeluaran;

// ================================================================
// STATISTIK KEUANGAN — BULAN INI
// ================================================================
$bulan_ini         = date('Y-m');
$pemasukan_bulan   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) AS total FROM transaksi_keuangan WHERE tipe='pemasukan' AND DATE_FORMAT(tanggal,'%Y-%m')='$bulan_ini'"))['total'] ?? 0;
$pengeluaran_bulan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) AS total FROM transaksi_keuangan WHERE tipe='pengeluaran' AND DATE_FORMAT(tanggal,'%Y-%m')='$bulan_ini'"))['total'] ?? 0;
$laba_bulan        = $pemasukan_bulan - $pengeluaran_bulan;

// ================================================================
// DATA TABUNG AKTIF (untuk kartu harga)
// ================================================================
$data_tabung = mysqli_query($conn, "SELECT * FROM tabung WHERE status='aktif' ORDER BY kode_tabung ASC");

// ================================================================
// DATA TRANSAKSI
// ================================================================
$data_transaksi = mysqli_query($conn, "SELECT * FROM transaksi_keuangan ORDER BY tanggal DESC, transaksi_id DESC");
?>

<style>
    /* === KARTU STATISTIK === */
    .stat-card {
        border-radius: 16px;
        padding: 18px 20px;
        border: 0;
        position: relative;
        overflow: hidden;
    }
    .stat-card .stat-icon {
        width: 46px; height: 46px;
        border-radius: 13px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
        background: rgba(255,255,255,0.15);
        color: white;
        flex-shrink: 0;
    }
    .stat-card .stat-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: rgba(255,255,255,0.6);
        font-weight: 600;
    }
    .stat-card .stat-value {
        font-size: 1.3rem;
        font-weight: 800;
        color: white;
        line-height: 1.2;
    }
    .stat-card .stat-sub {
        font-size: .72rem;
        color: rgba(255,255,255,0.5);
        margin-top: 1px;
    }

    /* === LABA KOTOR === */
    .laba-card {
        border-radius: 16px;
        padding: 20px 22px;
        border: 2px solid transparent;
    }
    .laba-positif {
        background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(5,150,105,0.05));
        border-color: rgba(16,185,129,0.25);
    }
    .laba-negatif {
        background: linear-gradient(135deg, rgba(239,68,68,0.1), rgba(220,38,38,0.05));
        border-color: rgba(239,68,68,0.25);
    }
    .laba-nilai {
        font-size: 1.9rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .laba-breakdown-item {
        flex: 1;
        padding: 8px 10px;
        border-radius: 10px;
        text-align: center;
    }
    .laba-breakdown-item .bd-label {
        font-size: .68rem;
        color: rgba(255,255,255,.4);
        margin-bottom: 2px;
    }
    .laba-breakdown-item .bd-val {
        font-size: .82rem;
        font-weight: 700;
    }

    /* === KARTU HARGA TABUNG === */
    .tabung-price-card {
        border-radius: 16px;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        transition: all 0.25s ease;
        overflow: hidden;
    }
    .tabung-price-card:hover {
        background: rgba(255,255,255,0.06);
        border-color: rgba(99,102,241,0.35);
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    }
    .tabung-price-card .tc-header {
        padding: 14px 16px 10px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .tabung-price-card .img-tabung {
        width: 50px; height: 50px;
        object-fit: cover;
        border-radius: 12px;
        border: 2px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
    }
    .tabung-price-card .stok-bar-wrap {
        padding: 10px 16px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .tabung-price-card .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 9px 16px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .tabung-price-card .price-row:last-child { border-bottom: 0; }
    .tabung-price-card .price-label {
        font-size: .75rem;
        color: rgba(255,255,255,.4);
        font-weight: 500;
    }
    .tabung-price-card .price-val {
        font-size: .88rem;
        font-weight: 700;
    }

    /* === SECTION TITLE === */
    .section-title {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .1em;
        font-weight: 700;
        color: rgba(255,255,255,.35);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,.07);
    }

    /* === TABEL TRANSAKSI === */
    .tabel-wrap { background: rgba(15,23,42,0.8); border-radius: 16px; overflow: hidden; }
    .tabel-wrap table thead th {
        background: rgba(30,41,59,0.95);
        color: rgba(255,255,255,.65);
        font-size: .73rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        font-weight: 600;
        border: 0;
        padding: 13px 14px;
    }
    .tabel-wrap table tbody tr {
        border-bottom: 1px solid rgba(255,255,255,.04);
        transition: background .15s;
    }
    .tabel-wrap table tbody tr:hover { background: rgba(255,255,255,.025); }
    .tabel-wrap table tbody td {
        padding: 11px 14px;
        vertical-align: middle;
        border: 0;
    }
</style>

<div class="container mt-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-white mb-1">Keuangan & Transaksi</h4>
            <p class="text-muted mb-0 small">Riwayat transaksi, informasi harga produk, dan analisa laba.</p>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- BAGIAN 1: KARTU STATISTIK                                    -->
    <!-- ============================================================ -->
    <div class="section-title"><i class="bi bi-bar-chart-fill"></i> Ringkasan Keuangan</div>
    <div class="row g-3 mb-4">

        <!-- Total Pemasukan -->
        <div class="col-md-3 col-6">
            <div class="stat-card h-100 d-flex align-items-center gap-3" style="background:linear-gradient(135deg,#065f46,#047857);">
                <div class="stat-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
                <div>
                    <div class="stat-label">Total Pemasukan</div>
                    <div class="stat-value">Rp <?= number_format($pemasukan, 0, ',', '.') ?></div>
                    <div class="stat-sub">Bulan ini: Rp <?= number_format($pemasukan_bulan, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- Total Pengeluaran -->
        <div class="col-md-3 col-6">
            <div class="stat-card h-100 d-flex align-items-center gap-3" style="background:linear-gradient(135deg,#7f1d1d,#dc2626);">
                <div class="stat-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
                <div>
                    <div class="stat-label">Total Pengeluaran</div>
                    <div class="stat-value">Rp <?= number_format($pengeluaran, 0, ',', '.') ?></div>
                    <div class="stat-sub">Bulan ini: Rp <?= number_format($pengeluaran_bulan, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- Saldo Kas -->
        <div class="col-md-3 col-6">
            <div class="stat-card h-100 d-flex align-items-center gap-3" style="background:linear-gradient(135deg,#1e3a5f,#1d4ed8);">
                <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="stat-label">Saldo Kas</div>
                    <div class="stat-value">Rp <?= number_format($laba_kotor, 0, ',', '.') ?></div>
                    <div class="stat-sub">Pemasukan &minus; Pengeluaran</div>
                </div>
            </div>
        </div>

        <!-- Laba Bulan Ini -->
        <div class="col-md-3 col-6">
            <div class="stat-card h-100 d-flex align-items-center gap-3"
                 style="background:<?= $laba_bulan >= 0 ? 'linear-gradient(135deg,#14532d,#16a34a)' : 'linear-gradient(135deg,#450a0a,#b91c1c)' ?>">
                <div class="stat-icon">
                    <i class="bi bi-<?= $laba_bulan >= 0 ? 'emoji-smile-fill' : 'emoji-frown-fill' ?>"></i>
                </div>
                <div>
                    <div class="stat-label">Laba Bulan Ini</div>
                    <div class="stat-value">Rp <?= number_format(abs($laba_bulan), 0, ',', '.') ?></div>
                    <div class="stat-sub"><?= $laba_bulan >= 0 ? 'Untung' : 'Rugi' ?> &mdash; <?= date('M Y') ?></div>
                </div>
            </div>
        </div>

    </div>

    

    <!-- ============================================================ -->
    <!-- BAGIAN 3: INFORMASI HARGA TABUNG PER PRODUK                  -->
    <!-- ============================================================ -->
    <div class="section-title"><i class="bi bi-tags-fill"></i> Daftar Harga Produk Aktif</div>
    <div class="row g-3 mb-4">
        <?php while ($tbg = mysqli_fetch_assoc($data_tabung)):
            $img         = !empty($tbg['foto']) ? "/oksigen_aca/assets/img/" . $tbg['foto'] : "https://via.placeholder.com/50";
            $is_kritis   = ($tbg['qty_penuh'] <= $tbg['threshold']);
            $persen_stok = ($tbg['qty_total'] > 0) ? round(($tbg['qty_penuh'] / $tbg['qty_total']) * 100) : 0;
        ?>
        <div class="col-lg-4 col-md-6">
            <div class="tabung-price-card h-100">

                <!-- Header: foto + nama + badge -->
                <div class="tc-header">
                    <img src="<?= $img ?>" class="img-tabung" alt="<?= htmlspecialchars($tbg['kode_tabung']) ?>">
                    <div class="flex-grow-1">
                        <div class="fw-bold text-white mb-1" style="font-size:.95rem;"><?= htmlspecialchars($tbg['kode_tabung']) ?></div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span style="font-size:11px;background:rgba(56,189,248,.13);color:#38bdf8;border:1px solid rgba(56,189,248,.22);border-radius:20px;padding:2px 10px;font-weight:600;">
                                <?= htmlspecialchars($tbg['ukuran']) ?>
                            </span>
                            <?php if ($is_kritis): ?>
                            <span style="font-size:11px;background:rgba(239,68,68,.13);color:#f87171;border:1px solid rgba(239,68,68,.22);border-radius:20px;padding:2px 10px;font-weight:600;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Stok Kritis
                            </span>
                            <?php else: ?>
                            <span style="font-size:11px;background:rgba(52,211,153,.13);color:#34d399;border:1px solid rgba(52,211,153,.22);border-radius:20px;padding:2px 10px;font-weight:600;">
                                <i class="bi bi-check-circle-fill me-1"></i>Stok Aman
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Stok Bar -->
                <div class="stok-bar-wrap">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.7rem;color:rgba(255,255,255,.4);">Stok Penuh / Total</span>
                        <span style="font-size:.7rem;color:rgba(255,255,255,.65);font-weight:700;"><?= $tbg['qty_penuh'] ?> / <?= $tbg['qty_total'] ?> unit</span>
                    </div>
                    <div class="progress" style="height:5px;background:rgba(255,255,255,.07);border-radius:10px;">
                        <div class="progress-bar" style="width:<?= $persen_stok ?>%;background:<?= $is_kritis ? '#ef4444' : '#10b981' ?>;border-radius:10px;transition:width .5s;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span style="font-size:.68rem;color:rgba(255,255,255,.3);">Kosong: <?= $tbg['qty_kosong'] ?> unit</span>
                        <span style="font-size:.68rem;color:rgba(255,255,255,.3);">Min: <?= $tbg['threshold'] ?> unit</span>
                    </div>
                </div>

                <!-- Harga Refill -->
                <div class="price-row">
                    <span class="price-label"><i class="bi bi-arrow-repeat me-2"></i>Harga Refill</span>
                    <span class="price-val text-success">Rp <?= number_format($tbg['harga_refill'], 0, ',', '.') ?></span>
                </div>

                <!-- Harga Tabung Kosong -->
                <div class="price-row">
                    <span class="price-label"><i class="bi bi-box me-2"></i>Harga Tabung Kosong</span>
                    <span class="price-val" style="color:#38bdf8;">Rp <?= number_format($tbg['harga_tabung_kosong'], 0, ',', '.') ?></span>
                </div>

                <!-- Harga Full Set -->
                <div class="price-row" style="background:rgba(99,102,241,.04);">
                    <span class="price-label"><i class="bi bi-stars me-2"></i>Harga Full Set</span>
                    <span class="price-val" style="color:#a78bfa;">Rp <?= number_format($tbg['harga_full_set'], 0, ',', '.') ?></span>
                </div>

            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- ============================================================ -->
    <!-- BAGIAN 4: TABEL RIWAYAT TRANSAKSI                            -->
    <!-- ============================================================ -->
    <div class="section-title"><i class="bi bi-clock-history"></i> Riwayat Transaksi</div>
    <div class="tabel-wrap shadow-lg mb-4">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <th>Tipe</th>
                        <th>Keterangan</th>
                        <th>Metode</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($data_transaksi)): ?>
                    <tr>
                        <td class="ps-4">
                            <span class="text-white fw-semibold" style="font-size:.88rem;">
                                <?= date('d/m/Y', strtotime($row['tanggal'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['tipe'] == 'pemasukan'): ?>
                            <span style="font-size:.78rem;background:rgba(16,185,129,.13);color:#34d399;border:1px solid rgba(16,185,129,.22);border-radius:20px;padding:4px 12px;font-weight:700;">
                                <i class="bi bi-arrow-down me-1"></i>Masuk
                            </span>
                            <?php else: ?>
                            <span style="font-size:.78rem;background:rgba(239,68,68,.13);color:#f87171;border:1px solid rgba(239,68,68,.22);border-radius:20px;padding:4px 12px;font-weight:700;">
                                <i class="bi bi-arrow-up me-1"></i>Keluar
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold text-white" style="font-size:.87rem;"><?= htmlspecialchars($row['kategori']) ?></div>
                            <small style="color:rgba(255,255,255,.4);"><?= htmlspecialchars($row['deskripsi']) ?></small>
                        </td>
                        <td>
                            <?php if ($row['metode_bayar'] == 'tunai'): ?>
                            <span style="font-size:.75rem;background:rgba(251,191,36,.1);color:#fbbf24;border:1px solid rgba(251,191,36,.2);border-radius:20px;padding:3px 10px;font-weight:600;">
                                <i class="bi bi-cash me-1"></i>Tunai
                            </span>
                            <?php else: ?>
                            <span style="font-size:.75rem;background:rgba(99,102,241,.1);color:#818cf8;border:1px solid rgba(99,102,241,.2);border-radius:20px;padding:3px 10px;font-weight:600;">
                                <i class="bi bi-bank me-1"></i>Transfer
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-bold" style="font-size:.95rem;color:<?= $row['tipe'] == 'pemasukan' ? '#34d399' : '#f87171' ?>;">
                            <?= $row['tipe'] == 'pemasukan' ? '+' : '&minus;' ?> Rp <?= number_format($row['nominal'], 0, ',', '.') ?>
                        </td>
                        <td class="text-center pe-4">
                            <?php if ($row['tipe'] == 'pemasukan'): ?>
                            <a href="cetak_struk.php?id=<?= $row['transaksi_id'] ?>" target="_blank"
                               class="btn btn-sm me-1"
                               style="background:rgba(99,102,241,.18);color:#818cf8;border:1px solid rgba(99,102,241,.28);"
                               title="Cetak Struk">
                                <i class="bi bi-printer-fill"></i>
                            </a>
                            <?php endif; ?>
                            <a href="?hapus=<?= $row['transaksi_id'] ?>"
                               onclick="return confirm('Hapus transaksi ini?')"
                               class="btn btn-sm"
                               style="background:rgba(239,68,68,.18);color:#f87171;border:1px solid rgba(239,68,68,.28);"
                               title="Hapus">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>