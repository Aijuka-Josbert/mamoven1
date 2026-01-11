<?php
$page_title = 'My Shopping Cart';
require_once __DIR__ . '/includes/header.php';

// User must be logged in to view their cart
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . 'auth/login.php?redirect=' . urlencode('cart.php'));
    exit;
}
$user_id = $_SESSION['user_id'];

// Handle cart updates (remove/update quantity) from POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'remove' && $cart_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);
        } elseif ($action === 'update' && $cart_id > 0) {
            $quantity = (int)($_POST['quantity'] ?? 1);
            if ($quantity > 0) {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$quantity, $cart_id, $user_id]);
            } else {
                // If quantity is 0 or less, remove the item
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->execute([$cart_id, $user_id]);
            }
        }
    } catch (PDOException $e) {
        // Log error, but don't break the page
        error_log("Cart update error: " . $e->getMessage());
    }
    // Redirect to the same page using GET to prevent form resubmission
    header("Location: " . 'cart.php');
    exit;
}


// Fetch cart items and calculate totals
$subtotal = 0;
$delivery_fee = 5000; // Default/fixed delivery fee
$cart_items = [];

try {
    $stmt = $pdo->prepare("
        SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.status = 'active'
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $total = $subtotal + $delivery_fee;
} catch (PDOException $e) {
    $error_message = "Could not fetch cart items. Please try again.";
}
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="section-title">My Shopping Cart</h1>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($cart_items)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                    <h4 class="mb-3">Your cart is empty</h4>
                    <p class="text-muted">Looks like you haven't added any delicious treats yet.</p>
                    <a href="products.php" class="btn btn-primary mt-3">Start Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Cart Items List -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="row align-items-center mb-4">
                                <div class="col-md-2">
                                    <?php if (!empty($item['image']) && strpos($item['image'], 'data:image/') === 0): ?>
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="img-fluid rounded">
                                    <?php else: ?>
                                        <img src="<?php echo BASE_URL; ?>/assets/images/placeholder.jpg" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="img-fluid rounded">
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="mb-1 text-muted">Price: UGX <?php echo number_format($item['price']); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <form method="POST" class="d-flex">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" class="form-control form-control-sm text-center" 
                                               value="<?php echo $item['quantity']; ?>" min="1" onchange="this.form.submit()">
                                    </form>
                                </div>
                                <div class="col-md-2 text-end">
                                    <p class="fw-bold mb-0">UGX <?php echo number_format($item['price'] * $item['quantity']); ?></p>
                                </div>
                                <div class="col-md-1 text-end">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Item">&times;</button>
                                    </form>
                                </div>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                        <a href="products.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="mb-3">Order Summary</h4>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>UGX <?php echo number_format($subtotal); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee:</span>
                            <span>UGX <?php echo number_format($delivery_fee); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h5 fw-bold">
                            <span>Total:</span>
                            <span>UGX <?php echo number_format($total); ?></span>
                        </div>
                        <div class="d-grid mt-4">
                             <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                Proceed to Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Checkout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="checkout-form" method="POST" action="api/place_order.php">
                    <div class="mb-3">
                        <label for="delivery_address" class="form-label">Delivery Address *</label>
                        <textarea id="delivery_address" name="delivery_address" class="form-control" rows="3" required placeholder="Enter your full street address, area, and any nearby landmarks."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="delivery_phone" class="form-label">Contact Phone *</label>
                        <input type="tel" id="delivery_phone" name="delivery_phone" class="form-control" required placeholder="+256 7...">
                    </div>
                     <div class="mb-3">
                        <label for="special_instructions" class="form-label">Special Instructions</label>
                        <textarea id="special_instructions" name="special_instructions" class="form-control" rows="2" placeholder="e.g., 'Write Happy Birthday on the cake'"></textarea>
                    </div>
                     <hr>
                    <div class="text-center">
                        <p class="mb-1">Your total is <strong class="h5">UGX <?php echo number_format($total); ?></strong>.</p>
                        <p class="text-muted small">Payment is Cash on Delivery.</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="checkout-form" class="btn btn-primary">Place Order</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>