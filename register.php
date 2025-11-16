<?php
require 'config.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($user === '' || $pass === '') {
        $error = "Username dan password wajib diisi.";
    } else {
        // Cek apakah username sudah dipakai
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=?");
        $check->execute([$user]);
        if ($check->fetchColumn() > 0) {
            $error = "Username sudah digunakan, silakan pilih yang lain.";
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?,?)");
            $stmt->execute([$user, $hash]);

            $success = "Admin berhasil dibuat. Silakan login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #fbbc05, #ea4335);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .auth-card {
            max-width: 420px;
            width: 100%;
            border-radius: 16px;
            border: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        }
        .auth-header {
            border-radius: 16px 16px 0 0;
            background: #fff;
            padding: 20px 24px 12px;
            border-bottom: 1px solid #eee;
        }
        .auth-header h3 {
            margin: 0;
        }
        .auth-body {
            padding: 20px 24px 24px;
        }
    </style>
</head>
<body>
<div class="card auth-card">
    <div class="auth-header">
        <h3 class="mb-1">Register Admin</h3>
        <small class="text-muted">Buat akun admin pertama untuk mengelola aplikasi.</small>
    </div>
    <div class="auth-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success py-2">
                <?= htmlspecialchars($success) ?>
                <br>
                <a href="login.php" class="alert-link">Ke halaman login</a>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input
                    type="text"
                    name="username"
                    class="form-control"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Register
            </button>

            <div class="text-center mt-3">
                <small class="text-muted">
                    Sudah punya akun? <a href="login.php">Login</a>
                </small>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
