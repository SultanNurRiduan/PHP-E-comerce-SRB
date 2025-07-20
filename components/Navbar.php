<?php session_start(); ?>
<?php $currentRoute = $_GET['route'] ?? ''; ?>
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_products'])) {
  $_SESSION['selected_checkout_ids'] = array_map('intval', $_POST['selected_products']);
  header('Location: /shoe-shop/index.php?route=checkout');
  exit;
}

include_once __DIR__ . '/../components/logo.php';

$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $qty) {
    $cartCount += $qty;
  }
}
?>
<!-- Optional: Add custom animation classes in your Tailwind CSS configuration -->
<style>
  @keyframes pulse-soft {

    0%,
    100% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.05);
    }
  }

  .animate-pulse-soft {
    animation: pulse-soft 2s ease-in-out infinite;
  }
</style>
<nav class="bg-white shadow-md sticky top-0 z-[99999] p-2 transition-all duration-300 ease-in-out px-32">
  <div class="container mx-auto flex justify-between items-center">
    <!-- Left Menu -->
    <div class="flex items-center gap-6">
      <?php renderLogo(); ?>

      <!-- Home Link with Enhanced Hover and Active States -->
      <a href="index.php"
        class="relative font-bold group transition-all duration-300 ease-in-out
        <?= $currentRoute === '' ? 'text-red-600' : 'hover:text-red-600' ?>">
        Home
        <span class="absolute top-7 left-0 w-full h-0.5 bg-red-600 scale-x-0 
        group-hover:scale-x-100 transition-transform duration-300 
        <?= $currentRoute === '' ? 'scale-x-100' : 'scale-x-0' ?>"></span>
      </a>

      <a href="index.php?route=baru"
        class="relative font-bold group transition-all duration-300 ease-in-out
        <?= $currentRoute === 'baru' ? 'text-red-600' : 'hover:text-red-600' ?>">
        Baru
        <span class="absolute top-7 left-0 w-full h-0.5 bg-red-600 scale-x-0 
        group-hover:scale-x-100 transition-transform duration-300 
        <?= $currentRoute === 'baru' ? 'scale-x-100' : 'scale-x-0' ?>"></span>
      </a>

      <!-- Product Link with Enhanced Hover and Active States -->
      <a href="index.php?route=product"
        class="relative font-bold group transition-all duration-300 ease-in-out
        <?= $currentRoute === 'product' ? 'text-red-600' : 'hover:text-red-600' ?>">
        Sneakers
        <span class="absolute top-7 left-0 w-full h-0.5 bg-red-600 scale-x-0 
        group-hover:scale-x-100 transition-transform duration-300 
        <?= $currentRoute === 'product' ? 'scale-x-100' : 'scale-x-0' ?>"></span>
      </a>

      <a href="index.php?route=clothes"
        class="relative font-bold group transition-all duration-300 ease-in-out
        <?= $currentRoute === 'clothes' ? 'text-red-600' : 'hover:text-red-600' ?>">
        Clothes
        <span class="absolute top-7 left-0 w-full h-0.5 bg-red-600 scale-x-0 
        group-hover:scale-x-100 transition-transform duration-300 
        <?= $currentRoute === 'clothes' ? 'scale-x-100' : 'scale-x-0' ?>"></span>
      </a>

      <a href="index.php?route=aksesoris"
        class="relative font-bold group transition-all duration-300 ease-in-out
        <?= $currentRoute === 'aksesoris' ? 'text-red-600' : 'hover:text-red-600' ?>">
        Aksesoris
        <span class="absolute top-7 left-0 w-full h-0.5 bg-red-600 scale-x-0 
        group-hover:scale-x-100 transition-transform duration-300 
        <?= $currentRoute === 'aksesoris' ? 'scale-x-100' : 'scale-x-0' ?>"></span>
      </a>

      <!-- Admin Link with Enhanced Hover and Active States -->
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
        <a href="index.php?route=admin/dashboard"
          class="relative font-bold group transition-all duration-300 ease-in-out
          <?= $currentRoute === 'admin/dashboard' ? 'text-red-600' : 'hover:text-red-600' ?>">
          Admin
          <span class="absolute top-7 left-0 w-full h-0.5 bg-red-600 scale-x-0 
          group-hover:scale-x-100 transition-transform duration-300 
          <?= $currentRoute === 'admin/dashboard' ? 'scale-x-100' : 'scale-x-0' ?>"></span>
        </a>
      <?php endif; ?>
    </div>

    <!-- Right Menu -->
    <div class="flex items-center gap-4">
      <form action="index.php" method="GET" class="relative inline-block w-[300px]">
        <input type="hidden" name="route" value="baru" />
        <i id="search-icon"
          class="fa-solid fa-magnifying-glass text-xl cursor-pointer absolute top-1/2 left-[270px] -translate-y-1/2 transition-all duration-300 ease-in-out hover:text-red-600">
        </i>
        <input
          id="search-input"
          name="q"
          type="text"
          class="w-full py-[10px] pl-[40px] pr-[15px] text-[16px] border border-[#E21836] rounded-[5px] outline-none transition-all duration-300 ease-in-out opacity-0 pointer-events-none" />
        <div id="search-results" class="absolute bg-white border border-gray-200 rounded-md mt-1 shadow-lg w-[300px] max-h-[400px] overflow-y-auto hidden z-50"></div>
      </form>



      <!-- Cart Icon with Hover and Scale Effect -->
      <a href="index.php?route=cart" class="relative group transition-all duration-300 ease-in-out transform">
        <i class="fas fa-shopping-cart text-xl text-black transition-colors duration-300 hover:text-red-600"></i>

        <span class="border border-black absolute -top-1 -right-1 bg-white text-black rounded-full w-4 h-4 flex items-center justify-center text-xs cart-count">
          <?php
          $totalItems = 0;
          if (session_status() === PHP_SESSION_NONE) session_start();

          // Jika sudah login, hitung dari database
          if (isset($_SESSION['user']['id'])) {
            require_once __DIR__ . '/../lib/db.php'; // Ubah path sesuai lokasi file navbar
            $stmt = $pdo->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user']['id']]);
            $result = $stmt->fetch();
            $totalItems = $result['total'] ?? 0;
          } elseif (isset($_SESSION['cart'])) {
            // Jika belum login, hitung dari session
            $totalItems = array_sum($_SESSION['cart']);
          }

          echo $totalItems;
          ?>
        </span>
      </a>

      <!-- User Authentication Section with Smooth Transitions -->
      <?php if (!isset($_SESSION['user'])): ?>
        <a href="/shoe-shop/auth/login.php"
          class="group transition-all duration-300 ease-in-out transform hover:scale-110">
          <i class="fas fa-user text-red-600 text-xl 
          group-hover:text-red-600 transition-colors duration-300"></i>
        </a>
      <?php else: ?>
        <div class="flex items-center gap-3">
          <span class="flex items-center gap-2 ">
            <i class="fas fa-user text-xl text-red-600"></i>
            <span class="font-bold animate-pulse-soft">
              <?= htmlspecialchars($_SESSION['user']['name']) ?>
            </span>
          </span>
          <a href="/shoe-shop/auth/logout.php"
            class="inline-block font-bold text-black hover:text-red-600 
            transition-colors duration-300 hover:underline">
            <i class="fas fa-sign-out-alt"></i>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("search-input");
    const searchIcon = document.getElementById("search-icon");
    const searchResults = document.getElementById("search-results");

    if (!searchInput || !searchIcon || !searchResults) return;

    // Toggle input muncul/sembunyi
    searchIcon.addEventListener("click", () => {
      searchIcon.classList.replace("left-[270px]", "left-[10px]");
      searchInput.classList.replace("opacity-0", "opacity-100");
      searchInput.classList.replace("pointer-events-none", "pointer-events-auto");
      searchInput.setAttribute("placeholder", "Search here...");
      searchInput.focus();
    });

    searchInput.addEventListener("blur", () => {
      setTimeout(() => {
        searchResults.classList.add('hidden'); // sembunyikan hasil saat blur
        searchIcon.classList.replace("left-[10px]", "left-[270px]");
        searchInput.classList.replace("opacity-100", "opacity-0");
        searchInput.classList.replace("pointer-events-auto", "pointer-events-none");
        searchInput.setAttribute("placeholder", "");
      }, 200); // beri delay agar klik hasil tetap terdeteksi
    });

    // Debounce untuk input (tidak spam fetch)
    let debounceTimer;
    searchInput.addEventListener("input", () => {
      const query = searchInput.value.trim();

      clearTimeout(debounceTimer);
      if (query.length === 0) {
        searchResults.innerHTML = '';
        searchResults.classList.add('hidden');
        return;
      }

      debounceTimer = setTimeout(() => {
        fetch(`/shoe-shop/app/baru/fetch-search.php?q=${encodeURIComponent(query)}`)
          .then(res => res.json())
          .then(data => {
            if (!Array.isArray(data)) throw new Error("Invalid JSON");

            if (data.length === 0) {
              searchResults.innerHTML = '<div class="p-4 text-sm text-gray-500">Tidak ada hasil.</div>';
            } else {
              searchResults.innerHTML = data.map(product => `
              <div class="p-3 border-b hover:bg-gray-50 cursor-pointer" 
                onclick="location.href='/shoe-shop/app/baru/detail.php?id=${product.id}'">
                <div class="flex items-center gap-3">
                  <img src="/shoe-shop/public/assets/images/${product.image}" class="w-12 h-12 object-cover rounded-md">
                  <div>
                    <div class="font-semibold text-sm">${product.name}</div>
                    <div class="text-xs text-gray-500">${product.category}</div>
                    <div class="text-sm text-red-600 font-bold">Rp ${parseInt(product.price).toLocaleString()}</div>
                  </div>
                </div>
              </div>
            `).join('');
            }

            searchResults.classList.remove('hidden');
          })
          .catch(err => {
            console.error("Search Error:", err);
            searchResults.innerHTML = '<div class="p-4 text-sm text-red-500">Gagal mencari produk.</div>';
            searchResults.classList.remove('hidden');
          });
      }, 300);
    });
  });
</script>