<?php
$page_title = 'My Orders';
require_once __DIR__ . '/includes/header.php';

// Include PHPMailer for notifications
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/vendor/autoload.php';

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . 'auth/login.php?redirect=' . urlencode('orders.php'));
    exit;
}

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = (int)($_POST['order_id'] ?? 0);
    
    try {
        // Verify order belongs to user and can be cancelled
        $stmt = $pdo->prepare("
            SELECT * FROM orders 
            WHERE id = ? AND user_id = ? AND status IN ('pending', 'confirmed')
        ");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        
        if ($order) {
            // Update order status to cancelled
            $update_stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $update_stmt->execute([$order_id]);
            
            // Restore product stock
            $items_stmt = $pdo->prepare("
                SELECT product_id, quantity 
                FROM order_items 
                WHERE order_id = ?
            ");
            $items_stmt->execute([$order_id]);
            $items = $items_stmt->fetchAll();
            
            $restore_stmt = $pdo->prepare("
                UPDATE products 
                SET stock_quantity = stock_quantity + ? 
                WHERE id = ?
            ");
            
            foreach ($items as $item) {
                $restore_stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Notify Admin via Email about cancellation
            try {
                $mail = new PHPMailer(true);

                configure_mailer_transport($mail);
                $mail->setFrom(default_mail_from_address(), SITE_NAME);
                $mail->addAddress('mamasovenug@gmail.com');

                $mail->isHTML(true);
                $mail->Subject = "Order Cancelled - " . $order['order_number'];
                
                // Fetch user details for the email
                $user_stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
                $user_stmt->execute([$_SESSION['user_id']]);
                $user_info = $user_stmt->fetch();
                $customer_name = $user_info ? $user_info['full_name'] : 'Unknown Customer';

                $mail->Body = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            " . email_logo_html($mail, 80) . "
                        </div>
                        <h2 style='color: #8B4513;'>Order Cancellation Alert</h2>
                        <p><strong>Order Number:</strong> {$order['order_number']}</p>
                        <p><strong>Customer:</strong> " . htmlspecialchars($customer_name) . "</p>
                        <p><strong>Status:</strong> Cancelled by User</p>
                        <p><strong>Total Amount:</strong> UGX " . number_format($order['total_amount']) . "</p>
                        <p>The items have been returned to stock automatically.</p>
                        <p><a href='" . BASE_URL . "/admin/orders.php' style='color: #8B4513;'>View in Admin Dashboard</a></p>
                    </div>
                </body>
                </html>
                ";

                send_mail_with_fallback($mail);
            } catch (Exception $e) {
                // Log error but don't stop the success message flow
                error_log("Failed to send cancellation email to admin: " . $e->getMessage());
            }

            $success_message = "Order #{$order['order_number']} has been cancelled successfully.";
        } else {
            $error_message = "Order not found or cannot be cancelled.";
        }
    } catch (PDOException $e) {
        $error_message = "Failed to cancel order: " . $e->getMessage();
    }
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
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
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
                            <span class="badge <?php 
                                echo $order['status'] === 'cancelled' ? 'bg-danger' : 
                                     ($order['status'] === 'delivered' ? 'bg-success' : 'bg-info'); 
                            ?> text-white">
                                <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                            </span>
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
                            <div class="d-flex gap-2">
                                <a href="print_receipt.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-print me-2"></i> Print Receipt
                                </a>
                                
                                <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmCancelOrder(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['order_number']); ?>')">
                                        <i class="fas fa-times me-2"></i> Cancel Order
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Hidden form for cancellation -->
                            <form id="cancel-form-<?php echo $order['id']; ?>" method="POST" style="display: none;">
                                <input type="hidden" name="cancel_order" value="1">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmCancelOrder(orderId, orderNumber) {
    Swal.fire({
        title: 'Cancel Order?',
        html: `
            <p>Are you sure you want to cancel <strong>Order #${orderNumber}</strong>?</p>
            <p class="text-muted small">This action cannot be undone. The items will be returned to stock.</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Cancel Order',
        cancelButtonText: 'Keep Order'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('cancel-form-' + orderId).submit();
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>