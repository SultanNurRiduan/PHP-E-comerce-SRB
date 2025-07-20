<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../lib/db.php';
$keyword = $_GET['q'] ?? '';

$categoryType = 'clothes';

$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE c.category_type = :category_type";

$params = ['category_type' => $categoryType];

if ($keyword) {
  $sql .= " AND (p.name LIKE :keyword OR c.name LIKE :keyword)";
  $params['keyword'] = '%' . $keyword . '%';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Produk Clothes</title>
    <link href="../../dist/output.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row min-h-screen">
            <!-- Sidebar -->
            <aside class="w-full md:w-1/4 border-r border-black bg-gray-100">
                <?php include_once __DIR__ . '/filter.php'; ?>
            </aside>

            <!-- Produk -->
            <main class="w-full p-4">
                <h1 class="font-bold text-3xl my-8 text-center">
                    <?php if ($keyword): ?>
                        Hasil pencarian untuk: "<span class="text-red-600"><?= htmlspecialchars($keyword) ?></span>"
                    <?php else: ?>
                        Semua Produk Clothes
                    <?php endif; ?>
                </h1>
                <div id="product-list">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <?php include_once __DIR__ . '/../../components/cart.php'; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const checkboxes = document.querySelectorAll('.category-checkbox');
            const productList = document.getElementById('product-list');
            const searchInput = document.getElementById('search-input');

            function fetchFilteredProducts() {
                const selected = [...checkboxes]
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                const params = new URLSearchParams();
                selected.forEach(id => params.append('category[]', id));
                params.append('category_type', 'clothes'); // 👈 khusus clothes

                const keyword = (searchInput?.value || "<?= htmlspecialchars($keyword) ?>").trim();
                if (keyword) params.append('q', keyword);

                fetch("/shoe-shop/app/clothes/fetch.php?" + params.toString())
                    .then(res => res.text())
                    .then(html => {
                        productList.innerHTML = html;
                    })
                    .catch(() => {
                        productList.innerHTML = "<p class='text-red-600'>Gagal memuat produk.</p>";
                    });
            }

            checkboxes.forEach(cb => cb.addEventListener('change', fetchFilteredProducts));


            fetchFilteredProducts();
        });

        function addToCart(productId) {
            fetch("/shoe-shop/app/cart/add.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `product_id=${productId}&action=add`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.action === 'added') {
                        const notif = document.getElementById("notif-" + productId);
                        if (notif) {
                            notif.classList.remove("hidden");
                            setTimeout(() => notif.classList.add("hidden"), 2500);
                        }
                    } else {
                        alert("Gagal menambahkan: " + (data.message || ""));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Terjadi kesalahan.");
                });
        }
    </script>

</body>

</html>