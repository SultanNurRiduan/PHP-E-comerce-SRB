<?php
session_start();

function isLoggedIn() {
  return isset($_SESSION['user']);
}

function isAdmin() {
  return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function requireLogin() {
  if (!isLoggedIn()) {
    $_SESSION['flash'] = "Anda harus login terlebih dahulu.";
    header("Location: /shoe-shop/auth/login.php");
    exit;
  }
}

function requireAdmin() {
  if (!isAdmin()) {
    $_SESSION['flash'] = "Akses hanya untuk admin.";
    header("Location: /shoe-shop/index.php");
    exit;
  }
}
