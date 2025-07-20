<?php
require_once __DIR__ . '/../../../lib/db.php';
session_start();

$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    die("❌ ID pesanan tidak ditemukan.");
}

// Ambil semua item dari order
$stmtItems = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
$stmtItems->execute([$orderId]);
$items = $stmtItems->fetchAll();

if (!$items) {
    die("❌ Pesanan tidak ditemukan.");
}

// 1. Kembalikan stok
$stmtUpdateStock = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
foreach ($items as $item) {
    $stmtUpdateStock->execute([$item['quantity'], $item['product_id']]);
}

// 2. Ubah status jadi 'canceled'
$stmtUpdateOrder = $pdo->prepare("UPDATE orders SET status = 'canceled' WHERE id = ?");
$stmtUpdateOrder->execute([$orderId]);

// 3. Redirect balik ke dashboard
header("Location: /shoe-shop/app/admin/orders/index.php");
exit;
