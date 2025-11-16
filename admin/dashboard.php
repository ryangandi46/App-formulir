<?php
require '../config.php';
if (!isset($_SESSION['admin'])) header("Location: ../login.php");

$forms = $pdo->query("SELECT * FROM forms ORDER BY id DESC")->fetchAll();

// Build base URL otomatis
//$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$rootPath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
//$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $rootPath;
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . "/App_Form";

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Dashboard Admin</title>
<style>
    body { font-family: Arial, sans-serif; background:#f7f7f7; padding:20px; }
    .card { background:#fff; padding:16px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); max-width:960px; margin:auto; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px; border-bottom:1px solid #ddd; }
    th { background:#eee; }
    .btn { padding:6px 10px; border-radius:6px; cursor:pointer; font-size:13px; border:none; }
    .btn-primary { background:#2b6cb0; color:#fff; }
    .btn-outline { background:#fff; border:1px solid #bbb; color:#333; }
    .btn-small { padding:4px 8px; font-size:12px; }
    .link-box { display:flex; gap:6px; align-items:center; }
    .link-box input { padding:6px; font-size:12px; width:220px; }
    /* Modal */
    .modal-overlay { position:fixed; inset:0; display:none; justify-content:center; align-items:center; background:rgba(0,0,0,0.5); z-index:9999; }
    .modal { background:#fff; padding:18px; border-radius:10px; max-width:320px; text-align:center; }
    .modal img { max-width:100%; margin-bottom:10px; }
</style>
</head>
<body>

<div class="card">
    <h2>Dashboard Admin</h2>
    <a class="btn btn-primary" href="create_form.php">+ Buat Form Baru</a>
    <a class="btn btn-outline" href="../logout.php" style="float:right;">Logout</a>
    <br><br>

    <table>
        <tr>
            <th>Nama Form</th>
            <th>Link Publik</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($forms as $f): 
            $id = $f['id'];
            $publicLink = $baseUrl . "/f/" . $id;
            $qrSrc = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=" . urlencode($publicLink);
        ?>
        <tr>
            <td><?= htmlspecialchars($f['title']) ?></td>

            <td>
                <div class="link-box">
                    <input id="link<?= $id ?>" value="<?= $publicLink ?>" readonly>
                    <button class="btn btn-small btn-primary" onclick="copyLink('<?= $id ?>')">Salin</button>
                    <button class="btn btn-small btn-outline" onclick="showQR('<?= $qrSrc ?>')">QR</button>
                </div>
            </td>

            <td>
                <a class="btn btn-small btn-outline" href="view_form.php?id=<?= $id ?>">Detail</a>
                <a class="btn btn-small btn-outline" href="view_response.php?id=<?= $id ?>">Responses</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

</div>

<!-- MODAL QR CODE -->
<div id="modal" class="modal-overlay">
    <div class="modal">
        <h3>QR Code</h3>
        <img id="qrImage" src="" alt="">
        <br>
        <button class="btn btn-primary" onclick="openQR()">Buka di Tab Baru</button>
        <button class="btn btn-outline" onclick="closeModal()">Tutup</button>
    </div>
</div>

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

// Show QR Modal
let qrSrcGlobal = "";
function showQR(src) {
    qrSrcGlobal = src;
    document.getElementById("qrImage").src = src;
    document.getElementById("modal").style.display = "flex";
}

// Close Modal
function closeModal() {
    document.getElementById("modal").style.display = "none";
}

// Open QR in new tab
function openQR() {
    window.open(qrSrcGlobal, "_blank");
}
</script>

</body>
</html>
