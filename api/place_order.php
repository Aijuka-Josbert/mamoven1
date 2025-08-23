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

$user_id = $_SESSION['user_id'];
$delivery_address = trim($_POST['delivery_address'] ?? '');
$delivery_phone = trim($_POST['delivery_phone'] ?? '');
$special_instructions = trim($_POST['special_instructions'] ?? '');

if (!$delivery_address || !$delivery_phone) {
    echo json_encode(['success' => false, 'message' => 'Delivery address and phone are required']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Get cart items
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.status = 'active'
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }

    // Calculate total
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    // Get delivery fee
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'delivery_fee'");
    $stmt->execute();
    $delivery_fee = (float)($stmt->fetchColumn() ?: 5000);

    $total_amount = $subtotal + $delivery_fee;

    // Generate order number
    $order_number = 'MO' . date('Ymd') . sprintf('%04d', rand(1, 9999));

    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, order_number, total_amount, delivery_address, delivery_phone, special_instructions) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $order_number, $total_amount, $delivery_address, $delivery_phone, $special_instructions]);
    $order_id = $pdo->lastInsertId();

    // Create order items
    foreach ($cart_items as $item) {
        $total_price = $item['price'] * $item['quantity'];
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price'], $total_price]);
    }

    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $pdo->commit();
    header("Location: ../print_receipt.php?id=" . $order_id . '&placed=true' );
    exit;
        echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully',
        'order_number' => $order_number,
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
