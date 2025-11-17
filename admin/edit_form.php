<?php
require '../config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil form berdasarkan key atau id
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

// Saat submit (update form + pertanyaan)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $desc  = $_POST['description'] ?? '';

    // Cek apakah ada pertanyaan bertipe "file"
    $allow = 0;
    if (!empty($_POST['type'])) {
        foreach ($_POST['type'] as $t) {
            if ($t === 'file') {
                $allow = 1;
                break;
            }
        }
    }

    // Update forms
    $stmt = $pdo->prepare("
        UPDATE forms 
        SET title = ?, description = ?, allow_attachments = ?
        WHERE id = ?
    ");
    $stmt->execute([$title, $desc, $allow, $form_id]);

    // Hapus semua pertanyaan lama lalu insert ulang (lebih sederhana)
    $pdo->prepare("DELETE FROM questions WHERE form_id = ?")->execute([$form_id]);

    if (!empty($_POST['question'])) {
        foreach ($_POST['question'] as $i => $q) {
            $q = trim($q ?? '');
            if ($q === '') continue;

            $type    = $_POST['type'][$i] ?? 'text';
            $options = $_POST['options'][$i] ?? '';

            $pdo->prepare("
                INSERT INTO questions (form_id, question, type, options)
                VALUES (?,?,?,?)
            ")->execute([$form_id, $q, $type, $options]);
        }
    }

    header("Location: view_form.php?key=" . urlencode($form['public_key']));
    exit;
}

// Ambil pertanyaan untuk tampilan form edit
$q = $pdo->prepare("SELECT * FROM questions WHERE form_id = ? ORDER BY id ASC");
$q->execute([$form_id]);
$questions = $q->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Form - <?= htmlspecialchars($form['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color:#f1f3f4; font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; }
        .form-wrapper { max-width:900px; margin:32px auto; }
        .gform-card { border-radius:16px; border:none; }
        .gform-header {
            background:linear-gradient(120deg,#1a73e8,#185abc);
            color:#fff;border-radius:16px 16px 0 0;padding:20px 24px 16px;
        }
        .gform-header h1 { font-size:1.5rem;margin:0 0 6px; }
        .gform-header p { margin:0;opacity:.9; }
        .question-card { border-radius:16px;border:1px solid #dadce0; }
    </style>
    <script>
        function addQ() {
            let area = document.getElementById("q-list");
            area.insertAdjacentHTML('beforeend', `
                <div class="card question-card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Pertanyaan</label>
                            <input name="question[]" class="form-control" placeholder="Tulis pertanyaan">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Input</label>
                            <select name="type[]" class="form-select">
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="radio">Radio</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="select">Select</option>
                                <option value="file">Upload File (Foto/Dokumen)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                Opsi (radio/checkbox/select — pisahkan per baris)
                                <span class="text-muted small d-block">
                                    Kosongkan untuk tipe selain radio/checkbox/select
                                </span>
                            </label>
                            <textarea name="options[]" class="form-control" rows="2"
                                      placeholder="opsi1&#10;opsi2&#10;opsi3"></textarea>
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
        <div class="gform-header d-flex justify-content-between align-items-center">
            <div>
                <h1>Edit Form</h1>
                <p>Edit judul, deskripsi, dan pertanyaan (termasuk upload file).</p>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-light btn-sm">&larr; Dashboard</a>
                <a href="view_form.php?key=<?= urlencode($form['public_key']) ?>" class="btn btn-outline-light btn-sm">
                    Lihat Detail
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Judul Form</label>
                    <input name="title" class="form-control" required
                           value="<?= htmlspecialchars($form['title']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($form['description']) ?></textarea>
                </div>

                <h5 class="mt-2 mb-3">Pertanyaan</h5>

                <div id="q-list">
                    <?php if (!empty($questions)): ?>
                        <?php foreach ($questions as $qrow): ?>
                            <div class="card question-card shadow-sm mb-3">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Pertanyaan</label>
                                        <input name="question[]" class="form-control"
                                               value="<?= htmlspecialchars($qrow['question']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tipe Input</label>
                                        <select name="type[]" class="form-select">
                                            <?php
                                            $types = [
                                                'text'     => 'Text',
                                                'textarea' => 'Textarea',
                                                'number'   => 'Number',
                                                'date'     => 'Date',
                                                'radio'    => 'Radio',
                                                'checkbox' => 'Checkbox',
                                                'select'   => 'Select',
                                                'file'     => 'Upload File (Foto/Dokumen)',
                                            ];
                                            foreach ($types as $val => $label):
                                            ?>
                                                <option value="<?= $val ?>"
                                                    <?= $qrow['type'] === $val ? 'selected' : '' ?>>
                                                    <?= $label ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Opsi (radio/checkbox/select — pisahkan per baris)
                                            <span class="text-muted small d-block">
                                                Kosongkan untuk tipe selain radio/checkbox/select
                                            </span>
                                        </label>
                                        <textarea name="options[]" class="form-control" rows="2"
                                                  placeholder="opsi1&#10;opsi2&#10;opsi3"><?= htmlspecialchars($qrow['options']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Jika belum ada pertanyaan sama sekali -->
                        <div class="card question-card shadow-sm mb-3">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Pertanyaan</label>
                                    <input name="question[]" class="form-control" placeholder="Pertanyaan 1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tipe Input</label>
                                    <select name="type[]" class="form-select">
                                        <option value="text">Text</option>
                                        <option value="textarea">Textarea</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                        <option value="radio">Radio</option>
                                        <option value="checkbox">Checkbox</option>
                                        <option value="select">Select</option>
                                        <option value="file">Upload File (Foto/Dokumen)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">
                                        Opsi (radio/checkbox/select — pisahkan per baris)
                                        <span class="text-muted small d-block">
                                            Kosongkan untuk tipe selain radio/checkbox/select
                                        </span>
                                    </label>
                                    <textarea name="options[]" class="form-control" rows="2"
                                              placeholder="opsi1&#10;opsi2&#10;opsi3"></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" onclick="addQ()" class="btn btn-outline-primary">
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
