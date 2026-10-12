<?php
/** API JSON: daftar tugas belum selesai dengan tenggat dalam 60 menit ke depan (untuk notifikasi). */
require 'config.php';
header('Content-Type: application/json');
if (empty($_SESSION['user_id'])) { http_response_code(401); echo '[]'; exit; }

$st = $pdo->prepare("SELECT id, title, CONCAT(due_date, ' ', COALESCE(due_time, '23:59:00')) AS due
  FROM tasks WHERE user_id = ? AND completed = 0 AND due_date IS NOT NULL
  HAVING due BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 60 MINUTE)");
$st->execute([$_SESSION['user_id']]);
echo json_encode($st->fetchAll());
