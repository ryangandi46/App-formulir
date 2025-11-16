<?php
require '../config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$form_id = $_GET['id'] ?? 0;
if (!$form_id) {
    header("Location: dashboard.php");
    exit;
}

// Ambil data form
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$form_id]);
$form = $stmt->fetch();

if (!$form) {
    echo "Form tidak ditemukan.";
    exit;
}

// Ambil pertanyaan
$q = $pdo->prepare("SELECT * FROM questions WHERE form_id = ? ORDER BY id ASC");
$q->execute([$form_id]);
$questions = $q->fetchAll();

// Base URL untuk link publik
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . "/App_Form";   // sesuaikan nama folder kalau beda
$publicLink = $baseUrl . "/f/" . $form_id;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Form - <?= htmlspecialchars($form['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f1f3f4;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .page-wrapper {
            max-width: 900px;
            margin: 32px auto;
        }
        .card-g {
            border-radius: 16px;
            border: none;
        }
        .card-header-g {
            background: linear-gradient(120deg, #1a73e8, #185abc);
            color: #fff;
            border-radius: 16px 16px 0 0;
            padding: 18px 24px;
        }
        .card-header-g h2 {
            margin: 0;
            font-size: 1.4rem;
        }
        .question-card {
            border-radius: 16px;
            border: 1px solid #dadce0;
        }
        .q-type-badge {
            font-size: 0.7rem;
        }
        .options-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 0.85rem;
        }
        .link-input {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="card card-g shadow-sm">
        <div class="card-header-g d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Detail Form</h2>
                <small><?= htmlspecialchars($form['title']) ?></small>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-light btn-sm">
                    &larr; Kembali
                </a>
                <a href="view_response.php?id=<?= $form_id ?>" class="btn btn-outline-light btn-sm">
                    Responses
                </a>
                <a href="edit_form.php?id=<?= $form_id ?>" class="btn btn-outline-light btn-sm">
                    Edit Form
                </a>
            </div>
        </div>

        <div class="card-body">

            <!-- Info Form -->
            <div class="mb-4">
                <h5 class="mb-1"><?= htmlspecialchars($form['title']) ?></h5>
                <?php if (!empty($form['description'])): ?>
                    <p class="mb-2 text-muted">
                        <?= nl2br(htmlspecialchars($form['description'])) ?>
                    </p>
                <?php endif; ?>

                <div class="mb-2">
                    <span class="fw-semibold">Upload dokumen/foto:</span>
                    <?php if (!empty($form['allow_attachments'])): ?>
                        <span class="badge bg-success ms-1">Aktif</span>
                    <?php else: ?>
                        <span class="badge bg-secondary ms-1">Nonaktif</span>
                    <?php endif; ?>
                </div>

                <div class="mb-2">
                    <label class="form-label mb-1 small fw-semibold">Link Publik</label>
                    <div class="input-group input-group-sm" style="max-width: 100%;">
                        <input id="publicLink" type="text" class="form-control link-input" value="<?= $publicLink ?>" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="copyPublicLink()">
                            Salin
                        </button>
                        <a href="<?= $publicLink ?>" target="_blank" class="btn btn-outline-success">
                            Buka Form
                        </a>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Daftar Pertanyaan -->
            <h6 class="mb-3">Daftar Pertanyaan</h6>

            <?php if (empty($questions)): ?>
                <div class="alert alert-info">
                    Belum ada pertanyaan pada form ini.
                </div>
            <?php else: ?>
                <?php foreach ($questions as $index => $qrow): ?>
                    <div class="card question-card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-1">
                                <div class="fw-semibold">
                                    <?= ($index + 1) . '. ' . htmlspecialchars($qrow['question']); ?>
                                </div>
                                <div>
                                    <span class="badge bg-primary q-type-badge text-uppercase">
                                        <?= htmlspecialchars($qrow['type']); ?>
                                    </span>
                                </div>
                            </div>

                            <?php if (!empty(trim($qrow['options']))): ?>
                                <div class="mt-2">
                                    <div class="small text-muted mb-1">Opsi:</div>
                                    <div class="options-box">
                                        <?php
                                        $opsi = explode("\n", trim($qrow['options']));
                                        foreach ($opsi as $opt) {
                                            $opt = trim($opt);
                                            if ($opt === '') continue;
                                            echo '&#8226; ' . htmlspecialchars($opt) . '<br>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function copyPublicLink() {
        let input = document.getElementById("publicLink");
        input.select();
        input.setSelectionRange(0, 99999);

        navigator.clipboard.writeText(input.value)
            .then(() => alert("Link publik disalin!"))
            .catch(() => alert("Gagal menyalin link"));
    }
</script>
</body>
</html>
