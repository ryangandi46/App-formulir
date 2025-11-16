<?php
require '../config.php';
if (!isset($_SESSION['admin'])) header("Location: ../login.php");

$id = $_GET['id'];

$questions = $pdo->prepare("SELECT * FROM questions WHERE form_id=?");
$questions->execute([$id]);
$q_list = $questions->fetchAll();

$responses = $pdo->prepare("SELECT * FROM responses WHERE form_id=? ORDER BY id DESC");
$responses->execute([$id]);
$r_list = $responses->fetchAll();
?>

<h2>Responses</h2>
<a href="dashboard.php">Back</a>

<?php foreach ($r_list as $r): ?>
  <h4>Response #<?= $r['id']; ?> — <?= $r['submitted_at']; ?></h4>
  <ul>
  <?php foreach ($q_list as $q): 
      $ans = $pdo->prepare("SELECT answer FROM response_answers WHERE response_id=? AND question_id=?");
      $ans->execute([$r['id'], $q['id']]);
      $a = $ans->fetchColumn();
  ?>
    <li><b><?= $q['question']; ?>:</b> <?= $a; ?></li>
  <?php endforeach; ?>
  </ul>
  <hr>
<?php endforeach; ?>
