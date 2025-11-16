<?php
require __DIR__ . '/../../config.php';

// $id = $_GET['id'] ?? 0;

// $stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
// $stmt->execute([$id]);
// $form = $stmt->fetch();

// if (!$form) {
//     echo "Form tidak ditemukan.";
//     exit;
// }

// echo "<h1>".$form['title']."</h1>";

$form_id = $_GET['id'] ?? 0;
if (!$form_id) die("Form tidak ditemukan.");

// Ambil data form
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$form_id]);
$form = $stmt->fetch();

if (!$form) die("Form tidak ditemukan.");

// Ambil semua pertanyaan
$q = $pdo->prepare("SELECT * FROM questions WHERE form_id = ? ORDER BY id ASC");
$q->execute([$form_id]);
$questions = $q->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= htmlspecialchars($form['title']) ?></title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            max-width: 700px;
            margin: auto;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            padding: 12px 20px;
            background: #0a7cff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .qbox {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            background: #f7f7f7;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }

        .option-label {
            font-weight: normal;
        }
    </style>
</head>

<body>

    <h2><?= htmlspecialchars($form['title']) ?></h2>
    <p><?= nl2br(htmlspecialchars($form['description'])) ?></p>

    <form action="submit.php" method="POST">
        <input type="hidden" name="form_id" value="<?= $form_id ?>">

        <?php foreach ($questions as $q): ?>
            <div class="qbox">
                <label><?= htmlspecialchars($q['question']) ?></label>

                <?php
                $type = $q['type'];
                $name = "question_" . $q['id'];
                $options = !empty($q['options']) ? explode("\n", trim($q['options'])) : [];
                ?>

                <!-- INPUT TYPE: TEXT -->
                <?php if ($type == 'text'): ?>
                    <input type="text" name="<?= $name ?>">

                    <!-- INPUT TYPE: TEXTAREA -->
                <?php elseif ($type == 'textarea'): ?>
                    <textarea name="<?= $name ?>"></textarea>

                    <!-- INPUT TYPE: NUMBER -->
                <?php elseif ($type == 'number'): ?>
                    <input type="number" name="<?= $name ?>">

                    <!-- INPUT TYPE: DATE -->
                <?php elseif ($type == 'date'): ?>
                    <input type="date" name="<?= $name ?>">

                    <!-- INPUT TYPE: RADIO -->
                <?php elseif ($type == 'radio'): ?>
                    <?php foreach ($options as $opt): ?>
                        <label class="option-label">
                            <input type="radio" name="<?= $name ?>" value="<?= htmlspecialchars($opt) ?>">
                            <?= htmlspecialchars($opt) ?>
                        </label><br>
                    <?php endforeach; ?>

                    <!-- INPUT TYPE: CHECKBOX -->
                <?php elseif ($type == 'checkbox'): ?>
                    <?php foreach ($options as $opt): ?>
                        <label class="option-label">
                            <input type="checkbox" name="<?= $name ?>[]" value="<?= htmlspecialchars($opt) ?>">
                            <?= htmlspecialchars($opt) ?>
                        </label><br>
                    <?php endforeach; ?>

                    <!-- INPUT TYPE: SELECT -->
                <?php elseif ($type == 'select'): ?>
                    <select name="<?= $name ?>">
                        <?php foreach ($options as $opt): ?>
                            <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                        <?php endforeach; ?>
                    </select>

                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit">Kirim</button>
    </form>

</body>

</html>
