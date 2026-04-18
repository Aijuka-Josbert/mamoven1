<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
ob_clean();

try {
    // Get top rated products with verified reviews for dashboard display
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.name,
            p.description,
            p.price,
            p.image,
            AVG(r.rating) as avg_rating,
            COUNT(r.id) as review_count,
            (SELECT comment FROM reviews WHERE product_id = p.id AND is_verified_purchase = 1 ORDER BY created_at DESC LIMIT 1) as latest_comment,
            (SELECT u.full_name FROM reviews r2 JOIN users u ON r2.user_id = u.id WHERE r2.product_id = p.id AND r2.is_verified_purchase = 1 ORDER BY r2.created_at DESC LIMIT 1) as latest_reviewer
        FROM products p
        LEFT JOIN reviews r ON p.id = r.product_id AND r.is_verified_purchase = 1
        WHERE p.status = 'active'
        GROUP BY p.id, p.name, p.description, p.price, p.image
        HAVING review_count > 0
        ORDER BY avg_rating DESC, review_count DESC
        LIMIT 6
    ");
    $stmt->execute();
    $products = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}