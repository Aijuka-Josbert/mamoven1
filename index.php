<?php
$page_title = 'Welcome to Mama\'s Oven';
require_once __DIR__ . '/includes/header.php';

// Fetch featured products from the database
try {
    $stmt = $pdo->query("
        SELECT id, name, price, image, description 
        FROM products 
        WHERE status = 'active'
        ORDER BY created_at DESC 
        LIMIT 6
    ");
    $featured_products = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_products = [];
}

// Fetch top 7 best reviews globally
try {
    $stmt_rev = $pdo->query("
                SELECT r.rating, COALESCE(NULLIF(TRIM(r.comment), ''), 'No written comment provided yet.') as review, u.full_name, p.name as product_name
                FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN products p ON r.product_id = p.id
        WHERE p.status = 'active' AND TRIM(COALESCE(r.comment, '')) <> ''
        ORDER BY r.rating DESC, r.created_at DESC
        LIMIT 7
    ");
    $top_reviews = $stmt_rev->fetchAll();
} catch (PDOException $e) {
    $top_reviews = [];
}

$relevance_products = [];
$relevance_title = 'Trending Right Now';
$relevance_note = 'Based on customer activity and product ratings.';

try {
    $viewer_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    if ($viewer_user_id > 0) {
        $pref_stmt = $pdo->prepare("\
            SELECT p.category_id, COUNT(*) as order_hits
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON p.id = oi.product_id
            WHERE o.user_id = ? AND p.category_id IS NOT NULL
            GROUP BY p.category_id
            ORDER BY order_hits DESC
            LIMIT 3
        ");
        $pref_stmt->execute([$viewer_user_id]);

        $preferred_categories = array_map('intval', array_column($pref_stmt->fetchAll(), 'category_id'));
        $preferred_categories = array_values(array_filter($preferred_categories, static function ($v) {
            return $v > 0;
        }));

        if (!empty($preferred_categories)) {
            $in_clause = implode(',', array_fill(0, count($preferred_categories), '?'));
            $params = $preferred_categories;
            $params[] = $viewer_user_id;

            $relevance_stmt = $pdo->prepare("\
                SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.image,
                    p.stock_quantity,
                    c.name as category_name,
                    COALESCE(AVG(r.rating), 0) as avg_rating,
                    COUNT(r.id) as review_count,
                    p.featured,
                    p.created_at
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN reviews r ON r.product_id = p.id
                WHERE p.status = 'active'
                  AND p.category_id IN ($in_clause)
                  AND NOT EXISTS (
                      SELECT 1
                      FROM order_items oi2
                      JOIN orders o2 ON o2.id = oi2.order_id
                      WHERE o2.user_id = ? AND oi2.product_id = p.id
                  )
                GROUP BY p.id, p.name, p.price, p.image, p.stock_quantity, c.name, p.featured, p.created_at
                ORDER BY p.featured DESC, avg_rating DESC, review_count DESC, p.created_at DESC
                LIMIT 4
            ");
            $relevance_stmt->execute($params);
            $relevance_products = $relevance_stmt->fetchAll();

            if (!empty($relevance_products)) {
                $relevance_title = 'Picked For You';
                $relevance_note = 'Suggestions based on your recent order categories.';
            }
        }
    }

    if (empty($relevance_products)) {
        $trend_stmt = $pdo->query("\
            SELECT 
                p.id,
                p.name,
                p.price,
                p.image,
                p.stock_quantity,
                c.name as category_name,
                COALESCE(AVG(r.rating), 0) as avg_rating,
                COUNT(r.id) as review_count,
                SUM(CASE WHEN r.is_verified_purchase = 1 THEN 1 ELSE 0 END) as verified_reviews,
                p.featured,
                p.created_at
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN reviews r ON r.product_id = p.id
            WHERE p.status = 'active'
            GROUP BY p.id, p.name, p.price, p.image, p.stock_quantity, c.name, p.featured, p.created_at
            ORDER BY p.featured DESC, verified_reviews DESC, avg_rating DESC, review_count DESC, p.created_at DESC
            LIMIT 4
        ");
        $relevance_products = $trend_stmt->fetchAll();
    }
} catch (PDOException $e) {
    $relevance_products = [];
}

if (empty($relevance_products)) {
    try {
        $fallback_stmt = $pdo->query("\
            SELECT 
                p.id,
                p.name,
                p.price,
                p.image,
                p.stock_quantity,
                c.name as category_name,
                0 as avg_rating,
                0 as review_count,
                p.featured,
                p.created_at
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.status = 'active'
            ORDER BY p.featured DESC, p.created_at DESC
            LIMIT 4
        ");
        $relevance_products = $fallback_stmt->fetchAll();

        if (!empty($relevance_products)) {
            $relevance_note = 'Showing featured products while recommendation data is still building.';
        }
    } catch (PDOException $e) {
        $relevance_products = [];
    }
}
?>

<main>
    <section class="announcement-ribbon">
        <div class="container d-flex flex-wrap justify-content-center gap-3">
            <span><i class="fas fa-truck-fast me-2"></i>Fast bakery delivery across Kampala zones</span>
            <span><i class="fas fa-money-bill-wave me-2"></i>Payment now: Cash on Delivery</span>
            <span><i class="fas fa-bolt me-2"></i>Mobile Money and card payments: Coming Soon</span>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="hero-section hero-premium animate-on-scroll fade-in-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0 text-center text-lg-start">
                    <span class="hero-kicker">Fresh Daily. Made to Delight.</span>
                    <h1 class="hero-title display-3 fw-bold mb-3">Baked with Love,<br><span class="text-primary">Served with Joy</span></h1>
                    <p class="hero-description lead mb-4 pe-lg-4">
                        Discover handcrafted cakes, pastries, and snacks prepared in small batches. From celebration centerpieces to everyday treats,
                        each bite is made for comfort, flavor, and beautiful memories.
                    </p>
                    <div class="hero-pills d-flex flex-wrap justify-content-center justify-content-lg-start gap-2 mb-5">
                        <span class="hero-pill"><i class="fas fa-cake-candles me-2"></i>Custom Celebration Cakes</span>
                        <span class="hero-pill"><i class="fas fa-cookie-bite me-2"></i>Fresh Pastry Batches</span>
                        <span class="hero-pill"><i class="fas fa-shield-heart me-2"></i>Hygienic Packaging</span>
                    </div>
                    <div class="hero-buttons d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                        <a href="products.php" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-sm">Explore Our Menu</a>
                        <a href="about.php" class="btn btn-outline-primary btn-lg rounded-pill px-5 py-3 bg-white shadow-sm">Our Story</a>
                    </div>
                </div>
                <div class="col-lg-6 position-relative text-center">
                    <div class="hero-orb"></div>
                    <img src="assets/image2/new.jpg" alt="Delicious baked goods" class="img-fluid border border-5 border-white shadow-lg organic-blob position-relative z-1 hero-image-focus">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products py-5 animate-on-scroll fade-in-up delay-1">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Featured Treats</h2>
                <p class="lead text-muted">Handpicked favorites, loved by our customers.</p>
            </div>
            <div class="row gy-4">
                <?php if (empty($featured_products)): ?>
                    <div class="col-12">
                        <p class="text-center">No featured products available at the moment. Please check back soon!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($featured_products as $index => $product): ?>
                        <div class="col-lg-4 col-md-6 animate-on-scroll fade-in-up" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                            <div class="product-card showcase-card text-center pb-4">
                                <a href="product-details.php?id=<?php echo $product['id']; ?>" class="product-image-wrapper p-3 d-block">
                                    <img src="<?php echo htmlspecialchars($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image organic-blob">
                                </a>
                                <div class="product-info px-4">
                                    <h5 class="product-name font-weight-bold mt-2">
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h5>
                                    <p class="product-price text-primary fw-bold" style="font-size: 1.2rem;">UGX <?php echo number_format($product['price']); ?></p>
                                    <div class="mt-3">
                                        <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-shopping-bag me-2"></i> Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="text-center mt-5">
                <a href="products.php" class="btn btn-lg btn-outline-primary rounded-pill px-5">View All Products</a>
            </div>
        </div>
    </section>

    <section class="relevance-section py-5 animate-on-scroll fade-in-up delay-2">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <h2 class="section-title mb-2"><?php echo htmlspecialchars($relevance_title); ?></h2>
                    <p class="lead text-muted mb-0"><?php echo htmlspecialchars($relevance_note); ?></p>
                </div>
                <span class="relevance-pill">
                    <i class="fas fa-compass"></i>
                    <?php echo ($relevance_title === 'Picked For You') ? 'Personalized' : 'Community Signal'; ?>
                </span>
            </div>

            <div class="row gy-4">
                <?php if (empty($relevance_products)): ?>
                    <div class="col-12">
                        <p class="text-center text-muted mb-0">No recommendations available right now. Please check our full menu.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($relevance_products as $product): ?>
                        <?php
                            $stock = (int)($product['stock_quantity'] ?? 0);
                            $review_count = (int)($product['review_count'] ?? 0);
                            $avg_rating = round((float)($product['avg_rating'] ?? 0), 1);
                            $image = !empty($product['image']) ? $product['image'] : 'assets/images/placeholder.jpg';
                        ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="product-card relevance-card">
                                <a href="product-details.php?id=<?php echo (int)$product['id']; ?>" class="product-image-wrapper p-3 d-block">
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                                </a>
                                <div class="product-info px-4 pb-4">
                                    <div>
                                        <div class="relevance-meta">
                                            <span class="badge text-bg-light border"><?php echo htmlspecialchars($product['category_name'] ?? 'Bakery'); ?></span>
                                            <span class="relevance-rating"><?php echo $review_count > 0 ? htmlspecialchars($avg_rating . '/5') : 'New'; ?></span>
                                        </div>
                                        <h5 class="product-name mb-2">
                                            <a href="product-details.php?id=<?php echo (int)$product['id']; ?>" class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h5>
                                        <p class="relevance-price mb-2">UGX <?php echo number_format((float)$product['price']); ?></p>
                                        <small class="text-muted d-block mb-3"><?php echo $review_count; ?> review<?php echo $review_count === 1 ? '' : 's'; ?></small>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <a href="product-details.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill">View Product</a>
                                        <button class="btn btn-primary btn-sm rounded-pill" onclick="addToCart(<?php echo (int)$product['id']; ?>)" <?php echo $stock > 0 ? '' : 'disabled'; ?>>
                                            <?php echo $stock > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us py-5 bg-white bg-opacity-75 animate-on-scroll fade-in-up delay-2">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Why Choose Mama's Oven?</h2>
            </div>
            <div class="row text-center">
                <div class="col-md-4 mb-4 feature-box">
                    <i class="fas fa-seedling fa-3x text-primary mb-3"></i>
                    <h4>Finest Ingredients</h4>
                    <p class="text-muted">We use only the freshest, locally-sourced ingredients to ensure superior quality and taste.</p>
                </div>
                <div class="col-md-4 mb-4 feature-box">
                    <i class="fas fa-hand-holding-heart fa-3x text-primary mb-3"></i>
                    <h4>Baked with Passion</h4>
                    <p class="text-muted">Every item is crafted by hand with passion, care, and attention to detail.</p>
                </div>
                <div class="col-md-4 mb-4 feature-box">
                    <i class="fas fa-truck-fast fa-3x text-primary mb-3"></i>
                    <h4>Delivered Fresh</h4>
                    <p class="text-muted">Order online and get our delicious baked goods delivered fresh to your doorstep.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Customer Reviews Section -->
    <section class="customer-reviews-section py-5 animate-on-scroll fade-in-up delay-3" style="overflow: hidden;">
        <div class="container-fluid">
            <div class="text-center mb-5">
                <h2 class="section-title">Loved by Our Customers</h2>
                <p class="lead text-muted">See what our happy customers say about Mama's Oven</p>
            </div>
            
            <div id="customer-reviews-carousel">
                <?php if (!empty($top_reviews)): ?>
                <div class="reviews-ribbon-container">
                    <div class="reviews-ribbon-track">
                        <div class="reviews-ribbon-group">
                            <?php foreach ($top_reviews as $r): ?>
                                <div class="card shadow-sm border-0 reviews-ribbon-card">
                                    <div class="card-body h-100">
                                        <div class="text-warning mb-2">
                                            <?php for($i=1; $i<=5; $i++) {
                                                echo $i <= $r['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            } ?>
                                        </div>
                                        <h5 class="card-title fw-bold">"<?php echo htmlspecialchars($r['review']); ?>"</h5>
                                        <p class="text-muted mb-0 small">- <?php echo htmlspecialchars($r['full_name']); ?> 
                                        <br><span class="text-primary" style="font-size: 0.8rem;">on <?php echo htmlspecialchars($r['product_name']); ?></span></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="reviews-ribbon-group" aria-hidden="true">
                            <?php foreach ($top_reviews as $r): ?>
                                <div class="card shadow-sm border-0 reviews-ribbon-card">
                                    <div class="card-body h-100">
                                        <div class="text-warning mb-2">
                                            <?php for($i=1; $i<=5; $i++) {
                                                echo $i <= $r['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            } ?>
                                        </div>
                                        <h5 class="card-title fw-bold">"<?php echo htmlspecialchars($r['review']); ?>"</h5>
                                        <p class="text-muted mb-0 small">- <?php echo htmlspecialchars($r['full_name']); ?> 
                                        <br><span class="text-primary" style="font-size: 0.8rem;">on <?php echo htmlspecialchars($r['product_name']); ?></span></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <div class="text-center text-muted"><p>No reviews available yet. Be the first to leave one!</p></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script>
function escapeHTML(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function reviewCommentSnippet(comment) {
    const raw = String(comment ?? '').trim();
    if (!raw) {
        return 'No comment text.';
    }

    const shortened = raw.length > 100 ? `${raw.substring(0, 100)}...` : raw;
    return escapeHTML(shortened);
}

function loadDashboardReviews() {
    $.ajax({
        url: BASE_URL + '/api/get_dashboard_reviews.php',
        method: 'GET',
        dataType: 'json',
        cache: false,
        success: function(response) {
            if (response.success && response.products && response.products.length > 0) {
                let reviewsHTML = '<div class="row justify-content-center gy-4">';
                response.products.forEach(function(product, index) {
                    const ratingValue = Math.max(0, Math.min(5, Math.round(Number(product.avg_rating) || 0)));
                    const stars = '⭐'.repeat(ratingValue);
                    const safeName = escapeHTML(product.name || 'Product');
                    const safeReviewer = escapeHTML(product.latest_reviewer || '');
                    const safeImage = escapeHTML(product.image || 'assets/images/placeholder.jpg');
                    const safeComment = reviewCommentSnippet(product.latest_comment);
                    const safeAvg = Number(product.avg_rating || 0).toFixed(1);
                    const reviewCount = Number(product.review_count || 0);
                    reviewsHTML += `
                        <div class="col-lg-4 col-md-6 review-card-wrapper animate-on-scroll fade-in-scale" style="animation-delay: ${index * 0.15}s;">
                            <div class="review-card text-center h-100 p-4 position-relative">
                                <div class="review-product-image mb-3 mx-auto" style="width: 150px; height: 150px;">
                                    <img src="${safeImage}" alt="${safeName}" class="w-100 h-100 object-fit-cover organic-blob">
                                </div>
                                <div class="review-product-info">
                                    <h5 class="fw-bold mb-1">${safeName}</h5>
                                    <div class="rating-stars text-warning mb-2">${stars} <span class="rating-text text-muted small">${safeAvg}/5</span></div>
                                    <p class="review-count small text-muted mb-3">${reviewCount} customer reviews</p>
                                    
                                    <div class="review-comment-box bg-light rounded p-3 mb-3 position-relative">
                                        <i class="fas fa-quote-left text-primary opacity-25 position-absolute top-0 start-0 pt-2 ps-2"></i>
                                        <p class="review-comment fst-italic small mb-0 position-relative z-1">"${safeComment}"</p>
                                    </div>
                                    
                                    ${safeReviewer ? `<p class="reviewer-name fw-bold small">- ${safeReviewer}</p>` : ''}
                                    <a href="product-details.php?id=${product.id}" class="btn btn-sm btn-primary rounded-pill px-4 mt-2">View Product</a>
                                </div>
                            </div>
                        </div>
                    `;
                });
                reviewsHTML += '</div>';
                $('#customer-reviews-carousel').html(reviewsHTML);
            } else {
                $('#customer-reviews-carousel').html('<p class="text-center text-muted">No reviews yet.</p>');
            }
        },
        error: function() {
            $('#customer-reviews-carousel').html('<p class="text-center text-danger">Failed to load reviews.</p>');
        }
    });
}

$(document).ready(function() {
    loadDashboardReviews();
});
</script>

<?php if(isset($_GET['registered'])): ?>
<script>
    showSuccess('Your account has been created successfully! Welcome to Mama\'s Oven.');
</script>
<?php endif; ?>

<?php if(isset($_GET['logout'])): ?>
<script>
    showSuccess('You have been logged out successfully.');
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
