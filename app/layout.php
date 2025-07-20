<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shoe Shop</title>
  <link href="../src/output.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/8bdf85b85d.js" crossorigin="anonymous"></script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

<?php
$loadingRoutes = ['product', 'cart', 'baru', 'clothes', 'aksesoris']; // isi route yang kamu inginkan
if (in_array($route, $loadingRoutes)) {
  include 'components/loading.php';
}
?>

  <?php include 'components/Navbar.php'; ?>


  <main class="bg-gray-100 min-h-screen ">
    <?php include $page; ?>
  </main>

  <?php include 'components/Footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const loader = document.getElementById('loading-screen');

      // Tampilkan loader sebelum unload (navigasi pindah halaman)
      window.addEventListener('beforeunload', () => {
        loader.classList.remove('hidden');
        loader.style.opacity = '1';
        loader.style.pointerEvents = 'auto';
      });

      // Fungsi global
      window.showLoading = function() {
        loader.classList.remove('hidden');
        loader.style.opacity = '1';
        loader.style.pointerEvents = 'auto';
      }

      window.hideLoading = function() {
        loader.style.opacity = '0';
        loader.style.pointerEvents = 'none';
        setTimeout(() => loader.classList.add('hidden'), 200);
      }

      // Sembunyikan loader saat pertama kali halaman selesai load
      window.addEventListener('load', () => {
        setTimeout(() => {
          hideLoading();
        }, 300); // ← ubah angka ini untuk durasi loading
      });

      // Jika pakai jQuery, aktifkan loading otomatis AJAX
      if (window.jQuery) {
        $(document).ajaxStart(showLoading).ajaxStop(hideLoading);
      }
    });
  </script>
</body>

</html>