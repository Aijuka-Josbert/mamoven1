<?php
session_start();
include_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$product_id = $_POST['product_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;
$user_id = $_SESSION['user_id'];

if (!$product_id || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit;
}

try {
    // Check if product exists and is active
    $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found or unavailable']);
        exit;
    }

    // Check if item already in cart
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_cart_item = $stmt->fetch();

    if ($existing_cart_item) {
        // Update existing cart item
        $new_quantity = $existing_cart_item['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, added_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$new_quantity, $existing_cart_item['id']]);
    } else {
        // Add new cart item
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }

    // Get updated cart count
    $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = $stmt->fetchColumn() ?: 0;

    echo json_encode([
        'success' => true, 
        'message' => 'Product added to cart successfully',
        'cart_count' => $cart_count
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
