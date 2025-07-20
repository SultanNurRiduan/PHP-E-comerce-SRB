<?php
require_once __DIR__ . '/../../../lib/db.php';
$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);

if ($id <= 0) {
  echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
  exit;
}

$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$success = $stmt->execute([$id]);
echo json_encode(['success' => $success, 'message' => $success ? 'Kategori dihapus' : 'Gagal menghapus']);
