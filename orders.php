<?php
$page_title = 'My Orders';
require_once __DIR__ . '/includes/header.php';

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . 'auth/login.php?redirect=' . urlencode('orders.php'));
    exit;
}

// Fetch user's orders from the database
try {
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
    $error_message = "Could not fetch your order history.";
}
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="section-title">My Order History</h1>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
            <h4 class="mb-3">You haven't placed any orders yet.</h4>
            <p class="text-muted">All your past orders will appear here once you've made a purchase.</p>
            <a href="products.php" class="btn btn-primary mt-3">Browse Our Products</a>
        </div>
    <?php else: ?>
        <div class="accordion" id="ordersAccordion">
            <?php foreach ($orders as $index => $order): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $order['id']; ?>">
                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $order['id']; ?>">
                            <span class="fw-bold me-3">Order #<?php echo htmlspecialchars($order['order_number']); ?></span>
                            <span class="me-auto">Date: <?php echo date('d M Y', strtotime($order['created_at'])); ?></span>
                            <span class="badge bg-info text-dark"><?php echo ucfirst(htmlspecialchars($order['status'])); ?></span>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $order['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#ordersAccordion">
                        <div class="accordion-body">
                            <p><strong>Total Amount:</strong> UGX <?php echo number_format($order['total_amount']); ?></p>
                            <p><strong>Delivery To:</strong> <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                            <p><strong>Contact Phone:</strong> <?php echo htmlspecialchars($order['delivery_phone']); ?></p>
                            <?php if(!empty($order['special_instructions'])): ?>
                                <p><strong>Special Instructions:</strong> <?php echo htmlspecialchars($order['special_instructions']); ?></p>
                            <?php endif; ?>
                            <hr>
                            <a href="print_receipt.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fas fa-print me-2"></i> Print Receipt
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>