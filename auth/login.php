<?php
session_start();
require_once '../lib/db.php';

$errors = [];

// Proses form login / register
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  // === REGISTER ===
  if (isset($_POST['action']) && $_POST['action'] === 'register') {
    $name = trim($_POST['name'] ?? '');
    $confirm = $_POST['confirm'] ?? '';
    $role = 'user';

    if (!$name || !$email || !$password || !$confirm) {
      $errors[] = "Semua field wajib diisi.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = "Format email tidak valid.";
    }
    if ($password !== $confirm) {
      $errors[] = "Konfirmasi password tidak cocok.";
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

      $_SESSION['flash'] = "Pendaftaran berhasil. Silakan login.";
      header("Location: login.php");
      exit;
    }

    // === LOGIN ===
  } else {
    if (!$email || !$password) {
      $errors[] = "Email dan password wajib diisi.";
    }

    if (empty($errors)) {
      $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
      $stmt->execute([$email]);
      $user = $stmt->fetch();

      if ($user && password_verify($password, $user['password'])) {
        // Simpan data user ke session
        $_SESSION['user'] = [
          'id' => $user['id'],
          'name' => $user['name'],
          'email' => $user['email'],
          'role' => $user['role']
        ];

        // Jika ada cart di session, pindahkan ke database
        if (!empty($_SESSION['cart'])) {
          foreach ($_SESSION['cart'] as $productId => $qty) {
            $stmt = $pdo->prepare("
              INSERT INTO cart (user_id, product_id, quantity)
              VALUES (?, ?, ?)
              ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
            ");
            $stmt->execute([$user['id'], $productId, $qty]);
          }
          unset($_SESSION['cart']);
        }

        // Redirect ke halaman tujuan (jika ada), atau ke dashboard
        if (isset($_SESSION['redirect_after_login'])) {
          $redirect = $_SESSION['redirect_after_login'];
          unset($_SESSION['redirect_after_login']);
          header("Location: $redirect");
          exit;
        }

        $redirect = ($user['role'] === 'admin')
          ? "/shoe-shop/index.php?route=admin/dashboard"
          : "/shoe-shop/index.php";

        header("Location: $redirect");
        exit;
      } else {
        $errors[] = "Email atau password salah.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Login & Register</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="./src/output.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    body {
      background-image: url('../public/assets/images/Background.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    .container.active .form-box {
      right: 50%;
    }

    .container.active .form-box.register {
      visibility: visible;
    }

    .container.active .toggle-bg {
      left: 50%;
    }

    .container.active .toggle-panel.toggle-left {
      left: -50%;
      transition-delay: 0.6s;
    }

    .container.active .toggle-panel.toggle-right {
      right: 0;
      transition-delay: 1.2s;
    }

    /* Mobile Responsive */
    @media screen and (max-width: 650px) {
      .container {
        height: calc(100vh - 40px);
      }

      .form-box {
        bottom: 0;
        width: 100%;
        height: 70%;
      }

      .container.active .form-box {
        right: 0;
        bottom: 30%;
      }

      .toggle-bg {
        left: 0;
        top: -270%;
        width: 100%;
        height: 300%;
        border-radius: 20vw;
      }

      .container.active .toggle-bg {
        left: 0;
        top: 70%;
      }

      .toggle-panel {
        width: 100%;
        height: 30%;
      }

      .toggle-panel.toggle-left {
        top: 0;
      }

      .toggle-panel.toggle-right {
        right: 0;
        bottom: -30%;
      }

      .container.active .toggle-panel.toggle-left {
        left: 0;
        top: -30%;
      }

      .container.active .toggle-panel.toggle-right {
        bottom: 0;
      }
    }

    @media screen and (max-width: 400px) {
      .form-box {
        padding: 1.25rem;
      }

      .toggle-panel h1 {
        font-size: 1.875rem;
      }
    }
  </style>
</head>

<body class="font-poppins min-h-screen flex items-center justify-center">
  <div class="absolute inset-0 bg-black bg-opacity-40 z-0"></div>
  <!-- Main Container -->
  <div id="formContainer" class="container relative z-10 w-[850px] h-[550px] bg-white rounded-3xl shadow-2xl overflow-hidden transition-all duration-[1800ms] ease-in-out mx-5">

    <!-- Login Form -->
    <div class="form-box login absolute right-0 w-1/2 h-full bg-white flex items-center justify-center text-gray-800 text-center p-10 z-10 transition-all duration-600 ease-in-out delay-[1200ms]">
      <form method="post" class="w-full">
        <h1 class="text-4xl font-semibold mb-6 -mt-2">Login</h1>


        <?php if (isset($_SESSION['flash'])): ?>
          <div class="bg-green-100 text-green-700 p-2 rounded mb-2 text-sm w-full">
            <?= $_SESSION['flash'] ?>
          </div>
          <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Error Messages -->
        <?php foreach ($errors as $err): ?>
          <div class="bg-red-100 text-red-700 p-2 rounded mb-2 text-sm w-full"><?= $err ?></div>
        <?php endforeach; ?>

        <!-- Email Input -->
        <div class="relative my-7">
          <input type="email" name="email" placeholder="Email" required
            class="w-full py-3 px-5 pr-12 bg-gray-100 rounded-lg border-none outline-none text-base text-gray-800 font-medium placeholder-gray-500">
          <i class="bx bxs-envelope absolute right-5 top-1/2 transform -translate-y-1/2 text-xl"></i>
        </div>

        <!-- Password Input -->
        <div class="relative my-7">
          <input type="password" name="password" placeholder="Password" required
            class="w-full py-3 px-5 pr-12 bg-gray-100 rounded-lg border-none outline-none text-base text-gray-800 font-medium placeholder-gray-500">
          <i class="bx bxs-lock-alt absolute right-5 top-1/2 transform -translate-y-1/2 text-xl"></i>
        </div>


        <!-- Login Button -->
        <button type="submit" class="w-full h-12 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-lg transition-colors duration-300">
          Login
        </button>

        <!-- Social Login -->

      </form>
    </div>

    <!-- Register Form -->
    <div class="form-box register absolute right-0 w-1/2 h-full bg-white flex items-center justify-center text-gray-800 text-center p-10 z-10 invisible transition-all duration-600 ease-in-out delay-[1200ms]">
      <form method="post" class="w-full">
        <input type="hidden" name="action" value="register" />
        <h1 class="text-4xl font-semibold mb-6 -mt-2">Registration</h1>

        <!-- Name Input -->
        <div class="relative my-7">
          <input type="text" name="name" placeholder="Nama Lengkap" required
            class="w-full py-3 px-5 pr-12 bg-gray-100 rounded-lg border-none outline-none text-base text-gray-800 font-medium placeholder-gray-500">
          <i class="bx bxs-user absolute right-5 top-1/2 transform -translate-y-1/2 text-xl"></i>
        </div>

        <!-- Email Input -->
        <div class="relative my-7">
          <input type="email" name="email" placeholder="Email" required
            class="w-full py-3 px-5 pr-12 bg-gray-100 rounded-lg border-none outline-none text-base text-gray-800 font-medium placeholder-gray-500">
          <i class="bx bxs-envelope absolute right-5 top-1/2 transform -translate-y-1/2 text-xl"></i>
        </div>

        <!-- Password Input -->
        <div class="relative my-7">
          <input type="password" name="password" placeholder="Password" required
            class="w-full py-3 px-5 pr-12 bg-gray-100 rounded-lg border-none outline-none text-base text-gray-800 font-medium placeholder-gray-500">
          <i class="bx bxs-lock-alt absolute right-5 top-1/2 transform -translate-y-1/2 text-xl"></i>
        </div>

        <!-- Confirm Password Input -->
        <div class="relative my-7">
          <input type="password" name="confirm" placeholder="Konfirmasi Password" required
            class="w-full py-3 px-5 pr-12 bg-gray-100 rounded-lg border-none outline-none text-base text-gray-800 font-medium placeholder-gray-500">
          <i class="bx bxs-lock-alt absolute right-5 top-1/2 transform -translate-y-1/2 text-xl"></i>
        </div>

        <!-- Register Button -->
        <button type="submit" class="w-full h-12 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-lg transition-colors duration-300">
          Register
        </button>


      </form>
    </div>

    <!-- Toggle Animation Panel -->
    <div class="toggle-box absolute w-full h-full">
      <!-- Animated Background -->
      <div class="toggle-bg absolute -left-[250%] w-[300%] h-full bg-red-600 rounded-[150px] z-20 transition-all duration-[1800ms] ease-in-out"></div>
      <!-- Welcome Panel (Left) -->
      <div class="toggle-panel toggle-left absolute left-0 w-1/2 h-full text-white flex flex-col justify-center items-center z-20 transition-all duration-600 ease-in-out delay-[1200ms]">
        <h1 class="text-4xl font-semibold mb-4">Hello, Welcome!</h1>
        <p class="text-sm mb-5">Don't have an account?</p>
        <button onclick="toggleMode()" class="w-40 h-12 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-red-600 transition-colors duration-300">
          Register
        </button>
      </div>

      <!-- Welcome Back Panel (Right) -->
      <div class="toggle-panel toggle-right absolute -right-1/2 w-1/2 h-full text-white flex flex-col justify-center items-center z-20 transition-all duration-600 ease-in-out delay-600">
        <h1 class="text-4xl font-semibold mb-4">Welcome Back!</h1>
        <p class="text-sm mb-5">Already have an account?</p>
        <button onclick="toggleMode()" class="w-40 h-12 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-red-600 transition-colors duration-300">
          Login
        </button>
      </div>
    </div>
  </div>

  <script>
    function toggleMode() {
      const container = document.getElementById('formContainer');
      container.classList.toggle('active');
    }
  </script>
</body>

</html>