<?php
require '../config.php';

// Pastikan form_id dikirim
$form_id = $_POST['form_id'] ?? 0;
if (!$form_id) {
    die("Form tidak valid.");
}

// Ambil semua pertanyaan untuk form ini
$stmt = $pdo->prepare("SELECT id, type FROM questions WHERE form_id = ? ORDER BY id ASC");
$stmt->execute([$form_id]);
$questions = $stmt->fetchAll();

// 1. Simpan ke tabel responses
$insertResponse = $pdo->prepare("INSERT INTO responses (form_id) VALUES (?)");
$insertResponse->execute([$form_id]);
$response_id = $pdo->lastInsertId();

// 2. Simpan jawaban ke response_answers
$insertAnswer = $pdo->prepare("
    INSERT INTO response_answers (response_id, question_id, answer)
    VALUES (?, ?, ?)
");

foreach ($questions as $q) {
    $fieldName = 'question_' . $q['id'];

    if ($q['type'] === 'checkbox') {
        // Checkbox berupa array
        $value = isset($_POST[$fieldName])
            ? implode(', ', (array)$_POST[$fieldName])
            : '';
    } else {
        $value = $_POST[$fieldName] ?? '';
    }

    $insertAnswer->execute([
        $response_id,
        $q['id'],
        $value
    ]);
}

// 3. Proses upload file (attachments)
if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {

    $uploadDir = __DIR__ . '/../uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $insertFile = $pdo->prepare("
        INSERT INTO response_files (response_id, filename, original_name, mime_type, size)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($_FILES['attachments']['name'] as $idx => $originalName) {
        $error = $_FILES['attachments']['error'][$idx];

        if ($error !== UPLOAD_ERR_OK) {
            // Lewati file yang gagal upload
            continue;
        }

        $tmpName = $_FILES['attachments']['tmp_name'][$idx];
        $mime    = $_FILES['attachments']['type'][$idx];
        $size    = $_FILES['attachments']['size'][$idx];

        // Amankan nama file
        $safeOriginal = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);
        $newName      = uniqid('file_', true) . '_' . $safeOriginal;
        $targetPath   = $uploadDir . $newName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $insertFile->execute([
                $response_id,
                $newName,
                $originalName,
                $mime,
                $size
            ]);
        }
    }
}

// 4. Tampilkan halaman terima kasih sederhana
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terima Kasih</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-3">Terima kasih!</h3>
                    <p class="mb-4">Jawaban dan file yang Anda kirim sudah tersimpan.</p>
                    <a href="form.php?id=<?= htmlspecialchars($form_id) ?>" class="btn btn-primary">
                        Kembali ke Form
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
