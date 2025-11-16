<?php
require '../config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$forms = $pdo->query("SELECT * FROM forms ORDER BY id DESC")->fetchAll();

// Build base URL
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . "/App_Form"; // sesuaikan kalau nama folder beda
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Dashboard Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f1f3f4;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .page-wrapper {
            max-width: 1100px;
            margin: 32px auto;
        }
        .card-g {
            border-radius: 16px;
            border: none;
        }
        .card-header-g {
            background: linear-gradient(120deg, #4285f4, #3367d6);
            color: #fff;
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
        }
        .card-header-g h2 {
            margin: 0;
            font-size: 1.4rem;
        }
        .link-input {
            font-size: 0.8rem;
        }
        .badge-upload {
            font-size: .7rem;
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="card card-g shadow-sm">
        <div class="card-header-g d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Dashboard Admin</h2>
                <small>Kelola form, lihat link publik, QR, detail, dan responses.</small>
            </div>
            <div class="d-flex gap-2">
                <a href="create_form.php" class="btn btn-light btn-sm">
                    + Buat Form Baru
                </a>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">
                    Logout
                </a>
            </div>
        </div>
        <div class="card-body">

            <?php if (empty($forms)): ?>
                <div class="alert alert-info mb-0">
                    Belum ada form. Klik <strong>+ Buat Form Baru</strong> untuk membuat form pertama.
                </div>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 28%;">Nama Form</th>
                            <th style="width: 30%;">Link Publik</th>
                            <th style="width: 12%;">Upload File</th>
                            <th style="width: 30%;">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($forms as $f):
                            $id         = $f['id'];
                            $publicLink = $baseUrl . "/f/" . $id;
                            // $qrSrc      = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=" . urlencode($publicLink);
                            $qrSrc = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($publicLink);
                        ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($f['title']) ?>
                                    </div>
                                    <?php if (!empty($f['description'])): ?>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars(mb_strimwidth($f['description'], 0, 70, '...')) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="input-group input-group-sm">
                                        <input
                                            id="link<?= $id ?>"
                                            type="text"
                                            class="form-control link-input"
                                            value="<?= $publicLink ?>"
                                            readonly
                                        >
                                        <button class="btn btn-outline-primary" type="button"
                                                onclick="copyLink('<?= $id ?>')">
                                            Salin
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button"
                                                onclick="showQR('<?= $qrSrc ?>', '<?= htmlspecialchars(addslashes($f['title'])) ?>')">
                                            QR
                                        </button>
                                    </div>
                                </td>

                                <td>
                                    <?php if (!empty($f['allow_attachments'])): ?>
                                        <span class="badge bg-success badge-upload">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary badge-upload">Nonaktif</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Detail Form -->
                                        <a href="view_form.php?id=<?= $id ?>" class="btn btn-outline-secondary">
                                            Detail
                                        </a>

                                        <!-- Responses -->
                                        <a href="view_response.php?id=<?= $id ?>" class="btn btn-outline-info">
                                            Responses
                                        </a>

                                        <!-- Edit -->
                                        <a href="edit_form.php?id=<?= $id ?>" class="btn btn-outline-primary">
                                            Edit
                                        </a>

                                        <!-- Delete -->
                                        <a href="delete_form.php?id=<?= $id ?>"
                                           class="btn btn-outline-danger"
                                           onclick="return confirm('Yakin ingin menghapus form ini beserta semua responses-nya?');">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal QR Bootstrap -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">QR Code Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImage" src="" alt="QR Code" class="img-fluid mb-2">
                <p class="small text-muted mb-0" id="qrFormTitle"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    Tutup
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="openQRNewTab()">
                    Buka QR di Tab Baru
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Copy Link
    function copyLink(id) {
        let input = document.getElementById("link" + id);
        input.select();
        input.setSelectionRange(0, 99999);

        navigator.clipboard.writeText(input.value)
            .then(() => alert("Link disalin!"))
            .catch(() => alert("Gagal menyalin link"));
    }

    // QR Modal
    let qrSrcGlobal = "";
    function showQR(src, title) {
        qrSrcGlobal = src;
        document.getElementById("qrImage").src = src;
        document.getElementById("qrFormTitle").innerText = title || "";
        let qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
        qrModal.show();
    }

    function openQRNewTab() {
        if (qrSrcGlobal) {
            window.open(qrSrcGlobal, "_blank");
        }
    }
</script>
</body>
</html>
