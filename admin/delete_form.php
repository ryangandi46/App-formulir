<?php
require '../config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$id = 0;
if (isset($_GET['key'])) {
    $key = $_GET['key'];
    $stmt = $pdo->prepare("SELECT id FROM forms WHERE public_key = ?");
    $stmt->execute([$key]);
    $id = (int) $stmt->fetchColumn();
} else {
    $id = (int) ($_GET['id'] ?? 0);
}

if ($id) {
    // Pastikan foreign key ON DELETE CASCADE sudah diset untuk tables terkait
    $stmt = $pdo->prepare("DELETE FROM forms WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: dashboard.php");
exit;
