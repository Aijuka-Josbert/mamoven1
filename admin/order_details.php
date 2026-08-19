<?php
$page_title = 'Order Details';
require_once __DIR__ . '/includes/header.php';
require_admin();

// Validate order ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . 'orders.php');
    exit;
}
$order_id = (int)$_GET['id'];

// Fetch order details along with user information
try {
    $order_stmt = $pdo->prepare("
        SELECT o.*, u.full_name, u.email, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $order_stmt->execute([$order_id]);
    $order = $order_stmt->fetch();

    if (!$order) {
        header("Location: " . 'orders.php?status=notfound');
        exit;
    }

    // Fetch order items
    $items_stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.image as product_image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $items_stmt->execute([$order_id]);
    $order_items = $items_stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error fetching order details: " . $e->getMessage());
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Order: <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></p>
    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
</div>

<div class="row">
    <!-- Order Summary -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Order Summary</h6></div>
            <div class="card-body">
                <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                <p><strong>Order Date:</strong> <?php echo date('d M Y, g:ia', strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-info text-dark"><?php echo ucfirst(htmlspecialchars($order['status'])); ?></span></p>
                <p><strong>Total Amount:</strong> <span class="fw-bold">UGX <?php echo number_format($order['total_amount']); ?></span></p>
            </div>
        </div>
        
        <div class="card shadow mt-4">
            <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Customer Details</h6></div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Customer Phone:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                <hr>
                <h6 class="fw-bold">Delivery Information</h6>
                <p><strong>Delivery Phone:</strong> <?php echo htmlspecialchars($order['delivery_phone']); ?></p>
                <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                <?php if (!empty($order['special_instructions'])): ?>
                    <p><strong>Instructions:</strong><br><?php echo nl2br(htmlspecialchars($order['special_instructions'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Items in this Order</h6></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $subtotal = 0;
                            foreach ($order_items as $item): 
                                $subtotal += $item['total_price'];
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo BASE_URL . '/' . ($item['product_image'] ?: 'assets/images/placeholder.jpg'); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 15px;">
                                            <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end">UGX <?php echo number_format($item['unit_price']); ?></td>
                                    <td class="text-end fw-bold">UGX <?php echo number_format($item['total_price']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end fw-bold">UGX <?php echo number_format($subtotal); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Delivery Fee:</strong></td>
                                <td class="text-end fw-bold">UGX <?php echo number_format($order['total_amount'] - $subtotal); ?></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                                <td class="text-end fw-bold h5">UGX <?php echo number_format($order['total_amount']); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>