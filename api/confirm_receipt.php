<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
ob_clean();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to perform this action']);
    exit;
}

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

    if (
/* Dynamic Animations & Organic Shapes */
.organic-blob {
    border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
    transition: all 1s ease;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.organic-blob:hover, .product-card:hover .organic-blob {
    border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
    transform: scale(1.05) rotate(2deg);
}

/* Animations */
.animate-on-scroll {
    opacity: 0;
    transition: all 0.8s ease-out;
}

.animate-on-scroll.visible {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.fade-in-up {
    transform: translateY(40px);
}

.fade-in-scale {
    transform: scale(0.9);
}

.delay-1 { animation-delay: 0.2s; transition-delay: 0.2s; }
.delay-2 { animation-delay: 0.4s; transition-delay: 0.4s; }
.delay-3 { animation-delay: 0.6s; transition-delay: 0.6s; }

.feature-box i {
    transition: transform 0.5s ease;
}
.feature-box:hover i {
    transform: scale(1.2) rotate(-5deg);
}

/* Smooth gradient updates for better depth */
body {
    background: linear-gradient(to bottom, #FBF2EA 0%, #FFFFFF 100%);
    min-height: 100vh;
}

.product-image-wrapper {
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.product-image {
    width: 180px;
    height: 180px;
    object-fit: cover;
}
EOForder) {
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
