<?php
require_once __DIR__ . '/../../../lib/db.php';

$type = $_GET['type'] ?? null;

if ($type) {
  $stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name, c.category_type
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE c.category_type = ?
  ");
  $stmt->execute([$type]);
} else {
  $stmt = $pdo->query("
    SELECT p.*, c.name AS category_name, c.category_type
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
  ");
}

$products = $stmt->fetchAll();

$no = 1;
foreach ($products as $row):
?>
<tr class="hover:bg-gray-50">
  <td class="px-4 py-2 border"><?= $no++ ?></td>
  <td class="px-4 py-2 border"><?= htmlspecialchars($row['name']) ?></td>
  <td class="px-4 py-2 border">Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
  <td class="px-4 py-2 border"><?= $row['stock'] ?></td>
  <td class="px-4 py-2 border"><?= htmlspecialchars($row['category_name']) ?></td>
  <td class="px-4 py-2 border"><?= ucfirst($row['category_type']) ?></td>
  <td class="px-4 py-2 border">
    <?php if ($row['image']): ?>
      <img src="/shoe-shop/public/assets/images/<?= htmlspecialchars($row['image']) ?>" class="w-20 h-15 object-cover rounded">
    <?php else: ?>
      <em class="text-gray-400">Tidak ada</em>
    <?php endif; ?>
  </td>
  <td class="px-4 py-2 border">
    <button onclick="openEditModal(<?= $row['id'] ?>)" class="text-sm px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600">
      Edit
    </button>
  </td>
</tr>
<?php endforeach; ?>
<?php if (count($products) === 0): ?>
<tr>
  <td colspan="8" class="text-center py-4">Belum ada produk.</td>
</tr>
<?php endif; ?>
