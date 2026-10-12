<?php
/** Dashboard: progres %, total, selesai, belum selesai, dan tugas terdekat. */
require 'config.php';
$user = require_login();
$page = 'dashboard';

$st = $pdo->prepare('SELECT COUNT(*) total, COALESCE(SUM(completed),0) done FROM tasks WHERE user_id = ?');
$st->execute([$user['id']]);
$s = $st->fetch();
$total = (int)$s['total']; $done = (int)$s['done']; $pending = $total - $done;
$pct = $total ? round($done / $total * 100) : 0;

$st = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? AND completed = 0 ORDER BY due_date IS NULL, due_date, due_time LIMIT 5');
$st->execute([$user['id']]);
$upcoming = $st->fetchAll();

include 'include/header.php';
?>
<section class="card">
  <div class="row between"><strong>Progres Tugas</strong><strong><?= $pct ?>%</strong></div>
  <div class="progress"><div style="width: <?= $pct ?>%"></div></div>
</section>
<section class="stats">
  <div class="card stat"><span class="muted">Total</span><b><?= $total ?></b></div>
  <div class="card stat ok"><span class="muted">Selesai</span><b><?= $done ?></b></div>
  <div class="card stat warn"><span class="muted">Belum Selesai</span><b><?= $pending ?></b></div>
</section>
<section class="card">
  <div class="row between"><strong>Tugas Terdekat</strong><a href="tasks.php">Lihat semua →</a></div>
  <?php if (!$upcoming): ?><p class="muted">Tidak ada tugas tertunda. Mantap! 🎉</p><?php endif; ?>
  <ul class="task-list">
    <?php foreach ($upcoming as $t): ?>
      <li class="task"><div><b><?= e($t['title']) ?></b>
        <div class="meta"><span class="badge"><?= e($t['category']) ?></span><span class="badge p-<?= e($t['priority']) ?>"><?= e(ucfirst($t['priority'])) ?></span>
        <?php if ($t['due_date']): ?><span>📅 <?= e(date('d M Y', strtotime($t['due_date']))) ?> <?= $t['due_time'] ? e(substr($t['due_time'], 0, 5)) : '' ?></span><?php endif; ?></div></div></li>
    <?php endforeach; ?>
  </ul>
</section>
<?php include 'include/footer.php'; ?>
