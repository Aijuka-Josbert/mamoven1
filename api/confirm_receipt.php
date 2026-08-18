<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
if (ob_get_level() > 0) {
    ob_clean();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to perform this action']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

require_csrf_or_fail();

$order_id = (int)($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Check if order belongs to user and is not already delivered or cancelled
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found or access denied']);
        exit;
    }

    if (in_array($order['status'], ['delivered', 'cancelled'])) {
        echo json_encode(['success' => false, 'message' => 'Order is already ' . $order['status']]);
        exit;
    }

    // Update status to delivered
    $update_stmt = $pdo->prepare("UPDATE orders SET status = 'delivered' WHERE id = ? AND user_id = ?");
    $update_stmt->execute([$order_id, $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Order marked as received successfully. Thank you!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating order: ' . $e->getMessage()]);
}
