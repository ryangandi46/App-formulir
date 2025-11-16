<?php
require __DIR__ . '/../../config.php';

$key = $_GET['key'] ?? '';
if (!$key) die("Form tidak ditemukan.");

// Cari form berdasarkan public_key
$stmt = $pdo->prepare("SELECT * FROM forms WHERE public_key = ?");
$stmt->execute([$key]);
$form = $stmt->fetch();

if (!$form) die("Form tidak ditemukan.");

$form_id = $form['id'];

// Ambil pertanyaan
$q = $pdo->prepare("SELECT * FROM questions WHERE form_id = ? ORDER BY id ASC");
$q->execute([$form_id]);
$questions = $q->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($form['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color:#f1f3f4;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; }
        .form-wrapper { max-width:720px;margin:32px auto; }
        .gform-card { border-radius:16px;border:none; }
        .gform-header {
            background:linear-gradient(120deg,#4285f4,#3367d6);
            color:#fff;border-radius:16px 16px 0 0;padding:24px 24px 16px;
        }
        .gform-header h1 { font-size:1.5rem;margin:0 0 8px; }
        .gform-header p { margin:0;opacity:.9; }
        .question-card { border-radius:16px;border:1px solid #dadce0; }
        .question-label { font-weight:600;margin-bottom:8px; }
        .option-label { font-weight:400; }
    </style>
</head>
<body>
<div class="form-wrapper">
    <div class="card gform-card shadow-sm mb-3">
        <div class="gform-header">
            <h1><?= htmlspecialchars($form['title']) ?></h1>
            <?php if (!empty($form['description'])): ?>
                <p><?= nl2br(htmlspecialchars($form['description'])) ?></p>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <p class="mb-0 text-muted">Isi form berikut sesuai instruksi.</p>
        </div>
    </div>

    <form action="/App_Form/public/submit.php" method="POST" enctype="multipart/form-data" class="mb-4">
        <input type="hidden" name="form_id" value="<?= $form_id ?>">

        <?php foreach ($questions as $qrow): ?>
            <?php
            $type    = $qrow['type'];
            $name    = "question_" . $qrow['id'];
            $options = !empty($qrow['options']) ? explode("\n", trim($qrow['options'])) : [];
            ?>
            <div class="card question-card shadow-sm mb-3">
                <div class="card-body">
                    <label class="question-label d-block mb-2">
                        <?= htmlspecialchars($qrow['question']) ?>
                    </label>

                    <?php if ($type === 'text'): ?>
                        <input type="text" name="<?= $name ?>" class="form-control">

                    <?php elseif ($type === 'textarea'): ?>
                        <textarea name="<?= $name ?>" class="form-control" rows="3"></textarea>

                    <?php elseif ($type === 'number'): ?>
                        <input type="number" name="<?= $name ?>" class="form-control">

                    <?php elseif ($type === 'date'): ?>
                        <input type="date" name="<?= $name ?>" class="form-control">

                    <?php elseif ($type === 'radio'): ?>
                        <?php foreach ($options as $opt): ?>
                            <?php
                            $opt = trim($opt);
                            if ($opt === '') continue;
                            $idRadio = $name . md5($opt);
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="<?= $name ?>" id="<?= $idRadio ?>"
                                       value="<?= htmlspecialchars($opt) ?>">
                                <label class="form-check-label option-label" for="<?= $idRadio ?>">
                                    <?= htmlspecialchars($opt) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>

                    <?php elseif ($type === 'checkbox'): ?>
                        <?php foreach ($options as $opt): ?>
                            <?php
                            $opt = trim($opt);
                            if ($opt === '') continue;
                            $idCb = $name . md5($opt);
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="<?= $name ?>[]" id="<?= $idCb ?>"
                                       value="<?= htmlspecialchars($opt) ?>">
                                <label class="form-check-label option-label" for="<?= $idCb ?>">
                                    <?= htmlspecialchars($opt) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>

                    <?php elseif ($type === 'select'): ?>
                        <select name="<?= $name ?>" class="form-select">
                            <?php foreach ($options as $opt): ?>
                                <?php
                                $opt = trim($opt);
                                if ($opt === '') continue;
                                ?>
                                <option value="<?= htmlspecialchars($opt) ?>">
                                    <?= htmlspecialchars($opt) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    <?php else: ?>
                        <input type="text" name="<?= $name ?>" class="form-control">
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (!empty($form['allow_attachments'])): ?>
            <div class="card question-card shadow-sm mb-3">
                <div class="card-body">
                    <label class="question-label d-block mb-2">
                        Upload File (Foto / Dokumen)
                    </label>
                    <input type="file" name="attachments[]" class="form-control" multiple
                           accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx">
                    <div class="form-text">
                        Opsional — unggah foto (JPG/PNG) atau dokumen (PDF, Word, Excel, dll).
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4">Kirim</button>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
