<?php
require '../config.php';
if (!isset($_SESSION['admin'])) header("Location: ../login.php");

$id = $_GET['id'];

// Pertanyaan
$questions = $pdo->prepare("SELECT * FROM questions WHERE form_id=?");
$questions->execute([$id]);
$q_list = $questions->fetchAll();

// Responses
$responses = $pdo->prepare("SELECT * FROM responses WHERE form_id=? ORDER BY id DESC");
$responses->execute([$id]);
$r_list = $responses->fetchAll();

// Siapkan query untuk ambil jawaban & file
$ansStmt = $pdo->prepare("SELECT answer FROM response_answers WHERE response_id=? AND question_id=?");
$fileStmt = $pdo->prepare("SELECT * FROM response_files WHERE response_id=?");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Responses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Responses Form #<?= htmlspecialchars($id) ?></h2>

    <?php if (empty($r_list)): ?>
        <div class="alert alert-info">Belum ada response.</div>
    <?php endif; ?>

    <?php foreach ($r_list as $r): ?>
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-2">
                    Response #<?= $r['id']; ?>
                </h5>
                <h6 class="card-subtitle mb-3 text-muted">
                    Dikirim pada: <?= $r['created_at']; ?>
                </h6>

                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($q_list as $q): ?>
                        <?php
                        $ansStmt->execute([$r['id'], $q['id']]);
                        $a = $ansStmt->fetchColumn();
                        ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($q['question']); ?>:</strong>
                            <br>
                            <?= nl2br(htmlspecialchars($a)); ?>
                        </li>
                    <?php endforeach; ?>

                    <?php
                    // Lampiran file
                    $fileStmt->execute([$r['id']]);
                    $files = $fileStmt->fetchAll();
                    if ($files):
                    ?>
                        <li class="list-group-item">
                            <strong>Lampiran:</strong>
                            <ul class="mt-2">
                                <?php foreach ($files as $f): ?>
                                    <li>
                                        <a href="../uploads/<?= rawurlencode($f['filename']); ?>"
                                           target="_blank">
                                            <?= htmlspecialchars($f['original_name']); ?>
                                        </a>
                                        (<?= round($f['size'] / 1024, 1); ?> KB)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
