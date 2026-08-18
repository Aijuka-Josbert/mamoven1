<?php
$page_title = 'My Profile';
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php?redirect=' . urlencode('customer_profile.php'));
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($full_name)) {
            $errors[] = 'Full name is required';
        }
        if (empty($phone)) {
            $errors[] = 'Phone number is required';
        } elseif (!preg_match('/^[\+]?[0-9\s\(\)\-\.]+$/', $phone) || strlen(preg_replace('/\D/', '', $phone)) < 7) {
            $errors[] = 'Invalid phone number format (minimum 7 digits required)';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->execute([$full_name, $phone, $address, $user_id]);
                $_SESSION['full_name'] = $full_name;
                $success = 'Profile updated successfully!';
            } catch (PDOException $e) {
                $errors[] = 'Failed to update profile. Please try again.';
            }
        }
    }
}

$user = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = [];
}

$recent_orders = [];
try {
    $orders_stmt = $pdo->prepare("
        SELECT id, order_number, total_amount, status, created_at 
        FROM orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $orders_stmt->execute([$user_id]);
    $recent_orders = $orders_stmt->fetchAll();
} catch (PDOException $e) {
    $recent_orders = [];
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="profile-card">
                <div class="profile-header text-center">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    </div>
                </div>

                <div class="btn-group-vertical">
                    <a href="#profile-edit" class="btn btn-outline-primary" data-bs-toggle="collapse">
                        <i class="fas fa-edit me-2"></i> Edit Profile
                    </a>
                    <a href="orders.php" class="btn btn-outline-primary">
                        <i class="fas fa-box me-2"></i> My Orders
                    </a>
                    <a href="auth/logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="collapse mb-4" id="profile-edit">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Profile</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email (Read-only)</label>
                                <input type="email" id="email" class="form-control" disabled
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                <div class="form-text">Email cannot be changed. Contact support to update.</div>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Delivery Address</label>
                                <textarea id="address" name="address" class="form-control" rows="3" 
                                          placeholder="Enter your street address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Save Changes
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#profile-edit">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Orders</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <p class="text-muted">No orders yet. <a href="products.php">Start shopping</a></p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                            <td>UGX <?php echo number_format($order['total_amount']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $order['status'] === 'completed' ? 'success' : 
                                                        ($order['status'] === 'pending' ? 'warning' :
                                                        ($order['status'] === 'cancelled' ? 'danger' : 'info'));
                                                ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="print_receipt.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-receipt me-1"></i> Receipt
                                                </a>
                                                <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-times me-1"></i> Cancel
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="orders.php" class="btn btn-outline-primary">View All Orders</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>