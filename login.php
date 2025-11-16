<?php
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$user]);
    $u = $stmt->fetch();

    if ($u && password_verify($pass, $u['password'])) {
        $_SESSION['admin'] = $u['id'];
        header("Location: admin/dashboard.php");
        exit;
    }
    $error = "Username atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #4285f4, #34a853);
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
        <h3 class="mb-1">Login Admin</h3>
        <small class="text-muted">Masuk untuk mengelola form & responses.</small>
    </div>
    <div class="auth-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input
                    type="text"
                    name="username"
                    class="form-control"
                    required
                    autofocus
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
                Login
            </button>

            <div class="text-center mt-3">
                <small class="text-muted">
                    Belum punya akun? <a href="register.php">Register admin</a>
                </small>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
