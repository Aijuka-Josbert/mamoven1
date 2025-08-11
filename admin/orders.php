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
            // Redirect to avoid form resubmission on refresh
            header("Location: " . admin_url('orders.php?status=updated'));
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
                                    <a href="<?php echo admin_url('order_details.php?id=' . $order['id']); ?>" class="btn btn-sm btn-primary">
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