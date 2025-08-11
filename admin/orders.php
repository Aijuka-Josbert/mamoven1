<?php
session_start();
include_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$page_title = 'Manage Orders';
include_once '../includes/header.php';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $order_id = (int)($_POST['order_id'] ?? 0);
    
    if ($action === 'update_status' && $order_id) {
        $new_status = $_POST['status'] ?? '';
        $valid_statuses = ['pending', 'confirmed', 'processing', 'ready', 'delivered', 'cancelled'];
        
        if (in_array($new_status, $valid_statuses)) {
            try {
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $order_id]);
                $success = 'Order status updated successfully!';
            } catch (Exception $e) {
                $error = 'Failed to update order status: ' . $e->getMessage();
            }
        }
    }
}

// Get orders with pagination
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;
$status_filter = $_GET['status'] ?? '';

try {
    $where_clause = '';
    $params = [];
    
    if ($status_filter) {
        $where_clause = 'WHERE o.status = ?';
        $params[] = $status_filter;
    }
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM orders o $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_orders = $count_stmt->fetchColumn();
    
    // Get orders
    $sql = "SELECT o.*, u.full_name, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            $where_clause 
            ORDER BY o.created_at DESC 
            LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    $total_pages = ceil($total_orders / $limit);
} catch (Exception $e) {
    $orders = [];
    $total_pages = 0;
    $total_orders = 0;
}
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Manage Orders</h1>
                <div class="d-flex gap-2">
                    <select class="form-select" style="width: auto;" onchange="filterOrders(this.value)">
                        <option value="">All Orders</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="ready" <?php echo $status_filter === 'ready' ? 'selected' : ''; ?>>Ready</option>
                        <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        $statuses = ['pending' => 'warning', 'confirmed' => 'info', 'processing' => 'primary', 'ready' => 'success', 'delivered' => 'success', 'cancelled' => 'danger'];
        foreach ($statuses as $status => $badge_class):
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = ?");
                $stmt->execute([$status]);
                $count = $stmt->fetchColumn();
            } catch (Exception $e) {
                $count = 0;
            }
        ?>
        <div class="col-md-2 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-<?php echo $badge_class; ?>"><?php echo $count; ?></h4>
                    <p class="card-text small mb-0"><?php echo ucfirst($status); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Delivery Info</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No orders found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($order['full_name']); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td>UGX <?php echo number_format($order['total_amount']); ?></td>
                                    <td>
                                        <select class="form-select form-select-sm" 
                                                onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)"
                                                style="width: auto;">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($order['delivery_phone']); ?><br>
                                            <?php echo htmlspecialchars(substr($order['delivery_address'], 0, 30)) . '...'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" onclick="printOrder(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Orders pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function filterOrders(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function updateOrderStatus(orderId, status) {
    if (confirm('Are you sure you want to update this order status?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="order_id" value="${orderId}">
            <input type="hidden" name="status" value="${status}">
        `;
        document.body.appendChild(form);
        form.submit();
    } else {
        // Reset the select to original value
        location.reload();
    }
}

function viewOrderDetails(orderId) {
    window.open(`order-details.php?id=${orderId}`, '_blank');
}

function printOrder(orderId) {
    window.open(`print-order.php?id=${orderId}`, '_blank');
}
</script>

<?php include_once '../includes/footer.php'; ?>
