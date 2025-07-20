<?php if (isset($_SESSION['flash'])): ?>
  <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
    <?= $_SESSION['flash']; unset($_SESSION['flash']); ?>
  </div>
<?php endif; ?>
