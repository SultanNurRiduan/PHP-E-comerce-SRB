<?php
require_once __DIR__ . '/../../../auth/session.php';
require_once __DIR__ . '/../../../lib/db.php';
requireAdmin();

$id = $_POST['id'] ?? $_GET['id'] ?? null;
$isModal = ($_SERVER['REQUEST_METHOD'] === 'POST')
  ? (isset($_POST['modal']) && $_POST['modal'] == '1')
  : (isset($_GET['modal']) && $_GET['modal'] == '1');

if (!$id) {
  if ($isModal) {
    echo "Error: ID tidak ditemukan";
    exit;
  }
  header("Location: page.php");
  exit;
}

// Handle POST request lebih dulu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    echo 'success';
    exit;
  }

  $role = $_POST['role'] ?? null;
  if ($role) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$role, $id]);
    echo 'success';
    exit;
  } else {
    echo "Gagal: Role kosong.";
    exit;
  }
}

// Ambil data user jika GET request
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
  echo "Error: User tidak ditemukan";
  exit;
}

// Tampilkan form jika modal
if ($isModal): ?>
  <form method="post" action="/shoe-shop/app/admin/users/edit.php" onsubmit="handleFormSubmit(event, 'edit')">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="modal" value="1">

    <label class="block mb-2">Role:</label>
    <select name="role" class="w-full border p-2 rounded mb-3" required>
      <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
      <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>

    <div class="flex gap-2 mb-4">
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Simpan Perubahan</button>
      <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</button>
    </div>
  </form>

  <form method="post" action="/shoe-shop/app/admin/users/edit.php" onsubmit="handleFormSubmit(event, 'delete')">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="modal" value="1">
    <input type="hidden" name="delete" value="1">
    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Hapus Akun</button>
  </form>
<?php endif; ?>