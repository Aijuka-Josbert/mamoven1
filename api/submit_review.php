<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
if (ob_get_level() > 0) {
    ob_clean();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

require_csrf_or_fail();

$product_id = (int)($_POST['product_id'] ?? 0);
$user_id = $_SESSION['user_id'];
$review_id = (int)($_POST['review_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

// Validation
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
}

if (strlen($comment) > 500) {
    echo json_encode(['success' => false, 'message' => 'Comment is too long']);
    exit;
}

try {
    // Check if product exists
    $product_check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $product_check->execute([$product_id]);
    if (!$product_check->fetch()) {
        throw new Exception('Product not found');
    }

    // Check if user has a delivered order containing this product
    $purchase_check = $pdo->prepare("
        SELECT COUNT(*) as count FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE oi.product_id = ? AND o.user_id = ? AND LOWER(o.status) = 'delivered'
    ");
    $purchase_check->execute([$product_id, $user_id]);
    $purchase_result = $purchase_check->fetch();
    $is_verified_purchase = $purchase_result['count'] > 0 ? 1 : 0;

    // Check if user has already reviewed this product
    $existing_review = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
    $existing_review->execute([$product_id, $user_id]);
    $existing_review_row = $existing_review->fetch();

    if ($review_id > 0 && (!$existing_review_row || (int)$existing_review_row['id'] !== $review_id)) {
        echo json_encode(['success' => false, 'message' => 'Review mismatch. Reload the page and try again.']);
        exit;
    }

    $current_review_id = $existing_review_row ? (int)$existing_review_row['id'] : 0;

    if ($existing_review_row) {
        // Update existing review
        $stmt = $pdo->prepare("
            UPDATE reviews 
            SET rating = ?, comment = ?, is_verified_purchase = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND product_id = ? AND user_id = ?
        ");
        $stmt->execute([$rating, $comment ?: null, $is_verified_purchase, $current_review_id, $product_id, $user_id]);
        $message = 'Review updated successfully';
    } else {
        if ($review_id > 0) {
            echo json_encode(['success' => false, 'message' => 'Review not found for editing.']);
            exit;
        }

        // Insert new review
        $stmt = $pdo->prepare("
            INSERT INTO reviews (product_id, user_id, rating, comment, is_verified_purchase) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$product_id, $user_id, $rating, $comment ?: null, $is_verified_purchase]);
        $current_review_id = (int)$pdo->lastInsertId();
        $message = 'Review submitted successfully';
    }

    // Get updated average rating
    $avg_stmt = $pdo->prepare("
        SELECT 
            AVG(rating) as avg_rating, 
            COUNT(*) as review_count 
        FROM reviews 
           WHERE product_id = ?
    ");
    $avg_stmt->execute([$product_id]);
    $stats = $avg_stmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => $message,
        'review_id' => $current_review_id,
        'avg_rating' => round($stats['avg_rating'] ?: 0, 1),
        'review_count' => (int)($stats['review_count'] ?: 0),
        'is_verified_purchase' => $is_verified_purchase
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
