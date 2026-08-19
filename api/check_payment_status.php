<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../payments/pesajet.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

require_csrf_or_fail();

$order_id = (int)($_POST['order_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order']);
    exit;
}

try {
    // Only the order's own customer (or an admin) may check its status.
    $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    $stmt = $pdo->prepare("SELECT id, user_id, payment_method, payment_status, payment_reference FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order || (!$is_admin && (int)$order['user_id'] !== $user_id)) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    if ($order['payment_method'] !== 'mobile_money' || empty($order['payment_reference'])) {
        echo json_encode(['success' => true, 'payment_status' => $order['payment_status']]);
        exit;
    }

    // Only re-query PesaJet while the status is still non-final — no point
    // hammering their API once a payment has already completed or failed.
    if (in_array($order['payment_status'], ['completed', 'failed'], true)) {
        echo json_encode(['success' => true, 'payment_status' => $order['payment_status']]);
        exit;
    }

    $pesajet = new PesaJetPay();
    $result = $pesajet->getTransaction($order['payment_reference']);

    if ($result['success']) {
        $remoteStatus = strtolower($result['data']['status'] ?? 'processing');
        $mapped = match ($remoteStatus) {
            'completed', 'success', 'successful' => 'completed',
            'failed', 'declined', 'cancelled' => 'failed',
            default => 'processing',
        };

        $update = $pdo->prepare("UPDATE orders SET payment_status = ?, paid_at = IF(? = 'completed' AND paid_at IS NULL, NOW(), paid_at) WHERE id = ?");
        $update->execute([$mapped, $mapped, $order_id]);

        echo json_encode(['success' => true, 'payment_status' => $mapped]);
    } else {
        // Couldn't reach PesaJet right now — report the last known status
        // rather than failing the request.
        echo json_encode(['success' => true, 'payment_status' => $order['payment_status'], 'note' => 'Could not refresh from PesaJet right now']);
    }
} catch (PDOException $e) {
    error_log('check_payment_status failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred']);
}
