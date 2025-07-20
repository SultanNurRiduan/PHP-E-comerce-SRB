<?php
require_once __DIR__ . '/../../../lib/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Ambil semua produk dan kategori
// Ambil jenis kategori dari URL
$categoryType = $_GET['type'] ?? null;

$stmt = $pdo->query("
  SELECT MIN(id) AS id, name, category_type 
  FROM categories 
  GROUP BY name, category_type 
  ORDER BY name ASC
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Ambil semua produk yang hanya milik category_type tertentu
if ($categoryType) {
  // Jika type disediakan (sneakers/clothes/aksesoris)
  $stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name, c.category_type 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE c.category_type = ?
  ");
  $stmt->execute([$categoryType]);
} else {
  // Jika tidak ada type, tampilkan semua produk
  $stmt = $pdo->query("
    SELECT p.*, c.name AS category_name, c.category_type 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id
  ");
}
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Produk</title>
  <link href="/shoe-shop/src/output.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

  <!-- Tampilan HTML -->
  <div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">
        Daftar Produk <?= $categoryType ? ucfirst($categoryType) : '(Semua Kategori)' ?>
      </h1>
      <!-- Tombol Tambah Produk -->
      <button onclick="openAddModal()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-all">
        + Tambah Produk
      </button>
    </div>
    <div class="mb-4 flex gap-2">
      <!-- Semua -->
      <button id="btn-all" onclick="filterByCategory('')" class="px-4 py-2 rounded text-gray-700 bg-gray-200 hover:bg-blue-100">
        Semua
      </button>

      <?php
      $types = ['sneakers' => 'Sneakers', 'clothes' => 'Clothes', 'aksesoris' => 'Aksesoris'];
      foreach ($types as $key => $label): ?>
        <?php
        $isActive = ($categoryType === $key);
        $btnClass = $isActive
          ? 'bg-gray-200 text-gray-800 hover:bg-blue-100'
          : 'bg-gray-200 text-gray-800 hover:bg-blue-100';
        ?>
        <button id="btn-<?= $key ?>" onclick="filterByCategory('<?= $key ?>')"
          class="px-4 py-2 rounded <?= $btnClass ?>">
          <?= $label ?>
        </button>

      <?php endforeach; ?>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full table-auto border">
        <thead class="bg-gray-200 text-left">
          <tr>
            <th class="px-4 py-2 border">#</th>
            <th class="px-4 py-2 border">Nama</th>
            <th class="px-4 py-2 border">Harga</th>
            <th class="px-4 py-2 border">Stok</th>
            <th class="px-4 py-2 border">brand</th>
            <th class="px-4 py-2 border">Kategori</th>
            <th class="px-4 py-2 border">Gambar</th>
            <th class="px-4 py-2 border">Aksi</th>
          </tr>
        </thead>
        <tbody id="product-table-body">
          <?php if ($products): ?>
            <?php $no = 1;
            foreach ($products as $row): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 border"><?= $no++ ?></td>
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['name']) ?></td>
                <td class="px-4 py-2 border">Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                <td class="px-4 py-2 border"><?= $row['stock'] ?></td>
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['category_name']) ?></td>
                <td class="px-4 py-2 border"><?= ucfirst($row['category_type']) ?>
                </td>

                <td class="px-4 py-2 border">
                  <?php if ($row['image']): ?>
                    <img src="/shoe-shop/public/assets/images/<?= htmlspecialchars($row['image']) ?>" class="w-20 h-15 object-cover rounded">
                  <?php else: ?>
                    <em class="text-gray-400">Tidak ada</em>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-2 border">
                  <button onclick="openEditModal(<?= $row['id'] ?>)" class=" text-sm px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600">
                    Edit
                  </button>
                </td>
              </tr>
            <?php endforeach ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-4">Belum ada produk.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Tambah Produk -->
  <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Tambah Produk</h2>
            <button onclick="closeAddModal()" class="text-gray-500 hover:text-gray-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <form id="addForm" method="post" enctype="multipart/form-data" action="shoe-shop/app/admin/product/add.php">
            <div class="mb-4">
              <label class="block font-semibold mb-2">Nama Produk</label>
              <input type="text" name="name" required class="border w-full p-2 rounded">
            </div>

            <div class="mb-4">
              <label class="block font-semibold mb-2">Harga (Rp)</label>
              <input type="number" name="price" required class="border w-full p-2 rounded">
            </div>

            <div class="mb-4">
              <label class="block font-semibold mb-2">Stok</label>
              <input type="number" name="stock" required class="border w-full p-2 rounded">
            </div>

            <div class="mb-4">
              <label class="block font-semibold mb-2">Deskripsi</label>
              <textarea name="description" class="border w-full p-2 rounded" rows="3"></textarea>
            </div>

            <div class="mb-4">
              <label class="block font-semibold mb-2">Brand</label>
              <select name="category_id" required class="border w-full p-2 rounded">
                <option value="" disabled selected>Pilih Brand</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?= $category['id'] ?>">
                    <?= htmlspecialchars($category['name']) ?>
                    <?= isset($category['category_type']) && $category['category_type'] ? '(' . htmlspecialchars($category['category_type']) . ')' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>


            <div class="mb-4">
              <label class="block font-semibold mb-2">Kategori Tipe</label>
              <select name="category_type" required class="border w-full p-2 rounded">
                <option value="">-- Pilih Tipe Kategori --</option>
                <option value="sneakers">Sneakers</option>
                <option value="clothes">Clothes</option>
                <option value="aksesoris">Aksesoris</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block font-semibold mb-2">Gambar Produk</label>
              <input type="file" name="image" accept="image/*" required class="border w-full p-2 rounded">
            </div>

            <div class="flex justify-end gap-2">
              <button type="button" onclick="closeAddModal()" class="px-4 py-2 border rounded hover:bg-gray-100">
                Batal
              </button>
              <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Simpan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Edit Produk -->
  <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Edit Produk</h2>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <form id="editForm" method="post" enctype="multipart/form-data" action="edit.php">
            <input type="hidden" name="id" id="editId">

            <div class="mb-4">
              <label class="block font-semibold mb-2">Nama Produk</label>
              <input type="text" name="name" id="editName" required class="border w-full p-2 rounded">
            </div>

            <div class="mb-4">
              <label class="block font-semibold mb-2">Harga</label>
              <input type="number" name="price" id="editPrice" required class="border w-full p-2 rounded">
            </div>

            <div class="mb-4">
              <label class="block font-semibold mb-2">Stok</label>
              <input type="number" name="stock" id="editStock" required class="border w-full p-2 rounded">
            </div>

            <div class="mb-4">

              <input type="number" name="category_id" id="editCategoryId" required class="hidden">
            </div>

            <div class="mb-4">
              <label class="block font-semibold mb-2">Deskripsi</label>
              <textarea name="description" id="editDescription" class="border w-full p-2 rounded" rows="3"></textarea>
            </div>

            <div class="mb-4">
              <label class="block font-semibold mb-2">Brand</label>
              <select name="brand" id="editBrand" required class="border w-full p-2 rounded">
                <option value="" disabled>Pilih Brand</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?= htmlspecialchars($category['name']) ?>"
                    <?= (isset($product['brand']) && $product['brand'] === $category['name']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>


            <div class="mb-4">
              <label class="block font-semibold mb-2">Kategori Tipe</label>
              <select name="category_type" id="editCategoryType" required>
                <option value="sneakers">Sneakers</option>
                <option value="clothes">Clothes</option>
                <option value="aksesoris">Aksesoris</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block font-semibold mb-2">Gambar Produk</label>
              <input type="file" name="image" accept="image/*" class="border w-full p-2 rounded">
              <div id="currentImage" class="mt-2"></div>
            </div>

            <div class="flex justify-end gap-2">
              <button type="button" onclick="closeEditModal()" class="px-4 py-2 border rounded hover:bg-gray-100">
                Batal
              </button>
              <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Update
              </button>
              <button type="button" onclick="deleteProduct()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                Hapus
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Fungsi untuk membuka modal tambah
    function openAddModal() {
      document.getElementById('addModal').classList.remove('hidden');
    }

    // Fungsi untuk menutup modal tambah
    function closeAddModal() {
      document.getElementById('addModal').classList.add('hidden');
      document.getElementById('addForm').reset();
    }

    // Fungsi untuk membuka modal edit
    function openEditModal(id) {
      // Ambil data produk via AJAX
      fetch(`/shoe-shop/app/admin/product/get.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const product = data.product;


            document.getElementById('editId').value = product.id;
            document.getElementById('editName').value = product.name;
            document.getElementById('editPrice').value = product.price;
            document.getElementById('editStock').value = product.stock;
            document.getElementById('editDescription').value = product.description;
            document.getElementById('editBrand').value = product.category_name;
            document.getElementById('editCategoryType').value = product.category_type;
            document.getElementById('editCategoryId').value = product.category_id;

            // Tampilkan gambar saat ini
            const currentImageDiv = document.getElementById('currentImage');
            if (product.image) {
              currentImageDiv.innerHTML = `
            <p class="text-sm text-gray-600">Gambar saat ini:</p>
            <img src="/shoe-shop/public/assets/images/${product.image}" alt="Gambar Produk" class="w-32 mt-2 rounded">
          `;
            } else {
              currentImageDiv.innerHTML = '';
            }

            document.getElementById('editModal').classList.remove('hidden');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Gagal mengambil data produk');
        });
    }

    // Fungsi untuk menutup modal edit
    function closeEditModal() {
      document.getElementById('editModal').classList.add('hidden');
      document.getElementById('editForm').reset();
    }

    // Fungsi untuk menghapus produk
    function deleteProduct() {
      if (confirm('Yakin ingin menghapus produk ini?')) {
        const id = document.getElementById('editId').value;

        const formData = new FormData();
        formData.append('id', id);
        formData.append('delete', '1');

        fetch('/shoe-shop/app/admin/product/delete.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Produk berhasil dihapus');
              location.reload();
            } else {
              alert('Gagal menghapus produk: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
          });
      }
    }

    // Tutup modal ketika klik di luar modal
    window.onclick = function(event) {
      const addModal = document.getElementById('addModal');
      const editModal = document.getElementById('editModal');

      if (event.target === addModal) {
        closeAddModal();
      }
      if (event.target === editModal) {
        closeEditModal();
      }
    }

    // Handle form submission dengan AJAX
    document.getElementById('addForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch('/shoe-shop/app/admin/product/add.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Produk berhasil ditambahkan');
            closeAddModal();
            location.reload();
          } else {
            alert('Gagal menambahkan produk: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan');
        });
    });

    document.getElementById('editForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch('/shoe-shop/app/admin/product/edit.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Produk berhasil diperbarui');
            closeEditModal();
            location.reload();
          } else {
            alert('Gagal memperbarui produk: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan');
        });
    });

    function filterByCategory(type) {
      fetch(`/shoe-shop/app/admin/product/fetch_products.php?type=${type}`)
        .then(response => response.text())
        .then(html => {
          document.getElementById('product-table-body').innerHTML = html;
        })
        .catch(error => {
          console.error('Error:', error);
          // Hapus alert kalau hanya warning log
          // alert('Terjadi kesalahan saat mengambil produk');
        });
    }
  </script>

</body>

</html>