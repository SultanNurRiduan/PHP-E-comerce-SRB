<?php
include_once __DIR__ . '/../components/logo.php';
?>
<footer class="bg-gray-900 text-gray-400 pt-12 pb-6 px-6">
  <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">

    <!-- Brand + Description -->
    <div>
      <?php renderLogo(); ?>
      <p class="text-sm mt-8">Temukan gaya terbaikmu dengan koleksi sepatu terbaru kami. Fashion, kualitas, dan kenyamanan dalam satu tempat.</p>
      <div class="flex mt-4 gap-5">
        <a href="#" class="hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="hover:text-white transition"><i class="fab fa-twitter"></i></a>
        <a href="#" class="hover:text-white transition"><i class="fab fa-instagram"></i></a>
      </div>
    </div>

    <!-- Navigation -->
    <div>
      <h3 class="text-white font-semibold mb-3">Navigasi</h3>
      <ul class="space-y-2 text-sm">
        <li><a href="#" class="hover:text-white transition">Beranda</a></li>
        <li><a href="#" class="hover:text-white transition">Produk</a></li>
        <li><a href="#" class="hover:text-white transition">Tentang Kami</a></li>
        <li><a href="#" class="hover:text-white transition">Kontak</a></li>
      </ul>
    </div>

    <!-- Bantuan -->
    <div>
      <h3 class="text-white font-semibold mb-3">Bantuan</h3>
      <ul class="space-y-2 text-sm">
        <li><a href="#" class="hover:text-white transition">Cara Belanja</a></li>
        <li><a href="#" class="hover:text-white transition">Pengembalian</a></li>
        <li><a href="#" class="hover:text-white transition">Kebijakan Privasi</a></li>
        <li><a href="#" class="hover:text-white transition">Syarat & Ketentuan</a></li>
      </ul>
    </div>

    <!-- Newsletter -->
    <div>
      <h3 class="text-white font-semibold mb-3">Langganan</h3>
      <p class="text-sm mb-3">Dapatkan info terbaru dan penawaran menarik dari kami!</p>
      <form class="flex items-center">
        <input type="email" placeholder="Email Anda" class="w-full px-3 py-2 rounded-l bg-gray-800 text-white placeholder-gray-400 focus:outline-none">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r">Kirim</button>
      </form>
    </div>
  </div>

  <!-- Bottom -->
  <div class="border-t border-gray-700 mt-10 pt-4 text-center text-sm text-gray-500">
    &copy; <?= date('Y'); ?> SRB Garasi Sneakers. All rights reserved.
  </div>
</footer>

<!-- Tambahkan Font Awesome -->
<script src="https://kit.fontawesome.com/yourkitid.js" crossorigin="anonymous"></script>