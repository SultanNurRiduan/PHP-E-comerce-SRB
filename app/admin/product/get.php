<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../lib/db.php';

if (isset($_GET['id'])) {
  $id = (int) $_GET['id'];

  $stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name, c.category_type
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
  ");
  $stmt->execute([$id]);
  $product = $stmt->fetch();

  if ($product) {
    echo json_encode(['success' => true, 'product' => $product]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'ID produk tidak ditemukan']);
}
