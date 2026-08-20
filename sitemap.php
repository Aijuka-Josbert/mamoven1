<?php
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/xml; charset=utf-8');

$staticPages = [
    ['loc' => '/index.php', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/products.php', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/about.php', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => '/contact.php', 'priority' => '0.5', 'changefreq' => 'monthly'],
];

try {
    $products = $pdo->query("SELECT id, created_at FROM products WHERE status = 'active'")->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($staticPages as $page): ?>
    <url>
        <loc><?php echo htmlspecialchars(BASE_URL . $page['loc']); ?></loc>
        <changefreq><?php echo $page['changefreq']; ?></changefreq>
        <priority><?php echo $page['priority']; ?></priority>
    </url>
<?php endforeach; ?>
<?php foreach ($products as $product): ?>
    <url>
        <loc><?php echo htmlspecialchars(BASE_URL . '/product-details.php?id=' . $product['id']); ?></loc>
        <?php if (!empty($product['created_at'])): ?>
        <lastmod><?php echo date('Y-m-d', strtotime($product['created_at'])); ?></lastmod>
        <?php endif; ?>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
<?php endforeach; ?>
</urlset>
