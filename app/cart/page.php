<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../lib/db.php';

// Ambil user login
$userId = $_SESSION['user']['id'] ?? null;
$productsInCart = [];
$total = 0;

if ($userId) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.quantity, (p.price * c.quantity) AS subtotal
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $productsInCart = $stmt->fetchAll();

    foreach ($productsInCart as $item) {
        $total += $item['subtotal'];
    }

    // Ambil riwayat pesanan user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
} else {
    $orders = [];
}
?>


<div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Keranjang Belanja</h2>

    <?php if (empty($productsInCart)): ?>
        <div class="flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-md text-center">
            <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7.5A1 1 0 007 22h10a1 1 0 001-1.2L17 13M7 13h10M9 22a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2z" />
            </svg>
            <p class="text-gray-600 text-lg font-medium">Keranjang masih kosong</p>
            <p class="text-sm text-gray-500 mt-1">Yuk, mulai belanja sekarang dan temukan produk favoritmu!</p>
            <a href="/shoe-shop/index.php?route=baru" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Belanja Sekarang
            </a>
        </div>
    <?php else: ?>
        <form action="/shoe-shop/index.php?route=checkout" method="POST">
            <div class="space-y-4">
                <?php foreach ($productsInCart as $item): ?>
                    <div class="flex items-center justify-between border rounded-lg p-4 bg-white shadow">
                        <div class="flex items-center gap-10">
                            <img src="/shoe-shop/public/assets/images/<?= htmlspecialchars($item['image']) ?>"
                                alt="<?= htmlspecialchars($item['name']) ?>"
                                class="w-40 h-auto object-cover rounded-md">
                            <input
                                type="checkbox"
                                name="selected_products[]"
                                value="<?= $item['id'] ?>"
                                class="w-5 h-5 accent-green-500 product-check"
                                data-price="<?= $item['price'] ?>"
                                data-quantity="<?= $item['quantity'] ?>">
                            <div class="flex flex-col space-y-2">
                                <h3 class="text-lg font-semibold"><?= htmlspecialchars($item['name']) ?></h3>
                                <div class="flex items-center space-x-2">
                                    <button type="button" class="px-2 py-1 bg-gray-200 text-gray-700 rounded" onclick="updateQuantity(<?= $item['id'] ?>, -1)">–</button>
                                    <span id="qty-<?= $item['id'] ?>" class="text-sm text-gray-700"><?= $item['quantity'] ?></span>
                                    <button type="button" class="px-2 py-1 bg-gray-200 text-gray-700 rounded" onclick="updateQuantity(<?= $item['id'] ?>, 1)">+</button>
                                </div>
                                <p class="text-sm text-gray-500">Harga: Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                                <p class="text-sm font-bold text-green-600">Subtotal: Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></p>
                            </div>
                        </div>

                        <button
                            type="button"
                            onclick="removeFromCart(<?= $item['id'] ?>)"
                            class="text-red-500 hover:text-red-700 text-2xl mr-6">
                            <i class="fa-solid fa-trash"></i>
                        </button>

                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-6 text-right">
                <h3 class="text-xl font-bold">Total: <span id="total-price">0</span></h3>
                <button type="submit"
                    class="mt-4 px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold">
                    Checkout Produk Terpilih
                </button>
            </div>
        </form>
    <?php endif; ?>

    <?php if ($userId): ?>
        <div class="mt-10 bg-white p-6 rounded-xl shadow">
            <h2 class="text-2xl font-bold mb-4">Riwayat Pesanan Anda</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2 text-left">ID</th>
                            <th class="border px-4 py-2 text-left">Total</th>
                            <th class="border px-4 py-2 text-left">Metode</th>
                            <th class="border px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="border px-4 py-2">#<?= $order['id'] ?></td>
                                    <td class="border px-4 py-2">Rp<?= number_format($order['total_price'], 0, ',', '.') ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($order['payment_method']) ?></td>
                                    <td class="border px-4 py-2">
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <span class="text-orange-600 font-semibold">⏳ Menunggu Konfirmasi</span>
                                        <?php elseif ($order['status'] === 'accepted'): ?>
                                            <span class="text-green-600 font-semibold">✅ Diterima</span>
                                        <?php else: ?>
                                            <span class="text-red-600 font-semibold">❌ Ditolak</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">Belum ada pesanan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function formatRupiah(angka) {
        return 'Rp ' + angka.toLocaleString('id-ID');
    }

    function updateTotal() {
        const checkboxes = document.querySelectorAll('.product-check');
        let total = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                const price = parseInt(cb.dataset.price);
                const quantity = parseInt(cb.dataset.quantity);
                total += price * quantity;
            }
        });

        document.getElementById('total-price').textContent = formatRupiah(total);
    }

    // ✅ Dipanggil saat checkbox dipilih/dihapus
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.product-check').forEach(cb => {
            cb.addEventListener('change', updateTotal);
        });

        updateTotal(); // jalankan sekali saat pertama dimuat
    });

    function removeFromCart(productId) {
        if (!confirm("Hapus produk ini dari keranjang?")) return;

        fetch("/shoe-shop/app/cart/add.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `product_id=${productId}&action=remove`
            })
            .then(response => response.json())
            .then(data => {
                console.log("Remove response:", data);

                if (data.success && data.action === "remove") {
                    // 🧹 Hapus kartu produk dari tampilan
                    const card = document.querySelector(`.product-check[value="${productId}"]`)?.closest(".flex.items-center.justify-between");
                    if (card) card.remove();

                    // 🔁 Update total harga
                    updateTotal();

                    // 🔢 Update angka cart di ikon
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) cartCount.textContent = data.totalItems;

                    // 🚨 Reload jika tidak ada produk tersisa (biar muncul tampilan "keranjang kosong")
                    if (document.querySelectorAll('.product-check').length === 0) {
                        location.reload();
                    }
                } else {
                    alert("Gagal menghapus dari keranjang.");
                }
            })
            .catch(err => {
                console.error("Fetch error:", err);
                alert("Terjadi kesalahan.");
            });
    }

    function updateQuantity(productId, change) {
        fetch("/shoe-shop/app/cart/add.php", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: productId,
                    change: change
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 🔢 Update jumlah produk di tampilan
                    document.getElementById(`qty-${productId}`).textContent = data.newQuantity;

                    // 🔄 Update jumlah di checkbox
                    const checkbox = document.querySelector(`.product-check[value="${productId}"]`);
                    if (checkbox) {
                        checkbox.dataset.quantity = data.newQuantity;
                    }

                    // 💰 Update total harga
                    updateTotal();

                    // 🔢 Update angka cart di ikon
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) cartCount.textContent = data.totalItems;
                } else {
                    alert(data.message || 'Gagal mengupdate jumlah');
                }
            });
    }

    async function fetchProducts() {
        showLoading(); // hanya di sini spinner akan tampil
        try {
            const res = await fetch('/shoe-shop/pages/produk.php?q=sepatu');
            const html = await res.text();
            document.getElementById('productContainer').innerHTML = html;
        } finally {
            hideLoading();
        }
    }

    fetchProducts();
</script>