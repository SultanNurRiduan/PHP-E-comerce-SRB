<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../../lib/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
  $id = (int) $_POST['id'];
  
  // Ambil data produk
  $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
  $stmt->execute([$id]);
  $product = $stmt->fetch();
  
  if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
    exit;
  }
  
  try {
    // Hapus gambar jika ada
    if (!empty($product['image']) && file_exists(__DIR__ . '/../../../public/assets/images/' . $product['image'])) {
      unlink(__DIR__ . '/../../../public/assets/images/' . $product['image']);
    }
    
    // Hapus produk dari database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'Produk berhasil dihapus']);
  } catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus produk: ' . $e->getMessage()]);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>