<?php
// ✅ Gunakan variabel $products yang dikirim dari luar jika tersedia
if (!isset($products)) {
  require_once __DIR__ . '/../lib/db.php';
  $stmt = $pdo->query("
    SELECT p.id, p.name, p.description, p.price, p.image, p.stock, c.name AS category 
    FROM products p
    JOIN categories c ON p.category_id = c.id
  ");
  $products = $stmt->fetchAll();
}
?>
      <?php foreach ($products as $product): ?>
        <div class="group relative ">
          <div class="bg-gray-100 p-4 flex flex-col h-full hover:border-2 hover:border-black">
            <!-- Product Badge -->
            <div class="absolute top-0 right-0 m-3 z-10">
              <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                <?= htmlspecialchars($product['category']) ?>
              </span>
            </div>

            <!-- Product Image with Hover Effect -->
            <div class="w-full h-48 mb-4 overflow-hidden rounded-lg relative">
              <img 
                src="/shoe-shop/public/assets/images/<?= htmlspecialchars($product['image']) ?>"
                alt="<?= htmlspecialchars($product['name']) ?>"
                class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3"
              >
              <!-- Overlay on Hover -->
              <div class="absolute  transition-all duration-300"></div>
            </div>

            <!-- Product Details -->
            <div class="flex-grow border-t border-black pt-4">
              <h3 class="text-lg font-semibold mb-2 text-gray-800 line-clamp-2">
                <?= htmlspecialchars($product['name']) ?>
              </h3>

              <p class="text-sm text-gray-600 line-clamp-3">
                <?= htmlspecialchars($product['description']) ?>
              </p>

              <!-- Stock and Price -->
              <div class="flex flex-col mb-3">
                <span class="text-sm text-gray-500">
                  <i class="fas fa-box-open mr-1"></i> 
                  Stok: <?= (int)$product['stock'] ?>
                </span>
                <span class="text-green-600 font-bold">
                  Rp <?= number_format($product['price'], 0, ',', '.') ?>
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-2">
              <button 
                onclick="AddToCart(<?= $product['id'] ?>)"
                type="button"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 rounded-lg transition duration-300 transform hover:scale-105 flex items-center justify-center"
              >
                <i class="fas fa-shopping-cart text-center"></i>
              </button>
            </div>

            <!-- Notification Area -->
            <div 
              id="notif-<?= $product['id'] ?>" 
              class="absolute bottom-0 left-0 right-0 text-center text-green-600 text-xs py-1 opacity-0 transition-all duration-300"
            >
              ✓ Produk berhasil ditambahkan!
            </div>
          </div>
        </div>
      <?php endforeach; ?>

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
            // Update ikon keranjang di navbar dengan jumlah produk terbaru  
            const cartCountElement = document.querySelector('.cart-count');  
            if (cartCountElement) {  
                cartCountElement.textContent = data.totalItems;  
            }  
            alert('Produk ditambahkan ke keranjang!');  
        }  
    })  
    .catch(error => {  
        console.error('Error:', error);  
    });  
}  
</script>