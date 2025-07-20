<?php
require_once __DIR__ . '/../../lib/db.php';

$keyword = trim($_GET['q'] ?? '');

$sql = "SELECT 
          p.id, p.name, p.description, p.price, p.image, p.stock, 
          c.name AS category, c.category_type
        FROM products p
        JOIN categories c ON p.category_id = c.id";
$params = [];

if (!empty($keyword)) {
  $sql .= " WHERE 
                p.name LIKE :kw OR 
                p.description LIKE :kw OR 
                CAST(p.stock AS CHAR) LIKE :kw OR 
                c.name LIKE :kw OR 
                c.category_type LIKE :kw";
  $params['kw'] = "%$keyword%";
}

$sql .= " ORDER BY p.created_at DESC"; // optional: bisa pakai LIMIT juga

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Semua Produk</title>
  <link href="../../dist/output.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

  <div class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-center font-bold text-xl my-6">
      <?= $keyword ? 'Hasil pencarian untuk: <span class="text-red-600">' . htmlspecialchars($keyword) . '</span>' : 'Produck Terbaru' ?>
    </h1>

    <!-- Grid wrapper yang pas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
      <?php include_once __DIR__ . '/../../components/cart.php'; ?>
    </div>
  </div>

</body>

</html>