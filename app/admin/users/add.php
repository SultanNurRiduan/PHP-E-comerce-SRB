<?php
require_once '../../../auth/session.php';
requireAdmin();
require_once '../../../lib/db.php';

$errors = [];
$isModal = ($_SERVER['REQUEST_METHOD'] === 'POST')
  ? (isset($_POST['modal']) && $_POST['modal'] == '1')
  : (isset($_GET['modal']) && $_GET['modal'] == '1');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name     = trim($_POST['name']);
  $email    = trim($_POST['email']);
  $password = $_POST['password'];
  $role     = $_POST['role'];

  if (!$name || !$email || !$password || !$role) {
    $errors[] = "Semua field wajib diisi.";
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email tidak valid.";
  }

  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->execute([$email]);
  if ($stmt->fetch()) {
    $errors[] = "Email sudah terdaftar.";
  }

  if (empty($errors)) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hash, $role]);
    
    if ($isModal) {
      echo "success";
      exit;
    } else {
      header("Location: page.php?success=added");
      exit;
    }
  }
}

// Jika ini adalah request modal, hanya tampilkan form
if ($isModal) {
  ?>
  <?php foreach ($errors as $err): ?>
    <div class="bg-red-100 text-red-700 p-2 rounded mb-2"><?= $err ?></div>
  <?php endforeach; ?>

  <form method="post" action="/shoe-shop/app/admin/users/add.php" onsubmit="handleFormSubmit(event, 'add')">
    <input type="hidden" name="modal" value="1">
    <label class="block mb-2">Nama:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" class="w-full border p-2 rounded mb-3" required>

    <label class="block mb-2">Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="w-full border p-2 rounded mb-3" required>

    <label class="block mb-2">Password:</label>
    <input type="password" name="password" class="w-full border p-2 rounded mb-3" required>

    <label class="block mb-2">Role:</label>
    <select name="role" class="w-full border p-2 rounded mb-3" required>
      <option value="user" <?= (($_POST['role'] ?? '') === 'user') ? 'selected' : '' ?>>User</option>
      <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
    </select>

    <div class="flex gap-2">
      <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Simpan</button>
      <button type="button" onclick="closeAddModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</button>
    </div>
  </form>
  <?php
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Tambah User</title>
  <link href="../../../dist/output.css" rel="stylesheet">
</head>
<body class="p-8 bg-gray-100">
  <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Tambah User</h2>

    <?php foreach ($errors as $err): ?>
      <div class="bg-red-100 text-red-700 p-2 rounded mb-2"><?= $err ?></div>
    <?php endforeach; ?>

    <form method="post">
      <label class="block mb-2">Nama:</label>
      <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" class="w-full border p-2 rounded mb-3" required>

      <label class="block mb-2">Email:</label>
      <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="w-full border p-2 rounded mb-3" required>

      <label class="block mb-2">Password:</label>
      <input type="password" name="password" class="w-full border p-2 rounded mb-3" required>

      <label class="block mb-2">Role:</label>
      <select name="role" class="w-full border p-2 rounded mb-3" required>
        <option value="user" <?= (($_POST['role'] ?? '') === 'user') ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
      </select>

      <div class="flex gap-2">
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Simpan</button>
        <a href="page.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 inline-block">Kembali</a>
      </div>
    </form>
  </div>
</body>
</html>