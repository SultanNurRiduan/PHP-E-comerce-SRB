<?php
$banners = [
    ['img' => 'pages9.webp', 'link' => 'index.php?route=baru',      'title' => 'Produk Terbaru'],
    ['img' => 'banner5.jpg', 'link' => 'index.php?route=sneakers',  'title' => 'Sneakers Keren'],
    ['img' => 'banner5.png', 'link' => 'index.php?route=clothes',   'title' => 'Fashion'],
    ['img' => 'banner7.png', 'link' => 'index.php?route=aksesoris', 'title' => 'Aksesoris'],
];
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <?php foreach ($banners as $index => $banner): ?>
            <a href="<?= htmlspecialchars($banner['link']) ?>" class="block group">
                <img src="/shoe-shop/public/assets/images/<?= htmlspecialchars($banner['img']) ?>"
                    alt="<?= htmlspecialchars($banner['title']) ?>"
                    class="w-full h-auto object-cover rounded-lg shadow">
                <div class="text-center p-4 mt-4">
                    <h1 class="text-2xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($banner['title']) ?></h1>
                    <p class="text-gray-600 mb-2">Temukan Produk anda di SRB garasi Sneakers.</p>
                    <p class="font-semibold text-red-500 hover:text-red-600 hover:underline transition-all duration-300 ease-in-out group-hover:translate-x-1">
                        Shop Now →
                    </p>

                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>