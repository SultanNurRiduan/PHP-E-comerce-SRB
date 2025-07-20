<?php
require_once __DIR__ . '/../../../auth/session.php';
requireAdmin();
require_once __DIR__ . '/../../../lib/db.php';

// Ambil semua user
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

// Check for success message
$success = $_GET['success'] ?? '';
?>

<!DOCTYPE html>
<html>

<head>
  <title>Manajemen User</title>
  <link href="../../../public/assets/css/style.css" rel="stylesheet">
  <link href="../../../dist/output.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
  <div class=" max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <div class="flex justify-between">
      <h2 class="text-2xl font-bold mb-4">Daftar Pengguna</h2>

      <?php if ($success === 'added'): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">User berhasil ditambahkan!</div>
      <?php elseif ($success === 'updated'): ?>
        <div class="bg-blue-100 text-blue-700 p-3 rounded mb-4">User berhasil diupdate!</div>
      <?php endif; ?>

      <button onclick="openAddModal()" class="bg-green-500 text-white px-4 py-2 rounded mb-4 inline-block hover:bg-green-600">+ Tambah User</button>
    </div>
    <table class="w-full border">
      <thead class="bg-gray-200">
        <tr>
          <th class="p-2 border">Nama</th>
          <th class="p-2 border">Email</th>
          <th class="p-2 border">Role</th>
          <th class="p-2 border">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td class="p-2 border"><?= htmlspecialchars($user['name']) ?></td>
            <td class="p-2 border"><?= htmlspecialchars($user['email']) ?></td>
            <td class="p-2 border"><?= $user['role'] ?></td>
            <td class="p-2 border flex gap-2">
              <button onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>', '<?= $user['role'] ?>')" class="text-sm px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600">Edit</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>

  <!-- Modal Tambah User -->
  <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold">Tambah User</h3>
          <button onclick="closeAddModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>

        <div id="addModalContent">
          <!-- Content will be loaded here -->
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Edit User -->
  <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold">Edit User: <span id="editUserName"></span></h3>
          <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>

        <div id="editModalContent">
          <!-- Content will be loaded here -->
        </div>
      </div>
    </div>
  </div>

  <script>
    // Fungsi untuk modal tambah user
    function openAddModal() {
      document.getElementById('addModal').classList.remove('hidden');
      loadAddForm();
    }

    function closeAddModal() {
      document.getElementById('addModal').classList.add('hidden');
    }

    // Fungsi untuk modal edit user
    function openEditModal(id, name, role) {
      document.getElementById('editUserName').textContent = name;
      document.getElementById('editModal').classList.remove('hidden');
      loadEditForm(id);
    }

    function closeEditModal() {
      document.getElementById('editModal').classList.add('hidden');
    }

    // Load form content via AJAX
    function loadAddForm() {
      fetch('/shoe-shop/app/admin/users/add.php?modal=1') // Hapus 'shoe-shop/app/admin/users/'
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.text();
        })
        .then(html => {
          document.getElementById('addModalContent').innerHTML = html;
        })
        .catch(error => {
          console.error('Error loading add form:', error);
          document.getElementById('addModalContent').innerHTML = '<div class="text-red-500">Error loading form. Please try again.</div>';
        });
    }

    function loadEditForm(id) {
      fetch(`/shoe-shop/app/admin/users/edit.php?id=${id}&modal=1`)
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.text();
        })
        .then(html => {
          document.getElementById('editModalContent').innerHTML = html;
        })
        .catch(error => {
          console.error('Error loading edit form:', error);
          document.getElementById('editModalContent').innerHTML = '<div class="text-red-500">Error loading form. Please try again.</div>';
        });
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

    // Handle form submission via AJAX
    function handleFormSubmit(event, formType) {
      event.preventDefault();
      const form = event.target;
      const formData = new FormData(form);

      // Konfirmasi khusus untuk delete
      if (formType === 'delete') {
        if (!confirm('Yakin ingin menghapus akun ini?')) {
          return;
        }
      }

      fetch(form.action, {
          method: 'POST',
          body: formData
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.text();
        })
        .then(result => {
          if (result.trim() === 'success') {
            if (formType === 'add') {
              closeAddModal();
              alert('✅ User berhasil ditambahkan!');
              window.location.href = '/shoe-shop/index.php?route=admin/users';
            } else if (formType === 'edit') {
              closeEditModal();
              alert('✅ User berhasil diupdate!');
              window.location.href = '/shoe-shop/index.php?route=admin/users';
            } else if (formType === 'delete') {
              closeEditModal();
              alert('✅ User berhasil dihapus!');
              window.location.href = '/shoe-shop/index.php?route=admin/users';
            }
          } else {
            if (formType === 'add') {
              document.getElementById('addModalContent').innerHTML = result;
            } else if (formType === 'edit' || formType === 'delete') {
              document.getElementById('editModalContent').innerHTML = result;
            }
          }
        })
        .catch(error => {
          console.error('Error submitting form:', error);
          const errorMsg = '<div class="text-red-500">Error submitting form. Please try again.</div>';
          if (formType === 'add') {
            document.getElementById('addModalContent').innerHTML = errorMsg;
          } else if (formType === 'edit' || formType === 'delete') {
            document.getElementById('editModalContent').innerHTML = errorMsg;
          }
        });
    }
  </script>
</body>

</html>