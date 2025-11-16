<?php
require '../config.php';
if (!isset($_SESSION['admin'])) header("Location: ../login.php");

if ($_POST) {
    $title = $_POST['title'];
    $desc = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO forms (title, description) VALUES (?,?)");
    $stmt->execute([$title, $desc]);

    $form_id = $pdo->lastInsertId();

    // Simpan pertanyaan
    foreach ($_POST['question'] as $i => $q) {
        if (!empty($q)) {

            $type = $_POST['type'][$i];
            $options = $_POST['options'][$i] ?? "";

            $pdo->prepare("INSERT INTO questions (form_id, question, type, options) VALUES (?,?,?,?)")
                ->execute([$form_id, $q, $type, $options]);
        }
    }

    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Buat Form Baru</title>
<style>
input, textarea, select { width: 100%; padding: 8px; margin-bottom: 10px; }
.box { border:1px solid #ccc; padding: 10px; border-radius:8px; margin-bottom:10px; }
</style>
<script>
function addQ() {
    let area = document.getElementById("q");

    area.innerHTML += `
        <div class='box'>
            <label>Pertanyaan:</label>
            <input name="question[]" placeholder="Tulis pertanyaan">

            <label>Tipe Input:</label>
            <select name="type[]">
                <option value="text">Text</option>
                <option value="textarea">Textarea</option>
                <option value="radio">Radio</option>
                <option value="checkbox">Checkbox</option>
                <option value="select">Select</option>
            </select>

            <label>Opsi (untuk radio/checkbox/select — pisahkan baris baru):</label>
            <textarea name="options[]" placeholder="opsi1&#10;opsi2&#10;opsi3"></textarea>
        </div>
    `;
}
</script>
</head>
<body>

<h2>Buat Form Baru</h2>

<form method="POST">
    <label>Judul</label>
    <input name="title">

    <label>Deskripsi</label>
    <textarea name="description"></textarea>

    <h3>Pertanyaan:</h3>
    <div id="q">
        <div class="box">
            <label>Pertanyaan:</label>
            <input name="question[]" placeholder="Pertanyaan 1">

            <label>Tipe Input:</label>
            <select name="type[]">
                <option value="text">Text</option>
                <option value="textarea">Textarea</option>
                <option value="radio">Radio</option>
                <option value="checkbox">Checkbox</option>
                <option value="select">Select</option>
            </select>

            <label>Opsi (untuk radio/checkbox/select — pisahkan baris baru):</label>
            <textarea name="options[]" placeholder="opsi1&#10;opsi2&#10;opsi3"></textarea>
        </div>
    </div>

    <button type="button" onclick="addQ()">+ Tambah Pertanyaan</button>
    <br><br>

    <button type="submit">Simpan Form</button>
</form>

</body>
</html>
