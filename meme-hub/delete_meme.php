<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}
verifyCsrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    flash('error', 'รหัสมีมไม่ถูกต้อง');
    redirect('index.php');
}
$pdo = getPDO();
$stmt = $pdo->prepare('DELETE FROM memes WHERE id = :id');
$stmt->execute(['id' => $id]);
flash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'ลบมีมเรียบร้อยแล้ว' : 'ไม่พบมีมที่ต้องการลบ');
redirect('index.php');
