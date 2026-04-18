<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = (int)($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order']);
    exit;
}

try {
    // Verify order belongs to user
    $order_stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $order_stmt->execute([$order_id, $user_id]);
    $order = $order_stmt->fetch();

    if (!$order) {
        throw new Exception('Order not found');
    }

    // Check if order can be cancelled (only pending or processing)
    if (!in_array($order['status'], ['pending', 'processing'])) {
        throw new Exception('Cannot cancel orders with status: ' . $order['status']);
    }

    // Begin transaction for atomic operation
    $pdo->beginTransaction();

    // Restore stock quantities
    $items_stmt = $pdo->prepare("
        SELECT product_id, quantity FROM order_items WHERE order_id = ?
    ");
    $items_stmt->execute([$order_id]);
    $items = $items_stmt->fetchAll();

    $restore_stock = $pdo->prepare("
        UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?
    ");
    foreach ($items as $item) {
        $restore_stock->execute([$item['quantity'], $item['product_id']]);
    }

    // Update order status
    $update_stmt = $pdo->prepare("
        UPDATE orders SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = ?
    ");
    $update_stmt->execute([$order_id]);

    // Log activity
    $log_stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, username, activity_type, activity_description, ip_address) 
        VALUES (?, ?, 'order_cancellation', ?, ?)
    ");
    $log_stmt->execute([
        $user_id,
        $_SESSION['username'] ?? 'Unknown',
        "Order #" . $order['order_number'] . " cancelled",
        $_SERVER['REMOTE_ADDR']
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order cancelled successfully. Stock has been restored.'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
