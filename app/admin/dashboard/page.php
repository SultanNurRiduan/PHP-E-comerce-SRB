<?php
require_once __DIR__ . '/../../../lib/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$status = $_GET['status'] ?? 'all';

$statuses = [
    'all' => '📋 Semua',
    'pending' => '⏳ Pending',
    'accepted' => '✅ Diterima',
    'rejected' => '❌ Ditolak',
];
function countOrdersByStatus($pdo, $statusKey)
{
    if ($statusKey === 'all') {
        return $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = ?");
        $stmt->execute([$statusKey]);
        return $stmt->fetchColumn();
    }
}

if ($status === 'all') {
    $stmt = $pdo->query("
        SELECT o.*, u.name AS user_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("
        SELECT o.*, u.name AS user_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id
        WHERE o.status = :status
        ORDER BY o.created_at DESC
    ");
    $stmt->execute(['status' => $status]);
    $orders = $stmt->fetchAll();
}


?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">📦 Manajemen Pesanan</h2>

        <!-- Tab Status -->
        <div class="mb-6 flex flex-wrap gap-2">
            <?php foreach ($statuses as $key => $label):
                $isActive = ($status === $key); // pastikan ini benar
                $activeClass = $isActive
                    ? 'bg-gray-200 text-gray-800 hover:bg-blue-100'
                    : 'bg-gray-200 text-gray-800 hover:bg-blue-100';
                $count = countOrdersByStatus($pdo, $key);
            ?>
                <a href="?status=<?= $key ?>"
                    class="px-4 py-2 rounded-lg font-semibold text-sm transition <?= $activeClass ?>">
                    <?= $label ?> (<?= $count ?>)
                </a>
            <?php endforeach; ?>
        </div>


        <!-- Tabel Pesanan -->
        <div class="overflow-x-auto">
            <div id="table-container" class="overflow-x-auto text-sm border"></div>
        </div>
    </div>
    
    <!-- Modal Preview -->
    <div id="imageModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-70 flex items-center justify-center">
        <div class="relative bg-white p-4 rounded-lg shadow-xl max-w-3xl w-full mx-4">
            <!-- Tombol Close -->
            <button onclick="closeModal()"
                class="absolute top-2 right-2 text-white bg-red-600 hover:bg-red-700 rounded-full w-8 h-8 flex items-center justify-center text-xl font-bold shadow">
                &times;
            </button>

            <!-- Gambar -->
            <img id="modalImage" src="" alt="Preview" class="max-h-[80vh] w-auto mx-auto rounded mt-4 transition-all duration-300" />
        </div>
    </div>

    <script>
        async function loadTable(status = 'all') {
            const res = await fetch(`/shoe-shop/app/admin/dashboard/order.php?status=${status}`);
            const html = await res.text();
            document.getElementById('table-container').innerHTML = html;
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadTable(); // load all orders awal

            document.querySelectorAll('a[href^="?status="]').forEach(link => {
                link.addEventListener('click', e => {
                    e.preventDefault();
                    const status = new URL(link.href).searchParams.get("status");
                    loadTable(status);
                });
            });
        });

        async function loadTable(status = 'all') {
            const res = await fetch(`/shoe-shop/app/admin/dashboard/order.php?status=${status}`);
            const html = await res.text();
            document.getElementById('table-container').innerHTML = html;
        }

        async function handleAction(orderId, action) {
            const res = await fetch("/shoe-shop/app/admin/dashboard/acc.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `order_id=${orderId}&action=${action}`
            });

            const result = await res.json();
            if (result.success) {
                loadTable(); // reload tabel setelah aksi sukses
            } else {
                alert("❌ Gagal memperbarui status.");
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadTable(); // load all orders awal

            document.querySelectorAll('a[href^="?status="]').forEach(link => {
                link.addEventListener('click', e => {
                    e.preventDefault();
                    const status = new URL(link.href).searchParams.get("status");
                    loadTable(status);
                });
            });
        });

        function openModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modalImg.src = imageSrc;
            modal.classList.remove('hidden');
        }

        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
        }

        // Tutup modal saat klik background
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>


</html>