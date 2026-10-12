<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TUNTASIN</title>
</head>
<body>
    </main> 
    </div> 
    <nav class="bottom-nav">
  <a href="dashboard.php" class="<?= $page === 'dashboard' ? 'active' : '' ?>">🏠<small>Dashboard</small></a>
  <a href="tasks.php" class="<?= $page === 'tasks' ? 'active' : '' ?>">📋<small>Tugas</small></a>
  <a href="tasks.php#tambah" class="fab" aria-label="Tambah tugas">+</a>
  <a href="settings.php" class="<?= $page === 'settings' ? 'active' : '' ?>">⚙️<small>Pengaturan</small></a>
  <a href="logout.php">↩<small>Keluar</small></a>
</nav>
<script src="assets/app.js"></script>
</body>
</html>

</body>
</html>