<?php
include_once '../config/database.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.status = 'active' AND p.featured = 1 
                          ORDER BY p.created_at DESC 
                          LIMIT 6");
    $stmt->execute();
    $products = $stmt->fetchAll();

    // Format products
    foreach ($products as &$product) {
        $product['price'] = (float)$product['price'];
        $product['image'] = $product['image'] ?: 'assets/images/placeholder.jpg';
    }

    echo json_encode($products);

} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
