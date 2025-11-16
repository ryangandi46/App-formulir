<?php
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Metode tidak diizinkan.");
}

$form_id = (int) ($_POST['form_id'] ?? 0);
if (!$form_id) die("Form tidak ditemukan.");

// Ambil form (untuk cek dan ambil public_key)
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$form_id]);
$form = $stmt->fetch();
if (!$form) die("Form tidak ditemukan.");

$allow_attachments = !empty($form['allow_attachments']);
$public_key        = $form['public_key'];

// Ambil semua pertanyaan form ini
$q = $pdo->prepare("SELECT * FROM questions WHERE form_id = ? ORDER BY id ASC");
$q->execute([$form_id]);
$questions = $q->fetchAll();

// Simpan ke tabel responses
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO responses (form_id, created_at) VALUES (?, NOW())");
    $stmt->execute([$form_id]);
    $response_id = $pdo->lastInsertId();

    // Simpan jawaban per pertanyaan
    $ansStmt = $pdo->prepare("
        INSERT INTO response_answers (response_id, question_id, answer)
        VALUES (?,?,?)
    ");

    foreach ($questions as $qrow) {
        $fieldName = "question_" . $qrow['id'];

        if (!isset($_POST[$fieldName])) {
            $answer = '';
        } else {
            $value = $_POST[$fieldName];
            if (is_array($value)) {
                $answer = implode(", ", $value);
            } else {
                $answer = trim($value);
            }
        }
        $ansStmt->execute([$response_id, $qrow['id'], $answer]);
    }

    // Simpan file lampiran (jika diizinkan & ada file)
    if ($allow_attachments && !empty($_FILES['attachments']['name'][0])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $fileStmt = $pdo->prepare("
            INSERT INTO response_files (response_id, filename, original_name, size, created_at)
            VALUES (?,?,?,?, NOW())
        ");

        foreach ($_FILES['attachments']['name'] as $i => $origName) {
            if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $tmpName = $_FILES['attachments']['tmp_name'][$i];
            $size    = $_FILES['attachments']['size'][$i];

            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            $newName = uniqid('file_', true) . ($ext ? '.'.$ext : '');
            $dest = $uploadDir . $newName;

            if (move_uploaded_file($tmpName, $dest)) {
                $fileStmt->execute([
                    $response_id,
                    $newName,
                    $origName,
                    $size
                ]);
            }
        }
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Terjadi kesalahan saat menyimpan data: " . $e->getMessage());
}

// Bangun URL form publik untuk tombol "Kembali ke Form"
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . "/App_Form";
$formUrl = $baseUrl . "/f/" . $public_key;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Terima Kasih - <?= htmlspecialchars($form['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color:#f1f3f4;
            font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .thank-card {
            max-width:480px;
            width:100%;
            border-radius:16px;
            border:none;
            box-shadow:0 8px 24px rgba(0,0,0,0.12);
        }
        .thank-header {
            border-radius:16px 16px 0 0;
            background:linear-gradient(120deg,#34a853,#0f9d58);
            color:#fff;
            padding:20px 24px 14px;
        }
        .thank-header h1 {
            font-size:1.6rem;
            margin:0 0 6px;
        }
        .thank-body {
            padding:20px 24px 24px;
        }
    </style>
</head>
<body>
<div class="card thank-card">
    <div class="thank-header">
        <h1>Terima kasih!</h1>
        <p class="mb-0 small">
            Respons kamu untuk form <strong><?= htmlspecialchars($form['title']); ?></strong> sudah tersimpan.
        </p>
    </div>
    <div class="thank-body">
        <p class="mb-3">
            Data yang kamu kirim akan diproses oleh pihak terkait.  
            Jika diperlukan, kamu bisa mengisi form ini kembali atau menutup halaman ini.
        </p>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
            <a href="<?= htmlspecialchars($formUrl); ?>" class="btn btn-outline-secondary">
                Kembali ke Form
            </a>
            <button class="btn btn-primary" onclick="window.close();">
                Tutup Halaman
            </button>
        </div>

        <p class="text-muted mt-3 mb-0 small">
            Jika tombol <strong>Tutup Halaman</strong> tidak berfungsi (tergantung browser),
            kamu bisa menutup tab ini secara manual.
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
