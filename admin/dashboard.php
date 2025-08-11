<?php
session_start();
include_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$page_title = 'Admin Dashboard';
include_once '../includes/header.php';

// Get dashboard statistics
try {
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
    $total_products = $stmt->fetchColumn();

    // Total customers
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $total_customers = $stmt->fetchColumn();

    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $total_orders = $stmt->fetchColumn();

    // Pending orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $pending_orders = $stmt->fetchColumn();

    // Recent orders
    $stmt = $pdo->query("SELECT o.*, u.full_name 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 10");
    $recent_orders = $stmt->fetchAll();

    // Monthly revenue
    $stmt = $pdo->query("SELECT SUM(total_amount) 
                        FROM orders 
                        WHERE status IN ('confirmed', 'processing', 'ready', 'delivered') 
                        AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                        AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $monthly_revenue = $stmt->fetchColumn() ?: 0;

} catch (Exception $e) {
    $total_products = $total_customers = $total_orders = $pending_orders = $monthly_revenue = 0;
    $recent_orders = [];
}
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Admin Dashboard</h1>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?php echo $total_products; ?></h4>
                            <p class="card-text">Total Products</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-box fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?php echo $total_customers; ?></h4>
                            <p class="card-text">Total Customers</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?php echo $total_orders; ?></h4>
                            <p class="card-text">Total Orders</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">UGX <?php echo number_format($monthly_revenue); ?></h4>
                            <p class="card-text">Monthly Revenue</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Product
                        </a>
                        <a href="orders.php" class="btn btn-warning">
                            <i class="fas fa-eye"></i> View Orders (<?php echo $pending_orders; ?> pending)
                        </a>
                        <a href="customers.php" class="btn btn-info">
                            <i class="fas fa-users"></i> Manage Customers
                        </a>
                        <a href="categories.php" class="btn btn-secondary">
                            <i class="fas fa-tags"></i> Manage Categories
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Orders</h5>
                    <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <p class="text-muted text-center">No orders yet</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                            <td>UGX <?php echo number_format($order['total_amount']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $order['status'] === 'pending' ? 'warning' : 
                                                         ($order['status'] === 'confirmed' ? 'info' : 
                                                         ($order['status'] === 'delivered' ? 'success' : 'secondary')); ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
