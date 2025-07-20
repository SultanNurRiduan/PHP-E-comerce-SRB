<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../../lib/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name         = trim($_POST['name']);
  $price        = (int) $_POST['price'];
  $stock        = (int) $_POST['stock'];
  $description  = trim($_POST['description']);
  $category_id  = (int) $_POST['category_id'];
  $imageName    = '';

  if (!$name || !$price || !$stock || !$category_id) {
    echo json_encode(['success' => false, 'message' => 'Semua input wajib diisi']);
    exit;
  }

  // Upload gambar
  if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $imageName = uniqid() . '-' . basename($_FILES['image']['name']);
    $uploadDir = __DIR__ . '/../../../public/assets/images/';
    $uploadPath = $uploadDir . $imageName;

    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
      try {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, stock, category_id)
          VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $imageName, $stock, $category_id]);

        echo json_encode(['success' => true, 'message' => 'Produk berhasil ditambahkan']);
      } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan produk: ' . $e->getMessage()]);
      }
    } else {
      echo json_encode(['success' => false, 'message' => 'Gagal mengunggah gambar']);
    }
  } else {
    echo json_encode(['success' => false, 'message' => 'Gambar wajib diunggah']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
