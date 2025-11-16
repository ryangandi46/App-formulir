<?php
require '../config.php';
if (!isset($_SESSION['admin'])) header("Location: ../login.php");

// helper untuk buat public_key random
function generate_public_key(): string {
    return bin2hex(random_bytes(16)); // 32 karakter
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $desc  = $_POST['description'] ?? '';
    $allow = isset($_POST['allow_attachments']) ? 1 : 0;
    $public_key = generate_public_key();

    $stmt = $pdo->prepare("
        INSERT INTO forms (title, description, allow_attachments, public_key)
        VALUES (?,?,?,?)
    ");
    $stmt->execute([$title, $desc, $allow, $public_key]);

    $form_id = $pdo->lastInsertId();

    // Simpan pertanyaan
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

    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Form Baru</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color:#f1f3f4; font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; }
        .form-wrapper { max-width:900px; margin:32px auto; }
        .gform-card { border-radius:16px; border:none; }
        .gform-header {
            background:linear-gradient(120deg,#4285f4,#3367d6);
            color:#fff;border-radius:16px 16px 0 0;padding:24px 24px 16px;
        }
        .gform-header h1 { font-size:1.5rem;margin:0 0 8px; }
        .gform-header p { margin:0;opacity:.9; }
        .question-card { border-radius:16px;border:1px solid #dadce0; }
    </style>
    <script>
        function addQ() {
            let area = document.getElementById("q");
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
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Opsi (radio/checkbox/select — pisahkan per baris)</label>
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
        <div class="gform-header">
            <h1>Buat Form Baru</h1>
            <p>Atur judul, deskripsi, pertanyaan, dan upload dokumen/foto.</p>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Judul Form</label>
                    <input name="title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="allow_attachments" name="allow_attachments">
                    <label class="form-check-label" for="allow_attachments">
                        Izinkan responden meng-upload dokumen / foto
                    </label>
                </div>

                <h5 class="mt-2 mb-3">Pertanyaan</h5>

                <div id="q">
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
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Opsi (radio/checkbox/select — pisahkan per baris)</label>
                                <textarea name="options[]" class="form-control" rows="2"
                                          placeholder="opsi1&#10;opsi2&#10;opsi3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" onclick="addQ()" class="btn btn-outline-primary">
                        + Tambah Pertanyaan
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Simpan Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
