<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
/**
 * Login & registrasi. Jika belum ada user sama sekali, form menjadi pendaftaran
 * akun pertama (setara "first-time password registration" versi lama).
 */
require 'config.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$hasUser = (int)$pdo->query('SELECT COUNT(*) FROM user')->fetchColumn() > 0;
$mode = $_GET['mode'] ?? ($hasUser ? 'login' : 'register');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || strlen($password) < 4) {
        $error = 'Username wajib diisi dan password minimal 4 karakter.';
    } elseif ($mode === 'register') {
        $st = $pdo->prepare('SELECT id FROM user WHERE username = ?');
        $st->execute([$username]);
        if ($st->fetch()) {
            $error = 'Username sudah dipakai.';
        } else {
            $pdo->prepare('INSERT INTO user (username, full_name, password_hash) VALUES (?, ?, ?)')
                ->execute([$username, $username, password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            header('Location: dashboard.php'); exit;
        }
    } else {
        $st = $pdo->prepare('SELECT * FROM user WHERE username = ?');
        $st->execute([$username]);
        $u = $st->fetch();
        if ($u && password_verify($password, $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$u['id'];
            header('Location: dashboard.php'); exit;
        }
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk - TUNTASIN</title>
<link rel="stylesheet" href="asset/style.css">
<script>if (localStorage.getItem('tuntasin-theme') === 'dark') document.documentElement.classList.add('dark');</script>
</head>
<body class="login-body">
  <main class="login-card">
    <h1 class="brand">TUNTASIN</h1>
    <p class="tagline">Bantu Selesaikan Tugasmu Tepat Waktu!</p>
    <h2><?= $mode === 'register' ? 'Buat Akun' : 'Masuk' ?></h2>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="login.php?mode=<?= e($mode) ?>" class="stack">
      <?= csrf_field() ?>
      <label>Username<input name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>"></label>
      <label>Password<input type="password" name="password" required minlength="4"></label>
      <button class="btn primary"><?= $mode === 'register' ? 'Daftar & Masuk' : 'Masuk' ?></button>
    </form>
    <p class="muted small center">
      <?php if ($mode === 'register'): ?>
        <?php if ($hasUser): ?>Sudah punya akun? <a href="login.php?mode=login">Masuk</a><?php else: ?>Pertama kali? Buat password untuk akunmu.<?php endif; ?>
      <?php else: ?>Belum punya akun? <a href="login.php?mode=register">Daftar</a><?php endif; ?>
    </p>
  </main>
</body>
</html>
</body>
</html>