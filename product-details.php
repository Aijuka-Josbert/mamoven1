<?php
require_once __DIR__ . '/includes/header.php';

// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . 'products.php');
    exit;
}
$product_id = (int)$_GET['id'];

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
        // If product doesn't exist or is inactive, redirect.
        header("Location: " . 'products.php?status=notfound');
        exit;
    }

    // Get reviews and average rating
    $reviews_stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
        FROM reviews 
        WHERE product_id = ?
    ");
    $reviews_stmt->execute([$product_id]);
    $review_stats = $reviews_stmt->fetch();

    // Get related products from the same category
    $related_stmt = $pdo->prepare("
        SELECT id, name, price, image 
        FROM products 
        WHERE category_id = ? AND id != ? AND status = 'active' 
        ORDER BY RAND() 
        LIMIT 4
    ");
    $related_stmt->execute([$product['category_id'], $product_id]);
    $related_products = $related_stmt->fetchAll();

} catch (PDOException $e) {
    // Redirect on database error
    header("Location: " . 'products.php?status=error');
    exit;
}

$page_title = htmlspecialchars($product['name']);

// Resolve the product image (file path, legacy base64, or placeholder)
$image_url = product_image_url($product['image']);

// Split flavours into individual chips for display
$flavour_chips = [];
if (!empty($product['flavours'])) {
    $flavour_chips = array_filter(array_map('trim', explode(',', $product['flavours'])));
}
?>

