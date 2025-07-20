<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Cek apakah user sudah login
require_once __DIR__ . '/../../../auth/session.php';
requireLogin(); // ⬅ Wajib login sebelum lanjut

require_once __DIR__ . '/../../../lib/db.php';


// Tampilkan pesan sukses jika ada
$successTransactionId = $_SESSION['checkout_success'] ?? null;
unset($_SESSION['checkout_success']);

// Ambil produk yang dipilih dari session (bukan POST lagi)
$selectedIds = $_SESSION['selected_checkout_ids'] ?? [];

if (empty($selectedIds)) {
    echo '
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="bg-white p-6 rounded-xl shadow-md text-center max-w-md">
            <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M19.07 4.93L4.93 19.07" />
            </svg>
            <h2 class="text-xl font-semibold text-gray-700">Tidak ada produk yang dipilih</h2>
            <p class="text-gray-500 mt-2">Silakan pilih setidaknya satu produk untuk melanjutkan proses ini.</p>
            <a href="index.php?route=cart" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Kembali ke Keranjang
            </a>
        </div>
    </div>';
    exit;
}

$placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($selectedIds);
$selectedProducts = $stmt->fetchAll();

$total = 0;
foreach ($selectedProducts as &$product) {
    $pid = $product['id'];
    $qty = $_SESSION['cart'][$pid] ?? 1;
    $product['quantity'] = $qty;
    $product['subtotal'] = $qty * $product['price'];
    $total += $product['subtotal'];
}

