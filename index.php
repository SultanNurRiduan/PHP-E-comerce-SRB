<?php
$route = $_GET['route'] ?? '';


// ✅ Set layout default lebih awal
$layout = __DIR__ . '/app/layout.php';

switch ($route) {
  case 'baru':
    $page = __DIR__ . '/app/baru/page.php';
    break;
  case 'aksesoris':
    $page = __DIR__ . '/app/aksesoris/page.php';
    break;
    case 'clothes':
    $page = __DIR__ . '/app/clothes/page.php';
    break;
  case 'product':
    $page = __DIR__ . '/app/product/page.php';
    break;
  case 'love':
    $page = __DIR__ . '/app/love/page.php';
    break;
  case 'cart':
    $page = __DIR__ . '/app/cart/page.php';
    break;
  case 'checkout':
    $page = __DIR__ . '/app/cart/checkout/page.php';
    break;
  case 'admin/dashboard':
    $page = __DIR__ . '/app/admin/dashboard/page.php';
    $layout = __DIR__ . '/app/admin/layout.php';
    break;
  case 'admin/products':
    $page = __DIR__ . '/app/admin/product/page.php';
    $layout = __DIR__ . '/app/admin/layout.php';
    break;
  case 'admin/users':
    $page = __DIR__ . '/app/admin/users/page.php';
    $layout = __DIR__ . '/app/admin/layout.php';
    break;
  case 'admin/categories':
    $page = __DIR__ . '/app/admin/category/page.php';
    $layout = __DIR__ . '/app/admin/layout.php';
    break;
  default:
    $page = __DIR__ . '/app/home/page.php';
    break;
}
// ✅ Include layout (yang akan memuat halaman sesuai variabel $page)
include $layout;
