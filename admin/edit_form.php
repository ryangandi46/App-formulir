<?php
require '../config.php';
if (!isset($_SESSION['admin'])) header("Location: ../login.php");

$form = null;
$id   = 0;

if (isset($_GET['key'])) {
    $key = $_GET['key'];
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE public_key = ?");
    $stmt->execute([$key]);
    $form = $stmt->fetch();
    if ($form) {
        $id = $form['id'];
    }
} else {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
        $stmt->execute([$id]);
        $form = $stmt->fetch();
    }
}

if (!$form) {
    echo "Form tidak ditemukan.";
    exit;
}

// Ambil pertanyaan
$q = $pdo->prepare("SELECT * FROM questions WHERE form_id=? ORDER BY id");
$q->execute([$id]);
$questions = $q->fetchAll();

// Update
if ($_POST) {
    $title = $_POST['title'];
    $desc  = $_POST['description'];
    $allow = isset($_POST['allow_attachments']) ? 1 : 0;

    $pdo->prepare("UPDATE forms SET title=?, description=?, allow_attachments=? WHERE id=?")
        ->execute([$title, $desc, $allow, $id]);

    // Update existing questions
    foreach ($_POST['q_id'] as $i => $qid) {
        $question = $_POST['question'][$i];
        $type     = $_POST['type'][$i];
        $options  = $_POST['options'][$i] ?? '';

        $pdo->prepare("UPDATE questions SET question=?, type=?, options=? WHERE id=?")
            ->execute([$question, $type, $options, $qid]);
    }

    // Tambah pertanyaan baru jika ada
    if (!empty($_POST['new_question'])) {
        foreach ($_POST['new_question'] as $idx => $qtext) {
            if (!empty($qtext)) {
                $pdo->prepare("INSERT INTO questions (form_id, question, type, options) VALUES (?,?,?,?)")
                    ->execute([$id, $qtext, $_POST['new_type'][$idx], $_POST['new_options'][$idx]]);
            }
        }
    }

    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f1f3f4;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .form-wrapper { max-width: 900px; margin: 32px auto; }
        .gform-card { border-radius: 16px; border: none; }
        .gform-header {
            background: linear-gradient(120deg, #34a853, #0b8043);
            color: #fff;
            border-radius: 16px 16px 0 0;
            padding: 24px 24px 16px;
        }
        .gform-header h1 { font-size: 1.5rem; margin: 0 0 8px; }
        .gform-header p { margin: 0; opacity: .9; }
        .question-card { border-radius: 16px; border: 1px solid #dadce0; }
    </style>

    <script>
        function addNewQ() {
            let c = document.getElementById("new_questions");
            c.insertAdjacentHTML('beforeend', `
                <div class="card question-card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Pertanyaan Baru</label>
                            <input name="new_question[]" class="form-control" placeholder="Tulis pertanyaan">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe Input</label>
                            <select name="new_type[]" class="form-select">
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="radio">Radio</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="select">Select</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Opsi (jika radio/checkbox/select) — pisahkan baris baru
                            </label>
                            <textarea name="new_options[]" class="form-control" rows="2"
                                      placeholder="opsi1&#10;opsi2"></textarea>
                        </div>
                    </div>
                </div>
            `);
        }
    </script>
</head>
<body>
<div class="form-wrapper">
    <div class="card gform-card shadow-sm mb-3">
        <div class="gform-header">
            <h1>Edit Form</h1>
            <p>Ubah judul, deskripsi, pertanyaan, dan pengaturan upload dokumen/foto.</p>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Judul Form</label>
                    <input name="title" class="form-control"
                           value="<?= htmlspecialchars($form['title']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($form['description']) ?></textarea>
                </div>

                <!-- Checkbox aktifkan upload file -->
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="allow_attachments" name="allow_attachments"
                           <?= $form['allow_attachments'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="allow_attachments">
                        Izinkan responden meng-upload dokumen / foto pada form ini
                    </label>
                </div>

                <h5 class="mt-2 mb-3">Pertanyaan</h5>

                <?php foreach ($questions as $q): ?>
                    <div class="card question-card shadow-sm mb-3">
                        <div class="card-body">
                            <input type="hidden" name="q_id[]" value="<?= $q['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label">Pertanyaan</label>
                                <input name="question[]" class="form-control"
                                       value="<?= htmlspecialchars($q['question']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tipe Input</label>
                                <select name="type[]" class="form-select">
                                    <option value="text"     <?= $q['type']=='text'     ?'selected':'' ?>>Text</option>
                                    <option value="textarea" <?= $q['type']=='textarea'?'selected':'' ?>>Textarea</option>
                                    <option value="number"   <?= $q['type']=='number'  ?'selected':'' ?>>Number</option>
                                    <option value="date"     <?= $q['type']=='date'    ?'selected':'' ?>>Date</option>
                                    <option value="radio"    <?= $q['type']=='radio'   ?'selected':'' ?>>Radio</option>
                                    <option value="checkbox" <?= $q['type']=='checkbox'?'selected':'' ?>>Checkbox</option>
                                    <option value="select"   <?= $q['type']=='select'  ?'selected':'' ?>>Select</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Opsi (jika radio/checkbox/select)
                                </label>
                                <textarea name="options[]" class="form-control" rows="2"><?= $q['options'] ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <h5 class="mt-4 mb-3">Tambah Pertanyaan Baru</h5>
                <div id="new_questions"></div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" onclick="addNewQ()" class="btn btn-outline-primary">
                        + Tambah Pertanyaan
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
