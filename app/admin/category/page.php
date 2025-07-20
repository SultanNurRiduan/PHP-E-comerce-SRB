<?php
require_once __DIR__ . '/../../../lib/db.php';
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Manajemen Kategori</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
  <div class="max-w-7xl mx-auto bg-white shadow-md p-6 rounded-lg">
    <h1 class="text-2xl font-bold mb-6">Manajemen Kategori</h1>

    <!-- Form Tambah -->
    <form id="categoryForm" class="flex items-center gap-4 mb-6">
      <input type="text" id="categoryName" placeholder="Nama Kategori" required
        class="flex-grow p-2 border border-gray-300 rounded">
      <button type="submit"
        class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
        Tambah
      </button>
    </form>

    <!-- Notifikasi -->
    <div id="messageBox" class="mb-4 hidden px-4 py-3 rounded"></div>

    <!-- Tabel -->
    <table class="w-full table-auto border-collapse">
      <thead>
        <tr class="bg-gray-200">
          <th class="border px-4 py-2">ID</th>
          <th class="border px-4 py-2">Nama</th>
          <th class="border px-4 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody id="categoryTable">
        <?php foreach ($categories as $cat): ?>
          <tr data-id="<?= $cat['id'] ?>" data-name="<?= htmlspecialchars($cat['name']) ?>">
            <td class="border px-4 py-2"><?= $cat['id'] ?></td>
            <td class="border px-4 py-2 category-name"><?= htmlspecialchars($cat['name']) ?></td>
            <td class="border px-4 py-2">
              <button class="text-sm px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600  editBtn">Edit</button>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>

  <!-- MODAL Edit + Delete -->
  <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
      <h2 class="text-xl font-bold mb-4">Edit Kategori</h2>
      <input type="hidden" id="editId">
      <input type="text" id="editName" class="w-full border p-2 mb-4 rounded" placeholder="Nama Kategori">

      <div class="flex justify-between gap-4">
        <button onclick="deleteCategory()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 w-1/2">Hapus</button>
        <button onclick="updateCategory()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-1/2">Simpan</button>
      </div>
      <button onclick="closeModal()" class="mt-4 w-full bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
    </div>
  </div>

  <script>
    const table = document.getElementById('categoryTable');
    let currentId = null;
    const messageBox = document.getElementById('messageBox');

    // Form Tambah
    document.getElementById('categoryForm').addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('categoryName').value.trim();
      if (!name) return;
      handleCategoryAction('add', {
        name
      });
    });

    // Tombol Edit Modal
    table.addEventListener('click', (e) => {
      if (e.target.classList.contains('editBtn')) {
        const row = e.target.closest('tr');
        currentId = row.dataset.id;
        const name = row.dataset.name;
        document.getElementById('editId').value = currentId;
        document.getElementById('editName').value = name;
        openModal();
      }
    });

    function openModal() {
      document.getElementById('editModal').classList.remove('hidden');
      document.getElementById('editModal').classList.add('flex');
    }

    function closeModal() {
      document.getElementById('editModal').classList.add('hidden');
      document.getElementById('editModal').classList.remove('flex');
    }

    function updateCategory() {
      const id = document.getElementById('editId').value;
      const name = document.getElementById('editName').value.trim();
      if (!name) return;
      handleCategoryAction('edit', {
        id,
        name
      });
    }

    function deleteCategory() {
      const id = document.getElementById('editId').value;
      if (!confirm('Yakin ingin menghapus kategori ini?')) return;
      handleCategoryAction('delete', {
        id
      });
    }

    // Handler utama aksi kategori
    function handleCategoryAction(action, payload) {
      let url = '';
      let successMessage = '';

      if (action === 'add') {
        url = '/shoe-shop/app/admin/category/add.php';
        successMessage = '✅ Kategori berhasil ditambahkan!';
      } else if (action === 'edit') {
        url = '/shoe-shop/app/admin/category/update.php';
        successMessage = '✅ Kategori berhasil diubah!';
      } else if (action === 'delete') {
        url = '/shoe-shop/app/admin/category/delete.php';
        successMessage = '✅ Kategori berhasil dihapus!';
      }

      fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        })
        .then(response => {
          if (!response.ok) throw new Error('Network error');
          return response.json();
        })
        .then(result => {
          if (result.success) {
            if (action === 'add') {
              const row = document.createElement('tr');
              row.setAttribute('data-id', result.id);
              row.setAttribute('data-name', payload.name);
              row.innerHTML = `
          <td class="border px-4 py-2">${result.id}</td>
          <td class="border px-4 py-2 category-name">${payload.name}</td>
          <td class="border px-4 py-2">
            <button class="text-sm px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600 editBtn">Edit</button>
          </td>`;
              table.prepend(row);
              document.getElementById('categoryName').value = '';
            }

            if (action === 'edit') {
              const row = table.querySelector(`tr[data-id="${payload.id}"]`);
              row.querySelector('.category-name').textContent = payload.name;
              row.setAttribute('data-name', payload.name);
              closeModal();
            }

            if (action === 'delete') {
              const row = table.querySelector(`tr[data-id="${payload.id}"]`);
              if (row) row.remove();
              closeModal();
            }

            alert(successMessage);
          } else {
            // Tampilkan pesan error di modal
            alert('❌ ' + (result.message || 'Terjadi kesalahan.'));
          }
        })
        .catch(error => {
          console.error(error);
          alert('Kategori ini sedang digunakan oleh produk dan tidak bisa dihapus.');
        });
    }
  </script>

</body>

</html>