<?php
require '../config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$form = null;
$form_id = 0;

if (isset($_GET['key'])) {
    $key = $_GET['key'];
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE public_key = ?");
    $stmt->execute([$key]);
    $form = $stmt->fetch();
    if ($form) {
        $form_id = $form['id'];
    }
} else {
    $form_id = (int) ($_GET['id'] ?? 0);
    if ($form_id) {
        $stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
        $stmt->execute([$form_id]);
        $form = $stmt->fetch();
    }
}

if (!$form) {
    echo "Form tidak ditemukan.";
    exit;
}

// Pertanyaan
$q = $pdo->prepare("SELECT * FROM questions WHERE form_id = ? ORDER BY id ASC");
$q->execute([$form_id]);
$questions = $q->fetchAll();

// Responses
$r = $pdo->prepare("SELECT * FROM responses WHERE form_id = ? ORDER BY id ASC");
$r->execute([$form_id]);
$responses = $r->fetchAll();

// Prepared statements
$ansStmt  = $pdo->prepare("SELECT answer FROM response_answers WHERE response_id = ? AND question_id = ?");
$fileStmt = $pdo->prepare("SELECT * FROM response_files WHERE response_id = ?");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Responses - <?= htmlspecialchars($form['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (untuk icon file) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background-color:#f1f3f4;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; }
        .page-wrapper { max-width:1100px;margin:32px auto; }
        .card-g { border-radius:16px;border:none; }
        .card-header-g {
            background:linear-gradient(120deg,#673ab7,#512da8);
            color:#fff;border-radius:16px 16px 0 0;padding:18px 24px;
        }
        .card-header-g h2 { margin:0;font-size:1.4rem; }
        .answer-label { font-weight:600; }
        .response-card { border-radius:16px;border:1px solid #dadce0; }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="card card-g shadow-sm">
        <div class="card-header-g d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Responses – <?= htmlspecialchars($form['title']) ?></h2>
                <small>Total response: <strong><?= count($responses) ?></strong></small>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-light btn-sm">&larr; Dashboard</a>
                <a href="view_form.php?key=<?= $form['public_key'] ?>" class="btn btn-outline-light btn-sm">Detail Form</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($responses)): ?>
                <div class="alert alert-info mb-0">Belum ada response.</div>
            <?php else: ?>
                <?php foreach ($responses as $i => $res): ?>
                    <div class="card response-card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                   <span class="badge bg-primary">Response #<?= $i + 1; ?></span>
                                </div>
                                <div class="text-muted small">
                                    Dikirim: <?= isset($res['created_at']) ? htmlspecialchars($res['created_at']) : '-' ?>
                                </div>
                            </div>

                            <div class="list-group list-group-flush mb-2">
                                <?php foreach ($questions as $qrow): ?>
                                    <?php
                                    $ansStmt->execute([$res['id'], $qrow['id']]);
                                    $answer = $ansStmt->fetchColumn() ?? '';
                                    ?>
                                    <div class="list-group-item px-0">
                                        <div class="answer-label">
                                            <?= htmlspecialchars($qrow['question']); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?= nl2br(htmlspecialchars($answer)); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <?php
                                $fileStmt->execute([$res['id']]);
                                $files = $fileStmt->fetchAll();
                                if ($files):
                                ?>
                                    <div class="list-group-item px-0">
                                        <div class="answer-label mb-2">Lampiran</div>

                                        <div class="d-flex flex-wrap gap-3">
                                            <?php foreach ($files as $f): 
                                                $fileUrl  = "../uploads/" . rawurlencode($f['filename']);
                                                $origName = htmlspecialchars($f['original_name']);
                                                $ext      = strtolower(pathinfo($f['filename'], PATHINFO_EXTENSION));
                                            ?>

                                                <?php if (in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                                                    <!-- Preview gambar -->
                                                    <div style="width:180px;">
                                                        <img src="<?= $fileUrl ?>"
                                                             alt="<?= $origName ?>"
                                                             class="img-thumbnail mb-1"
                                                             style="max-height:150px; object-fit:cover;">
                                                        <div class="small text-muted"><?= $origName ?></div>
                                                        <a href="<?= $fileUrl ?>" target="_blank"
                                                           class="btn btn-sm btn-outline-primary mt-1">Lihat</a>
                                                    </div>

                                                <?php elseif ($ext === 'pdf'): ?>
                                                    <!-- Preview PDF -->
                                                    <div style="width:180px;">
                                                        <embed src="<?= $fileUrl ?>"
                                                               type="application/pdf"
                                                               style="width:180px;height:150px;border:1px solid #ccc;border-radius:6px;">
                                                        <div class="small text-muted mt-1"><?= $origName ?></div>
                                                        <a href="<?= $fileUrl ?>" target="_blank"
                                                           class="btn btn-sm btn-outline-primary mt-1">Buka PDF</a>
                                                    </div>

                                                <?php else: ?>
                                                    <!-- File lain: Word, Excel, ZIP, dll -->
                                                    <div style="width:180px;">
                                                        <div class="border p-3 rounded bg-light text-center">
                                                            <i class="bi bi-file-earmark-text" style="font-size:32px;"></i>
                                                            <div class="small mt-1"><?= $origName ?></div>
                                                        </div>
                                                        <a href="<?= $fileUrl ?>" target="_blank"
                                                           class="btn btn-sm btn-outline-primary mt-1">Download</a>
                                                    </div>

                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
