<?php
session_start();

// Kalau sudah login, langsung lempar ke dashboard
if (isset($_SESSION['status_login']) && $_SESSION['status_login'] == true) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek hardcode admin & admin
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['status_login'] = true;
        $_SESSION['user'] = 'Admin Lab IoT';
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Validasi Peminjaman</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background-color:#f6f7fb;
            min-height:100vh;
        }
        .login-wrap{
            min-height:100vh;
        }
        .login-card{
            border-radius:16px;
            border:1px solid rgba(16,24,40,.08);
            box-shadow:0 10px 25px rgba(16,24,40,.05);
            background:rgba(255,255,255,.95);
            backdrop-filter: blur(6px);
        }
        .login-title{
            font-weight:900;
            letter-spacing:-.02em;
        }
        .btn-login{
            background-color:#198754;
            border-color:#198754;
            border-radius:12px;
            font-weight:900;
        }
        .btn-login:hover{
            background-color:#157347;
            border-color:#157347;
        }
    </style>
</head>
<body>
    <div class="container login-wrap">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-md-5 col-lg-4">
                <div class="login-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background:rgba(25,135,84,.08); border:1px solid rgba(25,135,84,.15);">
                            <span style="font-size:18px;">🧪</span>
                            <h2 class="h4 m-0 login-title">Sistem Lab IoT</h2>
                        </div>
                        <div class="text-muted mt-2" style="font-size:13px;">🔐 Validasi Peminjaman Berbasis RFID</div>
                    </div>

                    <?php if($error != ""): ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Username</label>
                            <input type="text" class="form-control" name="username" required autocomplete="off" />
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" class="form-control" name="password" required />
                        </div>

                        <button type="submit" name="login" class="btn btn-login w-100 py-2">
                            Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

