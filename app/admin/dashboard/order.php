<?php
require_once __DIR__ . '/../../../lib/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$status = $_GET['status'] ?? 'all';

if ($status === 'all') {
    $stmt = $pdo->query("
        SELECT o.*, u.name AS user_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT o.*, u.name AS user_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id
        WHERE o.status = :status
        ORDER BY o.created_at DESC
    ");
    $stmt->execute(['status' => $status]);
}
$orders = $stmt->fetchAll();
?>

<table class="min-w-full border border-gray-300 text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="border px-4 py-2 text-left">ID</th>
            <th class="border px-4 py-2 text-left">User</th>
            <th class="border px-4 py-2 text-left">No HP</th>
            <th class="border px-4 py-2 text-left">Alamat</th>
            <th class="border px-4 py-2 text-left">Total</th>
            <th class="border px-4 py-2 text-left">Metode</th>
            <th class="border px-4 py-2 text-left">Tanggal</th>
            <th class="border px-4 py-2 text-left">Bukti</th>
            <th class="border px-4 py-2 text-left">Status</th>
            <th class="border px-4 py-2 text-left">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr class="hover:bg-gray-50">
                <td class="border px-4 py-2">#<?= $order['id'] ?></td>
                <td class="border px-4 py-2"><?= htmlspecialchars($order['user_name']) ?></td>
                <td class="border px-4 py-2"><?= htmlspecialchars($order['phone'] ?? '-') ?></td>
                <td class="border px-4 py-2 whitespace-pre-line"><?= nl2br(htmlspecialchars($order['address'] ?? '-')) ?></td>
                <td class="border px-4 py-2">Rp<?= number_format($order['total_price'], 0, ',', '.') ?></td>
                <td class="border px-4 py-2"><?= htmlspecialchars($order['payment_method']) ?></td>
                <td class="border px-4 py-2"><?= date('d-m-Y H:i', strtotime($order['created_at'])) ?></td>
                <td class="border px-4 py-2">
                    <?php if ($order['payment_method'] === 'cod'): ?>
                        <span class="italic text-gray-500">COD - tidak ada bukti</span>
                    <?php elseif (!empty($order['payment_proof'])): ?>
                        <img
                            src="/shoe-shop/public/assets/uploads/<?= htmlspecialchars($order['payment_proof']) ?>"
                            alt="Bukti Pembayaran"
                            class="w-32 h-auto rounded shadow cursor-pointer border border-gray-300 hover:border-red-500 hover:scale-105 transition-transform"
                            onclick="openModal('/shoe-shop/public/assets/uploads/<?= htmlspecialchars($order['payment_proof']) ?>')" />
                    <?php else: ?>
                        <span class="text-red-500">Tidak ada bukti</span>
                    <?php endif; ?>
                </td>

                <td class="border px-4 py-2">
                    <?php if ($order['status'] === 'accepted'): ?>
                        <span class="text-green-600 font-semibold">Diterima</span>
                    <?php elseif ($order['status'] === 'rejected'): ?>
                        <span class="text-red-600 font-semibold">Ditolak</span>
                    <?php else: ?>
                        <span class="text-yellow-600 font-semibold">Pending</span>
                    <?php endif; ?>
                </td>
                <td class="border px-4 py-2 space-x-2">
                    <?php if ($order['status'] === 'pending'): ?>
                        <button onclick="handleAction(<?= $order['id'] ?>, 'accept')" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition-colors">
                            ✅ Terima
                        </button>
                        <button onclick="handleAction(<?= $order['id'] ?>, 'reject')" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition-colors">
                            ❌ Tolak
                        </button>
                    <?php else: ?>
                        <span class="text-gray-500 italic">Sudah diproses</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($orders)): ?>
            <tr>
                <td colspan="10" class="text-center py-4 text-gray-500">Tidak ada pesanan.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>




