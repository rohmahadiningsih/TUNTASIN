<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
/** Manajemen tugas: tambah, tandai selesai, hapus, cari, filter. */
require 'config.php';
$user = require_login();
$page = 'tasks';
$categories = ['Umum', 'Sekolah', 'Kuliah', 'Pekerjaan', 'Pribadi'];
$priorities = ['rendah', 'sedang', 'tinggi'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        if ($title !== '') {
            $cat = in_array($_POST['category'] ?? '', $categories) ? $_POST['category'] : 'Umum';
            $pri = in_array($_POST['priority'] ?? '', $priorities) ? $_POST['priority'] : 'sedang';
            $pdo->prepare('INSERT INTO tasks (user_id, title, category, priority, due_date, due_time) VALUES (?,?,?,?,?,?)')
                ->execute([$user['id'], $title, $cat, $pri, ($_POST['due_date'] ?? '') ?: null, ($_POST['due_time'] ?? '') ?: null]);
            flash('Tugas ditambahkan.');
        }
    } elseif ($action === 'toggle') {
        $pdo->prepare('UPDATE tasks SET completed = 1 - completed WHERE id = ? AND user_id = ?')->execute([$id, $user['id']]);
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?')->execute([$id, $user['id']]);
        flash('Tugas dihapus.');
    }
    header('Location: tasks.php?' . http_build_query(array_intersect_key($_GET, array_flip(['q', 'status', 'category', 'priority']))));
    exit;
}

// Pencarian & filter
$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'semua';
$fcat = $_GET['category'] ?? '';
$fpri = $_GET['priority'] ?? '';
$sql = 'SELECT * FROM tasks WHERE user_id = ?';
$args = [$user['id']];
if ($q !== '') { $sql .= ' AND title LIKE ?'; $args[] = "%$q%"; }
if ($status === 'selesai') $sql .= ' AND completed = 1';
if ($status === 'belum') $sql .= ' AND completed = 0';
if (in_array($fcat, $categories)) { $sql .= ' AND category = ?'; $args[] = $fcat; }
if (in_array($fpri, $priorities)) { $sql .= ' AND priority = ?'; $args[] = $fpri; }
$sql .= ' ORDER BY completed, due_date IS NULL, due_date, due_time, id DESC';
$st = $pdo->prepare($sql);
$st->execute($args);
$tasks = $st->fetchAll();

include 'include/header.php';
?>
<section class="card" id="tambah">
  <strong>Tambah Tugas</strong>
  <form method="post" class="grid-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <input name="title" placeholder="Judul tugas..." required class="span-2">
    <select name="category"><?php foreach ($categories as $c): ?><option><?= $c ?></option><?php endforeach; ?></select>
    <select name="priority"><?php foreach ($priorities as $p): ?><option value="<?= $p ?>" <?= $p === 'sedang' ? 'selected' : '' ?>><?= ucfirst($p) ?></option><?php endforeach; ?></select>
    <input type="date" name="due_date">
    <input type="time" name="due_time">
    <button class="btn primary span-2">+ Tambah</button>
  </form>
</section>

<form method="get" class="card filters">
  <input name="q" value="<?= e($q) ?>" placeholder="🔍 Cari tugas...">
  <select name="status" onchange="this.form.submit()">
    <?php foreach (['semua' => 'Semua', 'belum' => 'Belum Selesai', 'selesai' => 'Selesai'] as $k => $v): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
  </select>
  <select name="category" onchange="this.form.submit()"><option value="">Semua Kategori</option>
    <?php foreach ($categories as $c): ?><option <?= $fcat === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select>
  <select name="priority" onchange="this.form.submit()"><option value="">Semua Prioritas</option>
    <?php foreach ($priorities as $p): ?><option value="<?= $p ?>" <?= $fpri === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option><?php endforeach; ?></select>
  <button class="btn">Cari</button>
</form>

<section class="card">
  <?php if (!$tasks): ?><p class="muted center">Belum ada tugas yang cocok.</p><?php endif; ?>
  <ul class="task-list">
  <?php foreach ($tasks as $t): ?>
    <li class="task <?= $t['completed'] ? 'done' : '' ?>">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $t['id'] ?>">
        <button class="check" title="Tandai selesai"><?= $t['completed'] ? '✔' : '' ?></button></form>
      <div class="grow"><b><?= e($t['title']) ?></b>
        <div class="meta"><span class="badge"><?= e($t['category']) ?></span><span class="badge p-<?= e($t['priority']) ?>"><?= e(ucfirst($t['priority'])) ?></span>
        <?php if ($t['due_date']): ?><span>📅 <?= e(date('d M Y', strtotime($t['due_date']))) ?> <?= $t['due_time'] ? e(substr($t['due_time'], 0, 5)) : '' ?></span><?php endif; ?></div></div>
      <form method="post" onsubmit="return confirm('Hapus tugas ini?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>">
        <button class="btn danger small">Hapus</button></form>
    </li>
  <?php endforeach; ?>
  </ul>
</section>
<?php include 'includes/footer.php'; ?>

</body>
</html>