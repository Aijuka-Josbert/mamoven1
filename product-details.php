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

// Handle base64 or fallback to placeholder
if (!empty($product['image']) && strpos($product['image'], 'data:image/') === 0) {
    $image_url = $product['image'];
} else {
    $image_url = BASE_URL . '/assets/images/placeholder.jpg';
}
?>

<div class="container my-5">
    <div class="row">
        <!-- Product Image -->
        <div class="col-lg-6 mb-4">
            <div class="product-card">
                 <div class="product-image-wrapper" style="height: 450px;">
                    <!-- Main Product Image -->
                    <img src="<?php echo htmlspecialchars($image_url); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image">
                </div>
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
            
            <?php if (!empty($product['flavours'])): ?>
                <p><strong>Flavours:</strong>
                    <?php
                        // display flavours as comma-separated plain text or as badges
                        echo htmlspecialchars($product['flavours']);
                    ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($product['ingredients'])): ?>
                <p><strong>Ingredients:</strong> <?php echo nl2br(htmlspecialchars($product['ingredients'])); ?></p>
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
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="row mt-5 pt-5 border-top">
        <div class="col-lg-8">
            <h3 class="section-title mb-4">Customer Reviews</h3>

            <!-- Reviews List -->
            <div id="reviews-container" class="mb-4">
                <p class="text-muted text-center"><i class="fas fa-spinner fa-spin"></i> Loading reviews...</p>
            </div>

            <!-- Review Form -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="mb-0">Leave a Review</h5>
                </div>
                <div class="card-body">
                    <form id="review-form" class="mt-3">
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
                            <textarea id="comment" name="comment" class="form-control" rows="3" placeholder="Share your experience with this product..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i> Submit Review
                        </button>
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
                         <div class="product-card">
                            <a href="<?php echo BASE_URL . '/product-details.php?id=' . (int)$related['id']; ?>" class="product-image-wrapper">
                                <!-- Related Product Images -->
                                <img src="<?php echo htmlspecialchars($related['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>" class="product-image">
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