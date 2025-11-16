<?php
require '../config.php';
if (!isset($_SESSION['admin'])) header("Location: ../login.php");

$id = $_GET['id'] ?? 0;

// Ambil form
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id=?");
$stmt->execute([$id]);
$form = $stmt->fetch();

// Ambil pertanyaan
$q = $pdo->prepare("SELECT * FROM questions WHERE form_id=? ORDER BY id");
$q->execute([$id]);
$questions = $q->fetchAll();

// Update
if ($_POST) {
    $title = $_POST['title'];
    $desc = $_POST['description'];

    $pdo->prepare("UPDATE forms SET title=?, description=? WHERE id=?")
        ->execute([$title, $desc, $id]);

    // Update existing questions
    foreach ($_POST['q_id'] as $i => $qid) {
        $question = $_POST['question'][$i];
        $type = $_POST['type'][$i];
        $options = $_POST['options'][$i] ?? '';

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
<html>
<head>
<title>Edit Form</title>
<style>
input, textarea, select { width: 100%; padding: 8px; margin-bottom: 10px; }
.box { border:1px solid #ddd; padding:10px; margin-bottom:10px; border-radius:8px; }
</style>

<script>
function addNewQ() {
    let c = document.getElementById("new_questions");
    c.innerHTML += `
        <div class='box'>
            <label>Pertanyaan Baru:</label>
            <input name="new_question[]" placeholder="Tulis pertanyaan">

            <label>Tipe Input:</label>
            <select name="new_type[]">
                <option value="text">Text</option>
                <option value="textarea">Textarea</option>
                <option value="radio">Radio</option>
                <option value="checkbox">Checkbox</option>
                <option value="select">Select</option>
            </select>

            <label>Opsi (jika radio/checkbox/select) - pisahkan dengan baris baru:</label>
            <textarea name="new_options[]" placeholder="opsi1&#10;opsi2"></textarea>
        </div>
    `;
}
</script>
</head>
<body>

<h2>Edit Form</h2>

<form method="POST">

    <label>Judul Form</label>
    <input name="title" value="<?= htmlspecialchars($form['title']) ?>">

    <label>Deskripsi</label>
    <textarea name="description"><?= htmlspecialchars($form['description']) ?></textarea>

    <h3>Pertanyaan</h3>

    <?php foreach ($questions as $q): ?>
        <div class="box">
            <input type="hidden" name="q_id[]" value="<?= $q['id'] ?>">

            <label>Pertanyaan:</label>
            <input name="question[]" value="<?= htmlspecialchars($q['question']) ?>">

            <label>Tipe Input:</label>
            <select name="type[]">
                <option value="text"     <?= $q['type']=='text'?'selected':'' ?>>Text</option>
                <option value="textarea" <?= $q['type']=='textarea'?'selected':'' ?>>Textarea</option>
                <option value="radio"    <?= $q['type']=='radio'?'selected':'' ?>>Radio</option>
                <option value="checkbox" <?= $q['type']=='checkbox'?'selected':'' ?>>Checkbox</option>
                <option value="select"   <?= $q['type']=='select'?'selected':'' ?>>Select</option>
            </select>

            <label>Opsi (jika radio/checkbox/select):</label>
            <textarea name="options[]"><?= $q['options'] ?></textarea>
        </div>
    <?php endforeach; ?>

    <h3>Tambah Pertanyaan Baru</h3>
    <div id="new_questions"></div>

    <button type="button" onclick="addNewQ()">+ Tambah Pertanyaan</button>
    <br><br>

    <button type="submit">Simpan Perubahan</button>
</form>

</body>
</html>
