<?php
session_start();
include 'includes/koneksi.php';

// --- LOGIKA LOGIN PHP ---
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); 

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    $data  = mysqli_fetch_array($query);

    if ($data) {
        $_SESSION['user_id']  = $data['user_id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role']     = $data['role'];

        // ✅ Redirect berdasarkan role
        if ($data['role'] === 'admin') {
            header("Location: pages/stok_tabung/data_tabung.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Oksigen</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/oksigen_aca/assets/css/style_modern.css">

    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-color);
            position: relative;
            overflow: hidden;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            background: var(--card-bg);
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
            backdrop-filter: blur(10px);
        }
        .glow-blob {
            position: absolute;
            width: 300px;
            height: 300px;
            filter: blur(80px);
            border-radius: 50%;
            z-index: 1;
            opacity: 0.4;
        }
        .blob-1 { top: -50px; left: -50px; background: var(--primary-gradient); }
        .blob-2 { bottom: -50px; right: -50px; background: var(--secondary-gradient); }
        .input-group-text {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid #334155;
            border-right: none;
            color: #94a3b8;
            border-top-left-radius: 50px;
            border-bottom-left-radius: 50px;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid #334155;
            border-left: none;
            color: white;
            border-top-right-radius: 50px;
            border-bottom-right-radius: 50px;
            padding-left: 0;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 1);
            box-shadow: none;
            border-color: #ec4899;
        }
    </style>
</head>
<body>

    <div class="glow-blob blob-1"></div>
    <div class="glow-blob blob-2"></div>

    <div class="login-card">
        <div class="text-center mb-4">
            <i class="bi bi-wind text-white" style="font-size: 3.5rem; text-shadow: 0 0 15px #ec4899;"></i>
            <h3 class="fw-bold text-white mt-2">Welcome!</h3>
            <p class="text-muted small">Silakan login untuk mengelola sistem.</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger py-2 rounded-pill small text-center border-0 bg-gradient-danger text-white shadow-sm mb-4">
                <i class="bi bi-exclamation-circle me-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3 input-group">
                <span class="input-group-text ps-3"><i class="bi bi-person"></i></span>
                <input type="text" name="username" class="form-control py-2" placeholder="Username" required autocomplete="off">
            </div>
            
            <div class="mb-4 input-group">
                <span class="input-group-text ps-3"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control py-2" placeholder="Password" required>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100 py-2 shadow-lg">
                MASUK SEKARANG
            </button>
        </form>

        <div class="text-center mt-4">
            <p class="text-muted small mb-2">Login with</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#" class="text-muted"><i class="bi bi-google fs-5"></i></a>
                <a href="#" class="text-muted"><i class="bi bi-facebook fs-5"></i></a>
                <a href="#" class="text-muted"><i class="bi bi-github fs-5"></i></a>
            </div>
        </div>
    </div>

</body>
</html>