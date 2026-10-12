<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TUNTASIN</title>
</head>
<body>
    <?php
/** Pengaturan: notifikasi, tema terang/gelap, profil, username, password. */
require 'config.php';
$user = require_login();
$page = 'settings';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    if ($a === 'prefs') {
        $theme = ($_POST['theme'] ?? '') === 'dark' ? 'dark' : 'light';
        $pdo->prepare('UPDATE users SET theme = ?, notifications = ? WHERE id = ?')
            ->execute([$theme, isset($_POST['notifications']) ? 1 : 0, $user['id']]);
        flash('Preferensi disimpan.');
    } elseif ($a === 'profile') {
        $pdo->prepare('UPDATE users SET full_name = ?, email = ? WHERE id = ?')
            ->execute([trim($_POST['full_name'] ?? ''), trim($_POST['email'] ?? ''), $user['id']]);
        flash('Profil diperbarui.');
    } elseif ($a === 'username') {
        $new = trim($_POST['username'] ?? '');
        $st = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id <> ?');
        $st->execute([$new, $user['id']]);
        if ($new === '') $error = 'Username tidak boleh kosong.';
        elseif ($st->fetch()) $error = 'Username sudah dipakai.';
        else { $pdo->prepare('UPDATE users SET username = ? WHERE id = ?')->execute([$new, $user['id']]); flash('Username diubah.'); }
    } elseif ($a === 'password') {
        if (!password_verify($_POST['current'] ?? '', $user['password_hash'])) $error = 'Password lama salah.';
        elseif (strlen($_POST['new'] ?? '') < 4) $error = 'Password baru minimal 4 karakter.';
        elseif ($_POST['new'] !== ($_POST['confirm'] ?? '')) $error = 'Konfirmasi password tidak cocok.';
        else { $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($_POST['new'], PASSWORD_DEFAULT), $user['id']]); flash('Password diubah.'); }
    }
    if (!$error) { header('Location: settings.php'); exit; }
    $user = require_login();
}
include 'include/header.php';
?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<section class="card">
  <strong>Preferensi</strong>
  <form method="post" class="stack" id="prefs-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="prefs">
    <label class="switch-row">Notifikasi pengingat tugas
      <input type="checkbox" name="notifications" id="notif-toggle" <?= $user['notifications'] ? 'checked' : '' ?> onchange="this.form.submit()"></label>
    <label class="switch-row">Mode gelap
      <input type="checkbox" name="theme" value="dark" <?= $user['theme'] === 'dark' ? 'checked' : '' ?> onchange="this.form.submit()"></label>
    <p class="muted small" id="notif-status"></p>
  </form>
</section>
<section class="card">
  <strong>Profil</strong>
  <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="profile">
    <label>Nama lengkap<input name="full_name" value="<?= e($user['full_name']) ?>"></label>
    <label>Email<input type="email" name="email" value="<?= e($user['email']) ?>"></label>
    <button class="btn primary">Simpan Profil</button></form>
</section>
<section class="card">
  <strong>Ubah Username</strong>
  <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="username">
    <label>Username baru<input name="username" required value="<?= e($user['username']) ?>"></label>
    <button class="btn primary">Ubah Username</button></form>
</section>
<section class="card">
  <strong>Ubah Password</strong>
  <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="password">
    <label>Password lama<input type="password" name="current" required></label>
    <label>Password baru<input type="password" name="new" required minlength="4"></label>
    <label>Konfirmasi password baru<input type="password" name="confirm" required minlength="4"></label>
    <button class="btn primary">Ubah Password</button></form>
</section>
<?php include 'include/footer.php'; ?>

</body>
</html>