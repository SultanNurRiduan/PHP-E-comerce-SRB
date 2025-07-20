<?php
// Ambil produk dari database jika belum ada
if (!isset($products)) {
  require_once __DIR__ . '/../lib/db.php';
  $stmt = $pdo->query("
    SELECT p.id, p.name, p.description, p.price, p.image, p.stock, c.name AS category 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
    LIMIT 10
  ");
  $products = $stmt->fetchAll();
}
?>

<!-- Carousel Produk Horizontal -->
<div class="flex gap-0 px-6 py-10 relative overflow-x-auto scrollbar-hide">
  <?php foreach ($products as $product): ?>
    <div class="group relative z-50 hover:z-[100] -ml-12 first:ml-0 transition-all duration-300">
      <div class="w-auto h-auto bg-gray-100  shadow-xl p-4 flex flex-col justify-between transform group-hover:-translate-y-4 duration-300 group-hover:ring-2 group-hover:ring-black relative">

        <!-- Badge Kategori -->
        <div class="absolute top-0 right-0 m-2 z-10">
          <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
            <?= htmlspecialchars($product['category']) ?>
          </span>
        </div>

        <!-- Gambar Produk -->
        <div class="w-full h-28 overflow-hidden rounded-lg mb-2">
          <img 
            src="/shoe-shop/public/assets/images/<?= htmlspecialchars($product['image']) ?>"
            alt="<?= htmlspecialchars($product['name']) ?>"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3"
          >
        </div>

        <!-- Info Produk -->
        <h3 class="text-sm font-semibold text-gray-800 line-clamp-2">
          <?= htmlspecialchars($product['name']) ?>
        </h3>
        <p class="text-xs text-gray-600 line-clamp-2 mb-1">
          <?= htmlspecialchars($product['description']) ?>
        </p>

        <div class="text-xs text-gray-500">Stok: <?= (int)$product['stock'] ?></div>
        <div class="text-green-600 font-bold text-sm">
          Rp <?= number_format($product['price'], 0, ',', '.') ?>
        </div>

        <!-- Tombol Keranjang -->
        <div class="mt-2">
          <button 
            onclick="AddToCart(<?= $product['id'] ?>)"
            type="button"
            class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-1 rounded-md text-sm"
          >
            <i class="fas fa-shopping-cart"></i> Tambah
          </button>
        </div>

        <!-- Notifikasi -->
        <div id="notif-<?= $product['id'] ?>" class="text-center text-green-600 text-xs py-1 opacity-0 transition-all duration-300">
          ✓ Produk berhasil ditambahkan!
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Styling Scrollbar & Animasi -->
<style>
  .scrollbar-hide::-webkit-scrollbar {
    display: none;
  }
  .scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }

  @keyframes dash {
    to {
      stroke-dashoffset: 80;
    }
  }

  .animate-\[dash_1s_ease-out_forwards\] {
    animation: dash 1s ease-out forwards;
  }
</style>

<!-- Script Add to Cart -->
<script>
  function AddToCart(productId) {
    fetch('/shoe-shop/app/cart/add.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `product_id=${productId}&action=add`
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Update badge cart jika ada
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
          cartCountElement.textContent = data.totalItems;
        }

        // Tampilkan notifikasi sukses sementara
        const notif = document.getElementById('notif-' + productId);
        notif.classList.remove('opacity-0');
        notif.classList.add('opacity-100');
        setTimeout(() => notif.classList.add('opacity-0'), 1500);
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
  }
</script>
