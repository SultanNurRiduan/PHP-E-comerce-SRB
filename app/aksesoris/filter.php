<?php
require_once __DIR__ . '/../../lib/db.php';

// Ambil kategori yang termasuk kategori_type accessories
$stmt = $pdo->prepare("SELECT id, name FROM categories WHERE category_type = ?");
$stmt->execute(['aksesoris']);
$categories = $stmt->fetchAll();
?>

<div class="sticky top-[120px] p-4">
  <h2 class="font-bold text-lg mb-4">Filter Aksesoris</h2>
  <form id="filter-form" class="space-y-2">
    <?php foreach ($categories as $cat): ?>
      <div class="flex items-center">
        <input
          type="checkbox"
          type="checkbox"
          name="category[]"
          value="<?= $cat['id'] ?>"
          class="mr-2 category-checkbox"
          id="cat-<?= $cat['id'] ?>"
        >
        <label for="cat-<?= $cat['id'] ?>" class="text-gray-700">
          <?= htmlspecialchars($cat['name']) ?>
        </label>
      </div>
    <?php endforeach; ?>
  </form>
</div>
