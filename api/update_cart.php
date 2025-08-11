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

$cart_id = $_POST['cart_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;
$user_id = $_SESSION['user_id'];

if (!$cart_id || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item or quantity']);
    exit;
}

try {
    // Verify cart item belongs to user
    $stmt = $pdo->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }

    // Update quantity
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $cart_id]);

    echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