$username = $_SESSION['user']['name'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
</head>

<body class="bg-gray-100 ">
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-xl shadow-lg pt-10">
        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Checkout Produk Terpilih</h2>

        <?php if ($successTransactionId): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded shadow text-center">
                ✅ Pesanan Anda sedang diproses.<br>
                Nomor Transaksi: <strong>#<?= htmlspecialchars($successTransactionId) ?></strong>
            </div>
        <?php endif; ?>

        <form action="/shoe-shop/app/cart/checkout/order.php" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <!-- Kiri: Informasi Pemesan -->
                <div class="space-y-5">
                    <div>
                        <label class="block font-semibold mb-1">Nama Pemesan:</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($username) ?>" readonly
                            class="border p-2 w-full rounded bg-gray-100 cursor-not-allowed">
                    </div>

                    <div>
                        <label for="phone" class="block font-semibold mb-1">Nomor HP:</label>
                        <input type="text" name="phone" id="phone" required
                            class="border p-2 w-full rounded" placeholder="08xxxxxxx">
                    </div>

                    <div>
                        <label for="address" class="block font-semibold mb-1">Alamat Lengkap:</label>
                        <textarea name="address" id="address" required
                            class="border p-2 w-full rounded" rows="4"
                            placeholder="Jl. Nama Jalan, RT/RW, Kecamatan, Kota, Kode Pos"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="payment_method" class="block font-semibold mb-1">Metode Pembayaran:</label>
                        <select name="payment_method" id="payment_method" required class="w-full border p-2 rounded">
                            <option value="">-- Pilih Metode --</option>
                            <option value="transfer_bank">Transfer Bank</option>
                            <option value="dana">Dana</option>
                            <option value="cod">Bayar di Tempat (COD) / Cash </option>
                        </select>
                    </div>

                    <!-- Transfer Bank Options -->
                    <div id="bank-options" class="hidden space-y-2">
                        <label class="block font-medium">Pilih Bank:</label>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="bca" class="bank-checkbox w-5 h-5 accent-blue-600 cursor-pointer" data-bank="bca">
                            <label for="bca" class="flex items-center gap-2 cursor-pointer">
                                <img src="public/assets/images/bca.png" alt="BCA" class="w-50 h-10">
                            </label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="bri" class="bank-checkbox w-5 h-5 accent-blue-600 cursor-pointer" data-bank="bri">
                            <label for="bri" class="flex items-center gap-2 cursor-pointer">
                                <img src="public/assets/images/bri.png" alt="BRI" class="w-auto h-20">
                            </label>
                        </div>
                    </div>

                    <!-- Dana Options -->
                    <div id="dana-options" class="hidden space-y-2">
                        <label class="block font-medium">Dana:</label>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="dana-pay" class="w-5 h-5 dana-checkbox" data-bank="dana">
                            <label for="dana-pay" class="flex items-center gap-2 cursor-pointer">
                                <img src="public/assets/images/Dana.jpeg" class="h-auto w-40" alt="Dana">
                            </label>
                        </div>
                    </div>

                    <!-- MODAL TEMPLATE -->
                    <div id="qrModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                        <div class="bg-white rounded-xl shadow-lg p-6 text-center max-w-sm w-full relative">
                            <h2 id="qr-title" class="text-lg font-semibold mb-4">QR Pembayaran</h2>
                            <img id="qr-image" alt="QR" class="mx-auto w-100 h-140 object-cover">
                            <p id="qr-desc" class="mt-3 text-sm text-gray-700"></p>
                            <button onclick="closeModal()" class="mt-5 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Tutup</button>
                        </div>
                    </div>

                    <div class="mb-4" id="proof-wrapper">
                        <label for="proof" class="block font-semibold mb-2">Upload Bukti Pembayaran:</label>
                        <input type="file" name="proof" id="proof" accept="image/*"
                            class="border p-2 w-full rounded">
                    </div>
                </div>

                <!-- Kanan: Ringkasan Belanja -->
                <div>
                    <h3 class="text-xl font-bold mb-4 border-b pb-2">Ringkasan Belanja</h3>
                    <div class="space-y-4 max-h-[450px] overflow-y-auto pr-2">
                        <?php foreach ($selectedProducts as $item): ?>
                            <div class="border rounded-lg p-3 flex gap-4 shadow-sm">
                                <img src="/shoe-shop/public/assets/images/<?= htmlspecialchars($item['image']) ?>"
                                    alt="<?= htmlspecialchars($item['name']) ?>"
                                    class="w-32 h-auto object-cover rounded">

                                <div class="flex-1">
                                    <h4 class="text-md font-semibold"><?= htmlspecialchars($item['name']) ?></h4>
                                    <p class="text-sm">Jumlah: <?= $item['quantity'] ?> pcs</p>
                                    <p class="text-sm">Harga: Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                                    <p class="text-green-600 font-semibold">Subtotal: Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></p>

                                    <input type="hidden" name="products[<?= $item['id'] ?>][id]" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="products[<?= $item['id'] ?>][quantity]" value="<?= $item['quantity'] ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-6 pt-4 border-t text-right">
                        <h3 class="text-xl font-bold">Total: Rp <?= number_format($total, 0, ',', '.') ?></h3>
                        <input type="hidden" name="total" value="<?= $total ?>">
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center md:text-right">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded font-semibold shadow-lg transition">
                    ✅ Bayar Sekarang
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('payment_method').addEventListener('change', function() {
            const proofWrapper = document.getElementById('proof-wrapper');
            const proofInput = document.getElementById('proof');

            if (this.value === 'cod') {
                proofWrapper.classList.add('hidden');
                proofInput.removeAttribute('required');
            } else {
                proofWrapper.classList.remove('hidden');
                proofInput.setAttribute('required', 'required');
            }
        });

        const paymentMethod = document.getElementById('payment_method');
        const bankOptions = document.getElementById('bank-options');
        const danaOptions = document.getElementById('dana-options');
        const qrModal = document.getElementById('qrModal');
        const qrImage = document.getElementById('qr-image');
        const qrDesc = document.getElementById('qr-desc');
        const qrTitle = document.getElementById('qr-title');

        const qrData = {
            bca: {
                img: "public/assets/images/QRdana.jpg",
                desc: "No. Rek: 1234567890 SRB Garasi Sneakers"
            },
            bri: {
                img: "public/assets/images/QRdana.jpg",
                desc: "No. Rek: 9876543210 SRB Garasi Sneakers"
            },
            dana: {
                img: "public/assets/images/QRdana.jpg",
                desc: "Dana: 081234567890 SRB Garasi Sneakers"
            }
        };

        paymentMethod.addEventListener('change', () => {
            const val = paymentMethod.value;
            bankOptions.classList.toggle('hidden', val !== 'transfer_bank');
            danaOptions.classList.toggle('hidden', val !== 'dana');
        });

        function showModal(bankKey) {
            const data = qrData[bankKey];
            if (data) {
                qrImage.src = data.img;
                qrDesc.textContent = data.desc;
                qrTitle.textContent = "QR " + bankKey.toUpperCase();
                qrModal.classList.remove('hidden');
                qrModal.classList.add('flex');
            }
        }

        function closeModal() {
            qrModal.classList.add('hidden');
            qrModal.classList.remove('flex');
        }

        document.querySelectorAll('.bank-checkbox, .dana-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    // Uncheck semua checkbox lain dalam grup
                    document.querySelectorAll('.bank-checkbox, .dana-checkbox').forEach(other => {
                        if (other !== this) {
                            other.checked = false;
                        }
                    });

                    // Tampilkan modal RQ
                    showModal(this.dataset.bank);
                }
            });
        });
    </script>
</body>

</html>