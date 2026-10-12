<?php
/** Layout atas: sidebar desktop + header. Butuh $user dan $page. */
$nav = ['dashboard' => ['Dashboard', '🏠'], 'tasks' => ['Tugas', '📋'], 'settings' => ['Pengaturan', '⚙️']];
?>
<!DOCTYPE html>
<html lang="id" class="<?= $user['theme'] === 'dark' ? 'dark' : '' ?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($nav[$page][0]) ?> - TUNTASIN</title>
<link rel="stylesheet" href="asset/style.css">
<script>localStorage.setItem('tuntasin-theme', '<?= e($user['theme']) ?>');
window.TUNTASIN = { notifications: <?= (int)$user['notifications'] ?>, csrf: '<?= csrf_token() ?>' };</script>
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand small-brand">TUNTASIN</div>
    <nav>
      <?php foreach ($nav as $k => [$label, $icon]): ?>
        <a href="<?= $k ?>.php" class="<?= $page === $k ? 'active' : '' ?>"><span><?= $icon ?></span><?= $label ?></a>
      <?php endforeach; ?>
    </nav>
    <a href="logout.php" class="logout">↩ Keluar</a>
  </aside>
  <main class="content">
    <header class="topbar">
      <h1><?= e($nav[$page][0]) ?></h1>
      <span class="muted">Halo, <?= e($user['full_name'] ?: $user['username']) ?></span>
    </header>
    <?php if ($m = flash()): ?><div class="alert success"><?= e($m) ?></div><?php endif; ?>
