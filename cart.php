<?php
session_start();
include_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$page_title = 'My Cart';
include_once 'includes/header.php';

// Get cart items
try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.image, p.description 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.status = 'active'
        ORDER BY c.added_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();

    // Calculate totals
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    // Get delivery fee from settings
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'delivery_fee'");
    $stmt->execute();
    $delivery_fee = (float)($stmt->fetchColumn() ?: 5000);
    
    $total = $subtotal + $delivery_fee;

} catch (Exception $e) {
    $cart_items = [];
    $subtotal = 0;
    $delivery_fee = 5000;
    $total = 0;
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-shopping-cart"></i> My Cart</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($cart_items)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5>Your cart is empty</h5>
                            <p class="text-muted">Add some delicious items to your cart to get started!</p>
                            <a href="products.php" class="btn btn-primary">Browse Products</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-cart-id="<?php echo $item['id']; ?>">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img src="<?php echo $item['image'] ?: 'assets/images/placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="cart-item-image">
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['description']); ?></p>
                                        <strong class="text-primary">UGX <?php echo number_format($item['price']); ?></strong>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="quantity-controls">
                                            <button class="quantity-btn" onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                                                   min="1" onchange="updateCartQuantity(<?php echo $item['id']; ?>, this.value)">
                                            <button class="quantity-btn" onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>UGX <?php echo number_format($item['price'] * $item['quantity']); ?></strong>
                                    </div>
                                    <div class="col-md-1">
                                        <button class="btn btn-outline-danger btn-sm" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($cart_items)): ?>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="subtotal">UGX <?php echo number_format($subtotal); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Fee:</span>
                        <span>UGX <?php echo number_format($delivery_fee); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="text-primary" id="total">UGX <?php echo number_format($total); ?></strong>
                    </div>
                    
                    <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                        Proceed to Checkout
                    </button>
                    <a href="products.php" class="btn btn-outline-primary w-100">Continue Shopping</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkoutModalLabel">Checkout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="checkout-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="delivery_address" class="form-label">Delivery Address *</label>
                                <textarea id="delivery_address" class="form-control" rows="3" required 
                                         placeholder="Enter your full delivery address"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="delivery_phone" class="form-label">Phone Number *</label>
                                <input type="tel" id="delivery_phone" class="form-control" required 
                                       placeholder="+256 700 123456">
                            </div>
                            <div class="mb-3">
                                <label for="special_instructions" class="form-label">Special Instructions</label>
                                <textarea id="special_instructions" class="form-control" rows="2" 
                                         placeholder="Any special requests or instructions"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border rounded p-3 mb-3">
                        <h6>Order Summary</h6>
                        <div class="d-flex justify-content-between">
                            <span>Total Amount:</span>
                            <strong class="text-primary">UGX <?php echo number_format($total); ?></strong>
                        </div>
                        <small class="text-muted">Payment will be made on delivery (Cash on Delivery)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="place-order-btn" onclick="placeOrder()">
                    Place Order
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Make userLoggedIn available to main.js
var userLoggedIn = true;
</script>

<?php include_once 'includes/footer.php'; ?>
