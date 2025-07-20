<?php
session_start();
require_once __DIR__ . '/../../lib/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  die("Produk tidak ditemukan.");
}

$stmt = $pdo->prepare("
  SELECT p.id, p.name, p.description, p.price, p.stock, p.image, 
         c.name AS category, c.category_type
  FROM products p
  JOIN categories c ON p.category_id = c.id
  WHERE p.id = :id
");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
  die("Produk tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['name']) ?> - Detail Produk</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">
  <div class="max-w-6xl mx-auto px-6 py-10">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden md:flex md:gap-8">
      <!-- Gambar -->
      <div class="md:w-1/2 p-6 flex justify-center items-center bg-gray-50">
        <img src="/shoe-shop/public/assets/images/<?= htmlspecialchars($product['image']) ?>"
             onerror="this.src='https://via.placeholder.com/300x300?text=No+Image';"
             alt="<?= htmlspecialchars($product['name']) ?>"
             class="w-full max-w-sm h-auto rounded-xl shadow">
      </div>

      <!-- Detail -->
      <div class="md:w-1/2 p-6 flex flex-col justify-between">
        <div>
          <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h1>
          <div class="flex items-center gap-2 mb-4">
            <span class="text-sm bg-red-100 text-red-600 px-3 py-1 rounded-full font-medium">
              <?= htmlspecialchars($product['category']) ?>
            </span>
            <span class="text-xs text-gray-500 italic">(<?= htmlspecialchars($product['category_type']) ?>)</span>
          </div>

          <p class="text-2xl text-red-600 font-bold mb-4">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>

          <div class="mb-4">
            <h2 class="text-md font-semibold mb-1">Deskripsi:</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
          </div>

          <p class="text-sm text-gray-600">Stok tersedia: <strong><?= $product['stock'] ?></strong> item</p>
        </div>

        <!-- Tombol -->
        <div class="mt-6 flex flex-col sm:flex-row gap-3">
          <button
            onclick="addToCart(<?= $product['id'] ?>)"
            class="w-full sm:w-auto px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded transition duration-200">
            Beli Sekarang
          </button>

          <a href="/shoe-shop/index.php?route=baru"
            class="w-full sm:w-auto text-center px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded transition duration-200">
            ← Kembali
          </a>
        </div>

        <!-- Notifikasi -->
        <div id="notif" class="hidden mt-4 p-3 rounded bg-green-100 text-green-800 text-sm"></div>
      </div>
    </div>
  </div>

  <script>
    function addToCart(productId) {
      fetch('/shoe-shop/app/cart/add.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id: productId,
          change: 1
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const notif = document.getElementById('notif');
          notif.textContent = 'Produk berhasil ditambahkan ke keranjang.';
          notif.classList.remove('hidden');
          setTimeout(() => notif.classList.add('hidden'), 3000);
        } else {
          alert("Gagal menambahkan produk.");
        }
      })
      .catch(err => {
        console.error(err);
        alert("Terjadi kesalahan.");
      });
    }
  </script>
</body>
</html>
