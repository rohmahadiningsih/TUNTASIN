<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body> 
    <?php
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'tuntasin_user');
        define('DB_USER', 'root');
        define('DB_PASS', '');
    

    session_start();
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Koneksi database gagal. Pastikan MySQL aktif dan database.sql sudah diimpor. (' . htmlspecialchars($e->getMessage()) . ')');
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check() {
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? null)) { http_response_code(400); die('Token tidak valid.'); }
}


function require_login() {
    global $pdo;
    if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
    $st = $pdo->prepare('SELECT * FROM user WHERE id = ?');
    $st->execute([$_SESSION['user_id']]);
    $u = $st->fetch();
    if (!$u) { session_destroy(); header('Location: login.php'); exit; }
    return $u;
}

/** Pesan flash sekali tampil. */
function flash($msg = null) {
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m;
}
 ?>
</body>
</html>