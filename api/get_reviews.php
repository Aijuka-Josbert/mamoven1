<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
ob_clean();

$product_id = (int)($_GET['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

try {
    // Get verified purchase reviews for this product
    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            r.rating,
            r.comment,
            r.created_at,
            r.is_verified_purchase,
            u.full_name,
            (SELECT COUNT(*) FROM order_items oi 
             WHERE oi.product_id = r.product_id AND oi.order_id IN (
                 SELECT id FROM orders WHERE user_id = r.user_id AND status = 'delivered'
             )) as has_delivered_order
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ? 
        AND r.is_verified_purchase = 1
        ORDER BY r.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll();

    // Get average rating and count with breakdown
    $avg_stmt = $pdo->prepare("
        SELECT 
            AVG(rating) as avg_rating,
            COUNT(*) as review_count,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM reviews 
        WHERE product_id = ? AND is_verified_purchase = 1
    ");
    $avg_stmt->execute([$product_id]);
    $stats = $avg_stmt->fetch();

    $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'is_admin' => $is_admin,
        'avg_rating' => round($stats['avg_rating'] ?: 0, 1),
        'review_count' => (int)($stats['review_count'] ?: 0),
        'rating_breakdown' => [
            5 => (int)($stats['five_star'] ?: 0),
            4 => (int)($stats['four_star'] ?: 0),
            3 => (int)($stats['three_star'] ?: 0),
            2 => (int)($stats['two_star'] ?: 0),
            1 => (int)($stats['one_star'] ?: 0)
        ]
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}
