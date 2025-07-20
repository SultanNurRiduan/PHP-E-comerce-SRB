<?php
session_start();
require_once __DIR__ . '/../../../lib/db.php';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['products'], $_POST['total'], $_POST['payment_method'], $_POST['phone'], $_POST['address']) &&
    isset($_FILES['proof'])
) {

    if (!isset($_SESSION['user']['id'])) {
        die("❌ User belum login.");
    }
    $userId = $_SESSION['user']['id'];
    $total = (float) $_POST['total'];
    $paymentMethod = $_POST['payment_method'];
    $products = $_POST['products'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $proofFile = $_FILES['proof'];
    $filename = null;

    // Validasi folder tujuan upload
    $uploadDir = __DIR__ . '/../../../public/assets/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Validasi upload file
    $isCOD = ($paymentMethod === 'cod');

    // Validasi upload file jika bukan COD
    if (!$isCOD && $proofFile['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($proofFile['name'], PATHINFO_EXTENSION);
        $filename = 'proof_' . time() . '_' . uniqid() . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($proofFile['tmp_name'], $destination)) {
            die("❌ Gagal menyimpan file bukti pembayaran.");
        }
    } elseif (!$isCOD) {
        // Jika bukan COD dan file tidak valid
        $errCode = $proofFile['error'];
        die("❌ Bukti pembayaran wajib diupload. Kode error: $errCode");
    } else {
        // COD tidak perlu upload bukti
        $filename = null;
    }


    // Simpan ke tabel orders (dengan phone dan address)
    $stmt = $pdo->prepare("INSERT INTO orders 
        (user_id, phone, address, total_price, payment_method, payment_proof, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$userId, $phone, $address, $total, $paymentMethod, $filename]);

    $orderId = $pdo->lastInsertId();

    // Simpan ke order_items
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($products as $product) {
        $productId = $product['id'];
        $quantity = $product['quantity'];

        $stmtPrice = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmtPrice->execute([$productId]);
        $row = $stmtPrice->fetch();

        if ($row) {
            $price = $row['price'];
            $stmtItem->execute([$orderId, $productId, $quantity, $price]);

            // Kurangi stok
            $updateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $updateStock->execute([$quantity, $productId]);

            // Hapus dari keranjang
            // Hapus dari keranjang session
            if (isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
            }

            // Hapus dari keranjang database jika user login
            $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")->execute([$userId, $productId]);
        }
    }

    // Bersihkan sesi produk yang di-checkout
    unset($_SESSION['selected_checkout_ids']);

    $_SESSION['checkout_success'] = $orderId;
    header("Location: /shoe-shop/index.php?route=cart");
    exit;
}

die("❌ Data tidak valid.");
