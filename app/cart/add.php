<?php
ini_set('session.cookie_path', '/');
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

// Cek login
require_once __DIR__ . '/../../auth/session.php';
requireLogin();

require_once __DIR__ . '/../../lib/db.php';

$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User belum login']);
    exit;
}

// Fungsi untuk hitung total item user
function getTotalItems($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn() ?: 0;
}

// === REQUEST DARI FETCH JSON (AJAX) ===
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') === 0) {
    $data = json_decode(file_get_contents("php://input"), true);
    $productId = $data['id'] ?? null;
    $change = (int)($data['change'] ?? 0);

    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'ID produk tidak valid']);
        exit;
    }

    // Ambil cart jika ada
    $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $cart = $stmt->fetch();

    if ($cart) {
        $newQty = $cart['quantity'] + $change;
        if ($newQty > 0) {
            $update = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
            $update->execute([$newQty, $userId, $productId]);
        } else {
            $delete = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $delete->execute([$userId, $productId]);
            $newQty = 0;
        }
    } elseif ($change > 0) {
        $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert->execute([$userId, $productId, $change]);
        $newQty = $change;
    } else {
        $newQty = 0;
    }

    echo json_encode([
        'success' => true,
        'newQuantity' => $newQty,
        'totalItems' => getTotalItems($pdo, $userId)
    ]);
    exit;
}

// === REQUEST DARI FORM BIASA ===
$productId = $_POST['product_id'] ?? null;
$action = $_POST['action'] ?? '';

if (!$productId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
    exit;
}

$stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$cart = $stmt->fetch();

switch ($action) {
    case 'add':
        if ($cart) {
            $update = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $update->execute([$userId, $productId]);
        } else {
            $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $insert->execute([$userId, $productId]);
        }
        break;

    case 'remove':
        $delete = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $delete->execute([$userId, $productId]);
        break;

    case 'decrease':
        if ($cart) {
            $newQty = $cart['quantity'] - 1;
            if ($newQty > 0) {
                $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $update->execute([$newQty, $userId, $productId]);
            } else {
                $delete = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $delete->execute([$userId, $productId]);
            }
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenali']);
        exit;
}

$stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$newQty = $stmt->fetchColumn() ?: 0;

echo json_encode([
    'success' => true,
    'action' => $action,
    'newQuantity' => $newQty,
    'totalItems' => getTotalItems($pdo, $userId),
]);
