<?php
require '../config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

/* ambil form id/key dari GET (mendukung form=, id=, key=) */
$form = null;
$form_id = 0;

if (!empty($_GET['key'])) {
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE public_key = ?");
    $stmt->execute([$_GET['key']]);
    $form = $stmt->fetch();
    if ($form) $form_id = $form['id'];
} elseif (!empty($_GET['id'])) {
    $form_id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
    $stmt->execute([$form_id]);
    $form = $stmt->fetch();
} elseif (!empty($_GET['form'])) {
    $form_id = (int) $_GET['form'];
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
    $stmt->execute([$form_id]);
    $form = $stmt->fetch();
}

if (!$form) {
    exit("Form tidak ditemukan. (form_id = $form_id)");
}

/* ambil pertanyaan & responses */
$q = $pdo->prepare("SELECT * FROM questions WHERE form_id = ? ORDER BY id ASC");
$q->execute([$form_id]);
$questions = $q->fetchAll();

$r = $pdo->prepare("SELECT * FROM responses WHERE form_id = ? ORDER BY id ASC");
$r->execute([$form_id]);
$responses = $r->fetchAll();

/**
 * Prepared statements:
 * - $ansStmt     : jawaban teks biasa
 * - $fileByQStmt : file per response + per pertanyaan (type = file)
 * - $fileGlobal  : file global (question_id IS NULL) -> kolom Lampiran
 */
$ansStmt     = $pdo->prepare("SELECT answer FROM response_answers WHERE response_id = ? AND question_id = ?");
$fileByQStmt = $pdo->prepare("SELECT * FROM response_files WHERE response_id = ? AND question_id = ?");
$fileGlobal  = $pdo->prepare("SELECT * FROM response_files WHERE response_id = ? AND question_id IS NULL");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Responses - <?= htmlspecialchars($form['title']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background:#f1f3f4; }
    .table-scroll { overflow-x:auto; }
    table th, table td { vertical-align: top; white-space: normal; }
    .no-results { text-align:center; padding:20px; color:#666; }
    .search-row { gap:10px; align-items:center; }
    .file-preview-img {
        max-width:80px;
        max-height:80px;
        object-fit:cover;
        border-radius:6px;
        border:1px solid #ddd;
        display:block;
        margin-bottom:4px;
    }
    .file-preview-pdf {
        width:80px;
        height:80px;
        border:1px solid #ddd;
        border-radius:6px;
    }
    .file-name {
        font-size:12px;
    }
</style>
</head>
<body>
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><?= htmlspecialchars($form['title']) ?></h5>
                <small>Total response: <b><?= count($responses) ?></b></small>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-light btn-sm">Kembali</a>
            </div>
        </div>

        <div class="card-body">

            <?php if (empty($responses)): ?>
                <div class="alert alert-info mb-0">Belum ada response.</div>
            <?php else: ?>

            <div class="d-flex justify-content-between mb-3 search-row">
                <div class="w-50">
                    <input id="search" type="search" class="form-control" placeholder="Cari (ketik untuk mencari di semua kolom)...">
                </div>

                <div class="d-flex gap-2">
                    <a href="export_excel.php?form=<?= $form_id ?>" class="btn btn-success btn-sm">Export Excel</a>
                </div>
            </div>

            <div class="table-scroll">
                <table id="responsesTable" class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th style="min-width:50px">#</th>
                            <th style="min-width:160px">Tanggal</th>
                            <?php foreach ($questions as $qrow): ?>
                                <th><?= htmlspecialchars($qrow['question']) ?></th>
                            <?php endforeach; ?>                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responses as $i => $res): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><?= htmlspecialchars($res['created_at']) ?></td>

                                <?php foreach ($questions as $qrow): ?>
                                    <td>
                                        <?php
                                        // Jika pertanyaan bertipe "file", ambil file khusus pertanyaan ini
                                        if ($qrow['type'] === 'file') {
                                            $fileByQStmt->execute([$res['id'], $qrow['id']]);
                                            $filesQ = $fileByQStmt->fetchAll();

                                            if ($filesQ) {
                                                foreach ($filesQ as $f) {
                                                    $fileUrl  = "../uploads/" . rawurlencode($f['filename']);
                                                    $origName = htmlspecialchars($f['original_name']);
                                                    $ext      = strtolower(pathinfo($f['filename'], PATHINFO_EXTENSION));

                                                    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                                                        // Preview gambar
                                                        echo '<a href="'.$fileUrl.'" target="_blank">';
                                                        echo '<img src="'.$fileUrl.'" alt="'.$origName.'" class="file-preview-img">';
                                                        echo '</a>';
                                                        echo '<div class="file-name text-muted">'.$origName.'</div>';
                                                    } elseif ($ext === 'pdf') {
                                                        // Preview PDF kecil + link
                                                        echo '<a href="'.$fileUrl.'" target="_blank">';
                                                        echo '<embed src="'.$fileUrl.'" type="application/pdf" class="file-preview-pdf">';
                                                        echo '</a>';
                                                        echo '<div class="file-name text-muted">'.$origName.'</div>';
                                                    } else {
                                                        // File lain: icon + nama
                                                        echo '<div class="mb-1">';
                                                        echo '<i class="bi bi-file-earmark-text"></i> ';
                                                        echo '<a href="'.$fileUrl.'" target="_blank">'.$origName.'</a>';
                                                        echo '</div>';
                                                    }
                                                }
                                            } else {
                                                echo "-";
                                            }
                                        } else {
                                            // Tipe biasa: ambil dari response_answers
                                            $ansStmt->execute([$res['id'], $qrow['id']]);
                                            $answer = $ansStmt->fetchColumn() ?: "";
                                            echo nl2br(htmlspecialchars($answer));
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>                             
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="noResults" class="no-results" style="display:none;">Tidak ada hasil yang cocok.</div>

            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// debounce utility
function debounce(fn, delay){
    let t;
    return function(...args){
        clearTimeout(t);
        t = setTimeout(()=>fn.apply(this, args), delay);
    };
}

const searchInput = document.getElementById('search');
const table = document.getElementById('responsesTable');
const tbodyRows = table ? table.querySelectorAll('tbody tr') : [];
const noResults = document.getElementById('noResults');

function doSearch() {
    const q = (searchInput.value || '').trim().toLowerCase();
    if (!table) return;

    let visible = 0;
    tbodyRows.forEach(row => {
        // search across all cells of the row
        const text = row.textContent.replace(/\s+/g,' ').toLowerCase();
        if (q === '' || text.indexOf(q) !== -1) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    noResults.style.display = (visible === 0) ? 'block' : 'none';
}

// attach with debounce 150ms
searchInput.addEventListener('input', debounce(doSearch, 150));
</script>

</body>
</html>
