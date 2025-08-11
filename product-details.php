<?php
session_start();
include_once 'config/database.php';

$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Get product details
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.status = 'active'
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: products.php');
        exit;
    }

    // Get related products
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE category_id = ? AND id != ? AND status = 'active' 
        ORDER BY RAND() 
        LIMIT 4
    ");
    $stmt->execute([$product['category_id'], $product_id]);
    $related_products = $stmt->fetchAll();

} catch (Exception $e) {
    header('Location: products.php');
    exit;
}

$page_title = $product['name'];
include_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <!-- Product Image -->
        <div class="col-lg-6 mb-4">
            <div class="product-image-container">
                <img src="<?php echo $product['image'] ?: 'assets/images/placeholder.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="img-fluid rounded shadow">
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                    <?php if ($product['category_name']): ?>
                        <li class="breadcrumb-item">
                            <a href="products.php?category=<?php echo urlencode($product['category_name']); ?>">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>

            <h1 class="h2 mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <?php if ($product['featured']): ?>
                <span class="badge bg-warning text-dark mb-3">
                    <i class="fas fa-star"></i> Featured Product
                </span>
            <?php endif; ?>

            <div class="price-section mb-4">
                <h3 class="text-primary mb-0">UGX <?php echo number_format($product['price']); ?></h3>
                <?php if ($product['stock_quantity'] > 0): ?>
                    <small class="text-success">
                        <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock_quantity']; ?> available)
                    </small>
                <?php else: ?>
                    <small class="text-warning">
                        <i class="fas fa-exclamation-triangle"></i> Limited Stock
                    </small>
                <?php endif; ?>
            </div>

            <?php if ($product['description']): ?>
                <div class="product-description mb-4">
                    <h5>Description</h5>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($product['flavours']): ?>
                <div class="product-flavours mb-4">
                    <h5>Available Flavours</h5>
                    <div class="flavour-tags">
                        <?php 
                        $flavours = explode(',', $product['flavours']);
                        foreach ($flavours as $flavour): 
                        ?>
                            <span class="badge bg-light text-dark me-2 mb-2">
                                <?php echo htmlspecialchars(trim($flavour)); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($product['ingredients']): ?>
                <div class="product-ingredients mb-4">
                    <h5>Main Ingredients</h5>
                    <p class="text-muted"><?php echo htmlspecialchars($product['ingredients']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Add to Cart Section -->
            <div class="add-to-cart-section">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <div class="quantity-controls d-flex">
                            <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="quantity" class="form-control text-center mx-2" value="1" min="1" style="width: 80px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <button class="btn btn-primary btn-lg w-100" onclick="addToCartWithQuantity()">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <p class="text-muted mt-2">
                        <i class="fas fa-info-circle"></i> 
                        <a href="auth/login.php" class="text-decoration-none">Login</a> required to add items to cart
                    </p>
                <?php endif; ?>
            </div>

            <!-- Product Info Tabs -->
            <div class="product-tabs mt-5">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button">
                            Product Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#delivery" type="button">
                            Delivery Info
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="productTabsContent">
                    <div class="tab-pane fade show active" id="details" role="tabpanel">
                        <div class="p-3">
                            <h6>Product Information</h6>
                            <ul class="list-unstyled">
                                <li><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></li>
                                <li><strong>Availability:</strong> Made to order</li>
                                <li><strong>Shelf Life:</strong> Best consumed within 3-5 days</li>
                                <li><strong>Storage:</strong> Store in cool, dry place</li>
                            </ul>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="delivery" role="tabpanel">
                        <div class="p-3">
                            <h6>Delivery Information</h6>
                            <ul class="list-unstyled">
                                <li><strong>Delivery Areas:</strong> Kampala and surrounding areas</li>
                                <li><strong>Delivery Time:</strong> Same day or next day</li>
                                <li><strong>Delivery Fee:</strong> UGX 5,000 (Free for orders above UGX 50,000)</li>
                                <li><strong>Order Timing:</strong> Order before 2 PM for same-day delivery</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div class="related-products mt-5">
            <h3 class="section-title text-center mb-4">You May Also Like</h3>
            <div class="row">
                <?php foreach ($related_products as $related): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-card">
                            <img src="<?php echo $related['image'] ?: 'assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>" 
                                 class="product-image">
                            <div class="product-info">
                                <h6 class="product-name"><?php echo htmlspecialchars($related['name']); ?></h6>
                                <p class="product-price">UGX <?php echo number_format($related['price']); ?></p>
                                <div class="d-flex gap-2">
                                    <a href="product-details.php?id=<?php echo $related['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">View</a>
                                    <button onclick="addToCart(<?php echo $related['id']; ?>)" 
                                            class="btn btn-primary btn-sm">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.product-image-container {
    position: relative;
    overflow: hidden;
}

.product-image-container img {
    transition: transform 0.3s ease;
}

.product-image-container:hover img {
    transform: scale(1.05);
}

.price-section h3 {
    font-size: 2rem;
    font-weight: 700;
}

.flavour-tags .badge {
    font-size: 0.9rem;
    padding: 0.5rem 0.8rem;
    border: 1px solid #dee2e6;
}

.quantity-controls {
    max-width: 200px;
}

.nav-tabs .nav-link {
    color: var(--text-dark);
    border: 1px solid transparent;
}

.nav-tabs .nav-link.active {
    color: var(--primary-color);
    border-color: var(--primary-color) var(--primary-color) transparent;
}

.tab-content {
    border: 1px solid #dee2e6;
    border-top: none;
    background: white;
}
</style>

<script>
function changeQuantity(change) {
    const quantityInput = document.getElementById('quantity');
    let currentValue = parseInt(quantityInput.value);
    let newValue = currentValue + change;
    
    if (newValue < 1) newValue = 1;
    
    quantityInput.value = newValue;
}

function addToCartWithQuantity() {
    const quantity = document.getElementById('quantity').value;
    addToCart(<?php echo $product_id; ?>, quantity);
}

// Make userLoggedIn available to main.js
<?php if (isset($_SESSION['user_id'])): ?>
var userLoggedIn = true;
<?php else: ?>
var userLoggedIn = false;
<?php endif; ?>
</script>

<?php include_once 'includes/footer.php'; ?>
