<?php
require '../config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header("Location: dashboard.php");
    exit;
}

// Hapus form + cascading ke tabel lain
// Pastikan di database sudah ada foreign key dengan ON DELETE CASCADE
// untuk questions, responses, response_answers, response_files jika dipakai.
$stmt = $pdo->prepare("DELETE FROM forms WHERE id = ?");
$stmt->execute([$id]);

header("Location: dashboard.php");
exit;
