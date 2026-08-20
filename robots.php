<?php
require_once __DIR__ . '/config/database.php';
header('Content-Type: text/plain; charset=utf-8');
?>
User-agent: *
Disallow: /admin/
Disallow: /api/
Disallow: /auth/
Disallow: /config/
Disallow: /includes/
Disallow: /payments/
Disallow: /scripts/
Disallow: /database/
Disallow: /vendor/
Disallow: /cart.php
Disallow: /orders.php
Disallow: /customer_profile.php
Disallow: /health.php

Sitemap: <?php echo BASE_URL; ?>/sitemap.php
