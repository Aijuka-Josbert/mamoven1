<?php
session_start();
include_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$page_title = 'My Orders';
include_once 'includes/header.php';

// Get user's orders
try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               COUNT(oi.id) as item_count,
               GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') SEPARATOR ', ') as items
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    $orders = [];
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">My Orders</h1>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                    <h5>No orders yet</h5>
                    <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                    <a href="products.php" class="btn btn-primary">Browse Products</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($orders as $order): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card order-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Order #<?php echo htmlspecialchars($order['order_number']); ?></h6>
                            <span class="badge bg-<?php 
                                echo $order['status'] === 'pending' ? 'warning' : 
                                     ($order['status'] === 'confirmed' ? 'info' : 
                                     ($order['status'] === 'processing' ? 'primary' : 
                                     ($order['status'] === 'ready' ? 'success' : 
                                     ($order['status'] === 'delivered' ? 'success' : 'danger')))); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Order Date</small>
                                    <p class="mb-0"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Total Amount</small>
                                    <p class="mb-0 text-primary"><strong>UGX <?php echo number_format($order['total_amount']); ?></strong></p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">Items (<?php echo $order['item_count']; ?>)</small>
                                <p class="mb-0"><?php echo htmlspecialchars($order['items']); ?></p>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">Delivery Address</small>
                                <p class="mb-0"><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                            </div>

                            <?php if ($order['special_instructions']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Special Instructions</small>
                                    <p class="mb-0"><?php echo htmlspecialchars($order['special_instructions']); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                                <?php if ($order['status'] === 'delivered'): ?>
                                    <button class="btn btn-outline-success btn-sm" onclick="printReceipt(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-print"></i> Print Receipt
                                    </button>
                                <?php endif; ?>
                                <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                    <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
.order-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.order-card .card-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-bottom: none;
}

.order-card .badge {
    font-size: 0.8rem;
}
</style>

<script>
function viewOrderDetails(orderId) {
    // Load order details via AJAX
    $.ajax({
        url: 'api/get_order_details.php',
        method: 'GET',
        data: { order_id: orderId },
        success: function(response) {
            $('#orderDetailsContent').html(response);
            $('#orderDetailsModal').modal('show');
        },
        error: function() {
            showError('Failed to load order details');
        }
    });
}

function printReceipt(orderId) {
    window.open(`print-receipt.php?order_id=${orderId}`, '_blank');
}

function cancelOrder(orderId) {
    Swal.fire({
        title: 'Cancel Order?',
        text: 'Are you sure you want to cancel this order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Cancel Order',
        cancelButtonText: 'Keep Order'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement order cancellation
            $.ajax({
                url: 'api/cancel_order.php',
                method: 'POST',
                data: { order_id: orderId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showSuccess('Order cancelled successfully');
                        location.reload();
                    } else {
                        showError(response.message || 'Failed to cancel order');
                    }
                },
                error: function() {
                    showError('Network error. Please try again.');
                }
            });
        }
    });
}
</script>

<?php include_once 'includes/footer.php'; ?>
