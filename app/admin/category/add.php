<?php
require_once __DIR__ . '/../../../lib/db.php';
$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');

if ($name === '') {
  echo json_encode(['success' => false, 'message' => 'Nama tidak boleh kosong']);
  exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
$stmt->execute([$name]);
if ($stmt->fetchColumn() > 0) {
  echo json_encode(['success' => false, 'message' => 'Kategori sudah ada']);
  exit;
}

$stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
$stmt->execute([$name]);

echo json_encode([
  'success' => true,
  'id' => $pdo->lastInsertId(),
  'message' => 'Kategori berhasil ditambahkan'
]);
