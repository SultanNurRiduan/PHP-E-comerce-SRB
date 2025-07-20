<?php
require_once __DIR__ . '/../../lib/db.php';

// Ambil parameter dari URL
$keyword = $_GET['q'] ?? null;
$categories = $_GET['category'] ?? [];
$categoryType = $_GET['category_type'] ?? 'clothes'; // default tetap clothes

// Pastikan $categories berupa array
if (!is_array($categories)) {
  $categories = [$categories];
}

// Bangun query awal
$query = "SELECT p.id, p.name, p.description, p.price, p.image, p.stock, c.name AS category 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          WHERE c.category_type = ?";
$params = [$categoryType]; // → 'clothes'

// Filter berdasarkan keyword
if (!empty($keyword)) {
  $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
  $params[] = "%$keyword%";
  $params[] = "%$keyword%";
}

// Filter berdasarkan kategori (jika ada checkbox)
if (count($categories)) {
  $placeholders = implode(',', array_fill(0, count($categories), '?'));
  $query .= " AND p.category_id IN ($placeholders)";
  foreach ($categories as $cat) {
    $params[] = (int)$cat;
  }
}

// Eksekusi query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Render hasil
if (!empty($products)) {
  $GLOBALS['products'] = $products;
  echo '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">';
  include __DIR__ . '/../../components/cart.php';
} else {
  echo '<p class="text-gray-500">Tidak ada produk yang cocok.</p>';
}
