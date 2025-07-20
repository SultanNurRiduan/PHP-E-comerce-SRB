<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Shoe Shop</title>
  <link href="../src/output.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/8bdf85b85d.js" crossorigin="anonymous"></script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

  <?php include_once __DIR__ . '/../../components/NavAdmin.php'; ?>

  <main class="bg-gray-100 min-h-screen py-10 px-4">
    <?php include $page; ?>
  </main>

  <?php include_once __DIR__ . '/../../components/Footer.php'; ?>

</body>
</html>
