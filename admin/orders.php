<?php
$page_title = 'Manage Orders';
require_once __DIR__ . '/includes/header.php';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    $valid_statuses = ['pending', 'confirmed', 'processing', 'ready', 'delivered', 'cancelled'];

    if ($order_id > 0 && in_array($new_status, $valid_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);

            // Fetch user email and name for this order
            $stmt = $pdo->prepare("SELECT u.email, u.full_name, o.order_number FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
            $stmt->execute([$order_id]);
            $orderUser = $stmt->fetch();

            if ($orderUser) {
                require_once __DIR__ . '/../vendor/autoload.php';
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                configure_mailer_transport($mail);
                $mail->setFrom(default_mail_from_address(), SITE_NAME);
                $mail->addAddress($orderUser['email'], $orderUser['full_name']);
                $mail->isHTML(true);

                $statusMsg = ucfirst($new_status);
                $mail->Subject = "Order {$orderUser['order_number']} Status Update: {$statusMsg}";
                $mail->Body = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            " . email_logo_html($mail, 80) . "
                        </div>
                        <h2 style='color: #8B4513;'>Order Status Update</h2>
                        <p>Dear {$orderUser['full_name']},</p>
                        <p>Your order <strong>{$orderUser['order_number']}</strong> status has been updated to: <strong style='color: #8B4513;'>{$statusMsg}</strong>.</p>
                        <p>You can track your order status by visiting your <a href='" . BASE_URL . "/orders.php' style='color: #8B4513;'>order history</a>.</p>
                        <p style='margin-top: 30px;'>
                            Thank you for shopping with " . SITE_NAME . "!<br>
                            <strong>The " . SITE_NAME . " Team</strong>
                        </p>
                    </div>
                </body>
                </html>
                ";
                send_mail_with_fallback($mail);
            }

            // Redirect to avoid form resubmission on refresh
            header("Location: " . 'orders.php?status=updated');
            exit;
        } catch (PDOException $e) {
            $error_message = "Failed to update order status: " . $e->getMessage();
        }
    }
}

// Get filter status from URL
$status_filter = $_GET['status'] ?? '';

// Build the query based on the filter
$sql = "SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at, u.full_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id";

if (!empty($status_filter) && $status_filter !== 'all') {
    $sql .= " WHERE o.status = :status";
}

$sql .= " ORDER BY o.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    if (!empty($status_filter) && $status_filter !== 'all') {
        $stmt->bindParam(':status', $status_filter, PDO::PARAM_STR);
    }
    $stmt->execute();
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Could not fetch orders: " . $e->getMessage();
    $orders = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Manage Orders</h1>
    <!-- Filter Dropdown -->
    <form method="GET" class="d-flex align-items-center">
        <label for="status-filter" class="form-label me-2 mb-0">Filter:</label>
        <select class="form-select w-auto" id="status-filter" name="status" onchange="this.form.submit()">
            <option value="all" <?php echo ($status_filter === 'all' || $status_filter === '') ? 'selected' : ''; ?>>All Orders</option>
            <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="confirmed" <?php echo ($status_filter === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
            <option value="processing" <?php echo ($status_filter === 'processing') ? 'selected' : ''; ?>>Processing</option>
            <option value="ready" <?php echo ($status_filter === 'ready') ? 'selected' : ''; ?>>Ready</option>
            <option value="delivered" <?php echo ($status_filter === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
            <option value="cancelled" <?php echo ($status_filter === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
        </select>
    </form>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
    <div class="alert alert-success">Order status has been updated successfully.</div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="6" class="text-center">No orders found matching this filter.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                <td><?php echo date('d M Y, g:ia', strtotime($order['created_at'])); ?></td>
                                <td>UGX <?php echo number_format($order['total_amount']); ?></td>
                                <td>
                                    <!-- Status Update Form -->
                                    <form method="POST" class="d-inline-flex">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="pending" <?php echo ($order['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo ($order['status'] === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="processing" <?php echo ($order['status'] === 'processing') ? 'selected' : ''; ?>>Processing</option>
                                            <option value="ready" <?php echo ($order['status'] === 'ready') ? 'selected' : ''; ?>>Ready</option>
                                            <option value="delivered" <?php echo ($order['status'] === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo ($order['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>