<div class="container my-5">
    <div class="row">
        <!-- Product Image -->
        <div class="col-lg-6 mb-4">
            <div class="pd-image-panel">
                <?php if (!empty($product['category_name'])): ?>
                    <span class="pd-category-badge"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <?php endif; ?>
                <?php if (!empty($product['featured'])): ?>
                    <span class="pd-featured-badge"><i class="fas fa-star"></i> Bestseller</span>
                <?php endif; ?>
                <span class="pd-blob-frame">
                    <img src="<?php echo htmlspecialchars($image_url); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="pd-hero-image organic-blob">
                </span>
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6 ps-lg-5">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href='index.php'>Home</a></li>
                    <li class="breadcrumb-item"><a href='products.php'>Products</a></li>
                </ol>
            </nav>

            <h1 class="display-5 mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="product-price h2 mb-4">UGX <?php echo number_format($product['price']); ?></p>

            <!-- Rating Display -->
            <?php if ($review_stats['review_count'] > 0): ?>
            <div class="mb-3">
                <div class="stars" style="color: #FFD700; font-size: 1.2rem;">
                    <?php 
                    $rating = round($review_stats['avg_rating']);
                    for ($i = 0; $i < $rating; $i++): ?>★<?php endfor; ?>
                    <?php for ($i = $rating; $i < 5; $i++): ?>☆<?php endfor; ?>
                </div>
                <small class="text-muted"><?php echo round($review_stats['avg_rating'], 1); ?> out of 5 stars (<?php echo $review_stats['review_count']; ?> reviews)</small>
            </div>
            <?php endif; ?>

            <!-- Stock Status -->
            <div class="mb-4">
                <?php if ($product['stock_quantity'] > 10): ?>
                    <span class="stock-badge in-stock"><i class="fas fa-check-circle me-1"></i> In Stock</span>
                <?php elseif ($product['stock_quantity'] > 0): ?>
                    <span class="stock-badge low-stock"><i class="fas fa-exclamation-circle me-1"></i> Only <?php echo $product['stock_quantity']; ?> left!</span>
                <?php else: ?>
                    <span class="stock-badge out-of-stock"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
                <?php endif; ?>
            </div>

            <p class="lead text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <?php if (!empty($flavour_chips)): ?>
                <div class="pd-attribute-block">
                    <span class="pd-attribute-label"><i class="fas fa-ice-cream me-1"></i> Flavours</span>
                    <div class="pd-chip-row">
                        <?php foreach ($flavour_chips as $flavour): ?>
                            <span class="pd-chip"><?php echo htmlspecialchars($flavour); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($product['ingredients'])): ?>
                <div class="pd-attribute-block">
                    <span class="pd-attribute-label"><i class="fas fa-leaf me-1"></i> Ingredients</span>
                    <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($product['ingredients'])); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Add to Cart Section -->
            <div class="card bg-light border-0 p-3 mt-4">
                <div class="row align-items-center">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label for="quantity" class="form-label fw-bold">Quantity:</label>
                        <input type="number" id="quantity" class="form-control text-center" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                    </div>
                    <div class="col-md-7 d-grid">
                        <button class="btn btn-primary btn-lg" onclick="addToCart(<?php echo $product_id; ?>, document.getElementById('quantity').value)">
                            <i class="fas fa-shopping-bag me-2"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Trust Badges -->
            <div class="pd-trust-row">
                <div class="pd-trust-item"><i class="fas fa-bread-slice"></i> Baked Fresh</div>
                <div class="pd-trust-item"><i class="fas fa-truck"></i> Cash on Delivery</div>
                <div class="pd-trust-item"><i class="fas fa-shield-alt"></i> Quality Assured</div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="row mt-5 pt-5 border-top">
        <div class="col-lg-8">
            <h3 class="section-title mb-4">Customer Reviews</h3>

            <div id="reviews-summary" class="reviews-summary-panel mb-4 d-none">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="summary-main-rating">
                        <span class="summary-average" id="summary-average-rating">0.0</span>
                        <div class="summary-meta">
                            <div class="summary-stars" id="summary-stars">☆☆☆☆☆</div>
                            <small class="text-muted"><span id="summary-review-count">0</span> review(s)</small>
                        </div>
                    </div>
                    <div class="summary-verified text-muted small">
                        <i class="fas fa-badge-check me-1"></i><span id="summary-verified-count">0</span> verified purchase review(s)
                    </div>
                </div>
                <div id="rating-breakdown" class="rating-breakdown mt-3"></div>
            </div>

            <!-- Reviews List -->
            <div id="reviews-container" class="mb-4">
                <p class="text-muted text-center"><i class="fas fa-spinner fa-spin"></i> Loading reviews...</p>
            </div>

            <!-- Review Form -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="mb-0" id="review-form-title">Leave a Review</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3 review-help-text">Your feedback helps other customers choose better. You can edit your review anytime.</p>
                    <form id="review-form" class="mt-3">
                        <input type="hidden" name="review_id" id="review-id-input" value="">
                        <div class="mb-3">
                            <label class="form-label">Rating <span class="text-danger">*</span></label>
                            <div class="rating-stars-input" id="interactive-stars">
                                <input type="hidden" name="rating" id="rating-input" value="0" required>
                                <span class="star-interactive" data-value="1">★</span>
                                <span class="star-interactive" data-value="2">★</span>
                                <span class="star-interactive" data-value="3">★</span>
                                <span class="star-interactive" data-value="4">★</span>
                                <span class="star-interactive" data-value="5">★</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Your Review</label>
                            <textarea id="comment" name="comment" class="form-control" rows="3" maxlength="500" placeholder="Share your experience with this product..."></textarea>
                            <div class="text-end mt-1"><small class="text-muted" id="review-char-counter">0/500</small></div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary" id="review-submit-btn">
                                <i class="fas fa-paper-plane me-2"></i> Submit Review
                            </button>
                            <button type="button" class="btn btn-outline-secondary d-none" id="review-cancel-btn">
                                Cancel Edit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <p class="alert alert-info"><a href="auth/login.php">Login</a> to leave a review.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div class="related-products mt-5 pt-5 border-top">
            <h3 class="section-title text-center mb-5">You May Also Like</h3>
            <div class="row gy-4">
                <?php foreach ($related_products as $related): ?>
                    <div class="col-lg-3 col-md-6">
                         <div class="product-card catalog-card">
                            <a href="<?php echo BASE_URL . '/product-details.php?id=' . (int)$related['id']; ?>" class="product-image-wrapper catalog-image-frame">
                                <span class="blob-frame">
                                    <img src="<?php echo htmlspecialchars(product_image_url($related['image'])); ?>" 
                                         alt="<?php echo htmlspecialchars($related['name']); ?>" class="product-image organic-blob">
                                </span>
                                <span class="quick-view-overlay">
                                    <span class="quick-view-btn"><i class="fas fa-eye me-1"></i> Quick View</span>
                                </span>
                            </a>
                            <div class="product-info">
                                <div>
                                    <h5 class="product-name">
                                        <a href="<?php echo BASE_URL . '/product-details.php?id=' . (int)$related['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($related['name']); ?>
                                        </a>
                                    </h5>
                                    <p class="product-price">UGX <?php echo number_format($related['price']); ?></p>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary" onclick="addToCart(<?php echo $related['id']; ?>)">
                                        <i class="fas fa-shopping-bag me-2"></i> Add to Cart
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>