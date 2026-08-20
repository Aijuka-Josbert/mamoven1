<?php
/**
 * One-time migration: converts existing base64-encoded product images
 * (stored directly in the products.image column) into real files under
 * assets/uploads/products/, and updates each row to store the filename
 * instead. Safe to re-run — rows already migrated (not starting with
 * "data:image/") are skipped.
 *
 * Run once:
 *   php scripts/migrate_images_to_files.php
 */

require_once __DIR__ . '/../config/database.php';

$uploadDir = __DIR__ . '/../assets/uploads/products/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$products = $pdo->query("SELECT id, image FROM products WHERE image LIKE 'data:image/%'")->fetchAll();

if (empty($products)) {
    echo "Nothing to migrate — no base64 images found.\n";
    exit(0);
}

echo 'Found ' . count($products) . " product(s) with base64 images to migrate.\n";

$migrated = 0;
$failed = 0;

foreach ($products as $product) {
    $dataUri = $product['image'];

    if (!preg_match('/^data:image\/(\w+);base64,(.+)$/', $dataUri, $matches)) {
        echo "  Skipping product #{$product['id']}: unrecognized image format.\n";
        $failed++;
        continue;
    }

    $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
    $binaryData = base64_decode($matches[2]);

    if ($binaryData === false) {
        echo "  Skipping product #{$product['id']}: could not decode base64 data.\n";
        $failed++;
        continue;
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    if (file_put_contents($uploadDir . $filename, $binaryData) === false) {
        echo "  Skipping product #{$product['id']}: could not write file.\n";
        $failed++;
        continue;
    }

    $update = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
    $update->execute([$filename, $product['id']]);

    $sizeKb = round(strlen($binaryData) / 1024, 1);
    echo "  Migrated product #{$product['id']} -> {$filename} ({$sizeKb} KB)\n";
    $migrated++;
}

echo "\nDone. Migrated: $migrated, Failed: $failed.\n";
echo "Run this again any time — already-migrated rows are automatically skipped.\n";
