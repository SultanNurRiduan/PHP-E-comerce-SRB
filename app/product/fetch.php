<?php
require_once __DIR__ . '/../../lib/db.php';

$categories = $_GET['category'] ?? [];
$keyword = $_GET['q'] ?? null;
$categoryType = $_GET['category_type'] ?? 'sneakers'; // ← default: sneakers

if (!is_array($categories)) {
  $categories = [$categories];
}

$query = "SELECT p.id, p.name, p.description, p.price, p.image, p.stock, c.name AS category 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";
$params = [];

// Filter berdasarkan category_type = sneakers
if (!empty($categoryType)) {
  $query .= " AND c.category_type = ?";
  $params[] = $categoryType;
}

// Filter keyword (nama/deskripsi)
if ($keyword) {
  $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
  $params[] = "%$keyword%";
  $params[] = "%$keyword%";
}

// Filter berdasarkan kategori (dari checkbox)
if (count($categories)) {
  $placeholders = implode(',', array_fill(0, count($categories), '?'));
  $query .= " AND p.category_id IN ($placeholders)";
  foreach ($categories as $cat) {
    $params[] = (int)$cat;
  }
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ✅ Render produk menggunakan komponen cart.php
if (!empty($products)) {
  $GLOBALS['products'] = $products;
  echo '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">';
  include __DIR__ . '/../../components/cart.php';
} else {
  echo '<p class="text-gray-500">Tidak ada produk yang cocok.</p>';
}
