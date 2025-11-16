<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?,?)");
    $stmt->execute([$user, $pass]);

    echo "Admin berhasil dibuat. <a href='login.php'>Login</a>";
    exit;
}
?>
<form method="POST">
  Username: <input name="username"><br>
  Password: <input type="password" name="password"><br>
  <button>Register</button>
</form>

