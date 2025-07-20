<?php
require_once __DIR__ . '/../../../lib/db.php';
$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$name = trim($data['name'] ?? '');

if ($id <= 0 || $name === '') {
  echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
  exit;
}

$stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
$success = $stmt->execute([$name, $id]);
echo json_encode(['success' => $success, 'message' => $success ? 'Kategori diubah' : 'Gagal mengubah']);
