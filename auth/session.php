<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn(): bool
{
  return isset($_SESSION['user']);
}

/**
 * Cek apakah user adalah admin
 */
function isAdmin(): bool
{
  return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Ambil ID user yang sedang login (atau null jika belum)
 */
function getUserId(): ?int
{
  return $_SESSION['user']['id'] ?? null;
}

/**
 * Paksa login sebelum akses halaman
 */
function requireLogin(): void
{
  if (!isLoggedIn()) {
    ?>
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-gray-100 to-gray-200 px-4">
      <div class="bg-white p-8 rounded-2xl shadow-xl text-center max-w-md w-full">
        <div class="flex justify-center mb-4">
          <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v2m0 4h.01M12 19c3.866 0 7-3.134 7-7S15.866 5 12 5 5 8.134 5 12s3.134 7 7 7z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Akses Ditolak</h2>
        <p class="text-gray-600 mb-6">Anda harus login terlebih dahulu untuk mengakses halaman ini.</p>
        <a href="/shoe-shop/auth/login.php"
          class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-full transition duration-300">
          🔐 Login Sekarang
        </a>
      </div>
    </div>
    <?php
    exit;
  }
}

/**
 * Paksa admin untuk akses halaman
 */
function requireAdmin(): void
{
  if (!isAdmin()) {
    $_SESSION['flash'] = "Hanya admin yang boleh mengakses.";
    header("Location: /shoe-shop/index.php");
    exit;
  }
}

/**
 * (Opsional) Generate token CSRF
 */
function generateCsrfToken(): string
{
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

/**
 * (Opsional) Verifikasi token CSRF
 */
function verifyCsrfToken(string $token): bool
{
  return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
