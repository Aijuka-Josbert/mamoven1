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
$delivery_fee = 0; // Default to 0 until they select a location!
$cart_items = [];
$user_phone = '';
$user_address = '';

try {
    // Fetch user's phone and address for checkout suggestions
    $user_stmt = $pdo->prepare("SELECT phone, address FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user_data = $user_stmt->fetch();
    $user_phone = $user_data['phone'] ?? '';
    $user_address = $user_data['address'] ?? '';

    // Fetch dynamic delivery fee from settings
    $locations_stmt = $pdo->query("SELECT * FROM delivery_locations WHERE is_active = 1 ORDER BY name ASC");
    $delivery_locations = $locations_stmt->fetchAll();
    $delivery_fee = 0; // Default to 0 until they select a location!

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
    $total = $subtotal; // Total is just subtotal initially
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
                            <span id="subtotal-amount">UGX <?php echo number_format($subtotal); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee:</span>
                            <span id="delivery-fee">UGX <?php echo number_format($delivery_fee); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" id="discount-row" style="display: none;">
                            <span>Discount:</span>
                            <span id="discount-amount" style="color: green;">UGX 0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h5 fw-bold">
                            <span>Total:</span>
                            <span id="total-amount">UGX <?php echo number_format($total); ?></span>
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
                    <input type="hidden" id="promo-code-hidden" name="promo_code" value="">
                    <div class="mb-2">
                        <label for="delivery_area_search" class="form-label">Search Delivery Area</label>
                        <input type="text" id="delivery_area_search" class="form-control" placeholder="Type area name to filter list">
                        <div class="form-text">Search first, then select from the area dropdown.</div>
                    </div>
                    <div class="mb-3">
                        <label for="delivery_location" class="form-label">Select Delivery Area <span class="text-danger">*</span></label>
                        <select id="delivery_location" name="location_id" class="form-select" required onchange="updateDeliveryFee()">
                            <option value="">-- Select Area --</option>
                            <?php foreach ($delivery_locations as $loc): ?>
                                <option value="<?php echo $loc['id']; ?>" data-fee="<?php echo $loc['fee']; ?>">
                                    <?php echo htmlspecialchars($loc['name']); ?> (+UGX <?php echo number_format($loc['fee']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="delivery_address" class="form-label">Specific Delivery Address <span class="text-danger">*</span></label>
                        <textarea id="delivery_address" name="delivery_address" class="form-control" rows="2" required placeholder="Enter your full street address, area, and any nearby landmarks."><?php echo htmlspecialchars($user_address); ?></textarea>
                        <div class="form-text">We need this to deliver your order accurately within the area.</div>
                    </div>
                    <div class="mb-3">
                        <label for="delivery_phone" class="form-label">Contact Phone <span class="text-danger">*</span></label>
                        <input type="tel" id="delivery_phone" name="delivery_phone" class="form-control" 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               required placeholder="07XXXXXXXX" value="<?php echo htmlspecialchars($user_phone); ?>">
                        <div class="form-text">We'll use this to confirm your delivery. You can change it if needed.</div>
                    </div>
                     <div class="mb-3">
                        <label for="special_instructions" class="form-label">Special Instructions <small class="text-muted">(Optional)</small></label>
                        <textarea id="special_instructions" name="special_instructions" class="form-control" rows="2" placeholder="e.g., 'Write Happy Birthday on the cake' or 'Leave at gate'"></textarea>
                    </div>
                    
                    <!-- Promo Code Section -->
                    <div class="mb-3">
                        <label for="promo-code" class="form-label">Promo Code <small class="text-muted">(Optional)</small></label>
                        <div class="input-group">
                            <input type="text" id="promo-code" class="form-control" placeholder="Enter promo code">
                            <button type="button" class="btn btn-outline-secondary" onclick="applyPromoCode()">Apply</button>
                        </div>
                    </div>
                     <hr>
                    <div class="text-center">
                        <p class="mb-1">Your total is <strong class="h5" id="modal-total-amount">UGX <?php echo number_format($total); ?></strong>.</p>
                        <p class="text-muted small"><i class="fas fa-money-bill-wave"></i> Payment: Cash on Delivery</p>
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

<script>
let currentSubtotal = <?php echo $subtotal; ?>;
let currentDiscount = 0;

function updateDeliveryFee() {
    const select = document.getElementById("delivery_location");
    const option = select.options[select.selectedIndex];
    const fee = option.value ? parseFloat(option.getAttribute("data-fee")) : 0;
    
    // Format helper
    const formatUGX = (num) => String(Math.round(num)).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    // Update displays
    document.getElementById("delivery-fee").innerText = "UGX " + formatUGX(fee);
    
    const newTotal = currentSubtotal + fee - currentDiscount;
    document.getElementById("total-amount").innerText = "UGX " + formatUGX(newTotal);
    document.getElementById("modal-total-amount").innerText = "UGX " + formatUGX(newTotal);
}

function filterDeliveryAreas() {
    const searchInput = document.getElementById('delivery_area_search');
    const locationSelect = document.getElementById('delivery_location');

    if (!searchInput || !locationSelect) {
        return;
    }

    const searchTerm = searchInput.value.trim().toLowerCase();
    let hasVisibleSelected = false;

    for (let i = 0; i < locationSelect.options.length; i++) {
        const option = locationSelect.options[i];

        if (i === 0) {
            option.hidden = false;
            continue;
        }

        const optionText = option.text.toLowerCase();
        const isMatch = searchTerm === '' || optionText.includes(searchTerm);
        option.hidden = !isMatch;

        if (isMatch && option.selected) {
            hasVisibleSelected = true;
        }
    }

    if (!hasVisibleSelected && locationSelect.value !== '') {
        locationSelect.value = '';
        updateDeliveryFee();
    }
}

document.getElementById('delivery_area_search')?.addEventListener('input', filterDeliveryAreas);

document.getElementById('checkoutModal')?.addEventListener('shown.bs.modal', function() {
    filterDeliveryAreas();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>