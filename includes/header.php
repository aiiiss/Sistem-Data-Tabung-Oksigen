<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Oksigen ACA</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="/oksigen_aca/assets/css/style_modern.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/oksigen_aca/dashboard.php">
        <i class="bi bi-wind"></i> Oksigen PGM
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto gap-2 align-items-center">
          <li class="nav-item">
              <a class="nav-link text-white" href="/oksigen_aca/dashboard.php">
                  <i class="bi bi-grid-fill"></i> Dashboard
              </a>
          </li>
          <li class="nav-item">
              <a class="nav-link text-white" href="/oksigen_aca/pages/stok_tabung/data_tabung.php">
                  <i class="bi bi-box-seam-fill"></i> Stok
              </a>
          </li>
          <li class="nav-item">
              <a class="nav-link text-white" href="/oksigen_aca/pages/keuangan/data_transaksi.php">
                  <i class="bi bi-wallet-fill"></i> Keuangan
              </a>
          </li>
          <li class="nav-item">
              <a class="nav-link text-white" href="/oksigen_aca/pages/deskripsi.php">
                  <i class="bi bi-info-circle-fill"></i> Info
              </a>
          </li>
          
          <li class="nav-item ms-2">
              <a class="btn btn-danger text-white px-4 shadow-sm" href="/oksigen_aca/logout.php">
                  <i class="bi bi-power"></i> Keluar
              </a>
          </li>
        </ul>
    </div>
  </div>
</nav>

<div class="container mt-4 mb-5">