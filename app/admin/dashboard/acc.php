<?php
require_once __DIR__ . '/../../../lib/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($orderId && in_array($action, ['accept', 'reject'])) {
        $status = $action === 'accept' ? 'accepted' : 'rejected';

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);

        echo json_encode(['success' => true, 'status' => $status]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
