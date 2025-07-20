<?php session_start(); ?>
<?php $currentRoute = $_GET['route'] ?? ''; ?>
<?php include 'components/logo.php'; ?>

<nav class="bg-white shadow-md sticky top-0 z-50 p-2 px-32">
  <div class="container mx-auto flex justify-between items-center">
    <!-- Left Menu -->
    <div class="flex items-center gap-6">
      <?php renderLogo(); ?>
      
      <!-- Dashboard Link -->
      <a href="/shoe-shop/index.php?route=admin/dashboard"
        class="relative group font-bold transition-all duration-300 ease-in-out
        <?= $currentRoute === 'admin/dashboard' ? 'text-red-600' : 'hover:text-red-600' ?>"
      >
        Dashboard
        <span class="absolute top-7 left-0 w-full h-0.5 bg-red-600 scale-x-0 
        group-hover:scale-x-100 transition-transform duration-300 
        <?= $currentRoute === 'admin/dashboard' ? 'scale-x-100' : 'scale-x-0' ?>"></span>
      </a>

      <!-- Products Link -->
      <a href="/shoe-shop/index.php?route=admin/products"
        class="relative group font-bold  transition-all duration-300 ease-in-out
        <?= $currentRoute === 'admin/products' ? 'text-red-600' : 'hover:text-red-600' ?>"
      >
        Produk
        <span class="absolute top-7 left-0 w-full h-0.5 bg-red-600 scale-x-0 
        group-hover:scale-x-100 transition-transform duration-300 
        <?= $currentRoute === 'admin/products' ? 'scale-x-100' : 'scale-x-0' ?>"></span>
      </a>

      <!-- Users Link -->
      <a href="/shoe-shop/index.php?route=admin/users"
        class="relative group font-bold  transition-all duration-300 ease-in-out
        <?= $currentRoute === 'admin/users' ? 'text-red-600' : 'hover:text-red-600' ?>"
      >
        Users
        <span class="absolute top-7 left-0 w-full h-0.5 bg-red-600 scale-x-0 
        group-hover:scale-x-100 transition-transform duration-300 
        <?= $currentRoute === 'admin/users' ? 'scale-x-100' : 'scale-x-0' ?>"></span>
      </a>

      <a href="/shoe-shop/index.php?route=admin/categories"
        class="relative group font-bold  transition-all duration-300 ease-in-out
        <?= $currentRoute === 'admin/categories' ? 'text-red-600' : 'hover:text-red-600' ?>"
      >
        Kategori
        <span class="absolute top-7 left-0 w-full h-0.5 bg-red-600 scale-x-0 
        group-hover:scale-x-100 transition-transform duration-300 
        <?= $currentRoute === 'admin/categories' ? 'scale-x-100' : 'scale-x-0' ?>"></span>
      </a>

      <!-- Pengguna Link -->
      <a href="/shoe-shop/index.php?route=dashboard"
        class="relative group font-bold  transition-all duration-300 ease-in-out
        <?= $currentRoute === 'dashboard' ? 'text-red-600' : 'hover:text-red-600' ?>"
      >
        Pengguna
        <span class="absolute top-7 left-0 w-full h-0.5 bg-red-600 scale-x-0 
        group-hover:scale-x-100 transition-transform duration-300 
        <?= $currentRoute === 'dashboard' ? 'scale-x-100' : 'scale-x-0' ?>"></span>
      </a>
    </div>

    <!-- Right Menu -->
    <div class="flex items-center gap-4">
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
        <div class="flex items-center gap-3 group">
          <span class="flex items-center gap-2 transition-colors duration-300">
            <i class="fas fa-crown text-2xl transition-transform"></i>
            <span class="font-bold animate-pulse-soft">
              <?= htmlspecialchars($_SESSION['user']['name']) ?> (Admin)
            </span>
          </span>
          
          <a href="/shoe-shop/auth/logout.php"
            class="font-bold hover:text-red-600 
            transition-colors duration-300 hover:underline"
          >
            <i class="fas fa-sign-out-alt"></i>
          </a>
        </div>
      <?php else: ?>
        <a href="/shoe-shop/index.php"
          class="inline-block text-blue-500 hover:text-blue-700 
          transition-colors duration-300 font-bold group"
        >
          Kembali ke Home
          <span class="block h-0.5 bg-blue-500 scale-x-0 group-hover:scale-x-100 
          transition-transform duration-300"></span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Optional: Custom Animation Styles -->
<style>
  @keyframes pulse-soft {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
  }
  .animate-pulse-soft {
    animation: pulse-soft 2s ease-in-out infinite;
  }
</style>