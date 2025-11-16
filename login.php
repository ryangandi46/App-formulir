<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$user]);
    $u = $stmt->fetch();

    if ($u && password_verify($pass, $u['password'])) {
        $_SESSION['admin'] = $u['id'];
        header("Location: admin/dashboard.php");
        exit;
    }
    echo "Login gagal";
}
?>
<form method="POST">
  Username: <input name="username"><br>
  Password: <input name="password" type="password"><br>
  <button>Login</button>
</form>
