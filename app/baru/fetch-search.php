<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/db.php'; 

// Ambil parameter dari URL
$keyword       = trim($_GET['q'] ?? '');
$categoryType  = $_GET['category_type'] ?? null;
$categories    = $_GET['category'] ?? [];

// Pastikan categories berupa array
if (!is_array($categories)) {
  $categories = [$categories];
}

$params = [];
$whereClauses = [];

// 🔎 Filter keyword di beberapa kolom
if ($keyword !== '') {
  $whereClauses[] = "(p.name LIKE :kw OR p.description LIKE :kw OR 
                      CAST(p.stock AS CHAR) LIKE :kw OR 
                      c.name LIKE :kw OR c.category_type LIKE :kw)";
  $params['kw'] = "%$keyword%";
}

// 🔎 Filter category_type
if (!empty($categoryType)) {
  $whereClauses[] = "c.category_type = :category_type";
  $params['category_type'] = $categoryType;
}

// 🔎 Filter berdasarkan kategori ID (checkbox)
if (!empty($categories)) {
  $placeholders = [];
  foreach ($categories as $i => $catId) {
    $key = "cat_$i";
    $placeholders[] = ":$key";
    $params[$key] = (int)$catId;
  }
  $whereClauses[] = "p.category_id IN (" . implode(', ', $placeholders) . ")";
}

// 🧩 Query utama
$sql = "SELECT 
          p.id, p.name, p.description, p.stock, 
          p.price, p.image, 
          c.name AS category, c.category_type
        FROM products p
        JOIN categories c ON p.category_id = c.id";

if (!empty($whereClauses)) {
  $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$sql .= " ORDER BY p.created_at DESC LIMIT 10";

try {
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($results);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Query gagal',
    'message' => $e->getMessage()
  ]);
}
