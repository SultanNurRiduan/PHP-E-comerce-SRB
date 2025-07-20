<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../../lib/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id           = (int) $_POST['id'];
  $name         = trim($_POST['name']);
  $price        = (int) $_POST['price'];
  $stock        = (int) $_POST['stock'];
  $description  = trim($_POST['description']);
  $category_id  = (int) $_POST['category_id'];
  $imageName    = '';

  if (!$id || !$category_id || !$name || !$price || !$stock) {
    echo json_encode(['success' => false, 'message' => 'Semua input wajib diisi']);
    exit;
  }

  try {
    $pdo->beginTransaction();

    // Update gambar jika ada
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $imageName = uniqid() . '-' . basename($_FILES['image']['name']);
      $uploadDir = __DIR__ . '/../../../public/assets/images/';
      $uploadPath = $uploadDir . $imageName;

      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        throw new Exception('Gagal mengunggah gambar');
      }

      $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ?, category_id = ? WHERE id = ?");
      $stmt->execute([$name, $description, $price, $stock, $imageName, $category_id, $id]);
    } else {
      $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ? WHERE id = ?");
      $stmt->execute([$name, $description, $price, $stock, $category_id, $id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Produk berhasil diperbarui']);
  } catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
