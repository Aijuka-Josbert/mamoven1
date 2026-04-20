<?php
$page_title = 'Admin Dashboard';
require_once __DIR__ . '/includes/header.php';

// Fetch dashboard statistics
try {
    // Total Active Products
    $total_products = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    // Total Customers
    $total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    // Total Orders
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    // Total Pending Orders
    $pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    // Recent Orders
    $recent_orders_stmt = $pdo->query("
        SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at, u.full_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recent_orders = $recent_orders_stmt->fetchAll();

} catch (PDOException $e) {
    // Handle potential database errors gracefully
    $error_message = "Database error: " . $e->getMessage();
    $total_products = $total_customers = $total_orders = $pending_orders = 0;
    $recent_orders = [];
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4">Admin Dashboard</h1>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_products; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-cookie-bite fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_customers; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_orders; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-box-open fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_orders; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Orders -->
    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6></div>
                <div class="card-body">
                    <a href="add_product.php" class="admin-actions-btn btn-primary mb-3 shadow-sm">Add New Product</a>
                    <a href="orders.php" class="admin-actions-btn btn-info mb-3 shadow-sm">View All Orders</a>
                    <a href="categories.php" class="admin-actions-btn btn-secondary mb-3 shadow-sm">Manage Categories</a>
                    <a href="testimonials.php" class="admin-actions-btn btn-success mb-3 shadow-sm">Manage Feedback</a>
                    <a href="promo_codes.php" class="admin-actions-btn btn-dark mb-3 shadow-sm">Manage Promo Codes</a>
                    <a href="contact_messages.php" class="admin-actions-btn btn-warning mb-3 shadow-sm">View Contact Messages</a>
                </div>
            </div>
        </div>
        <div class="col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_orders)): ?>
                                    <tr><td colspan="5" class="text-center">No recent orders found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        <td>UGX <?php echo number_format($order['total_amount']); ?></td>
                                        <td><span class="badge bg-info text-dark"><?php echo ucfirst(htmlspecialchars($order['status'])); ?></span></td>
                                        <td><a href="./order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>