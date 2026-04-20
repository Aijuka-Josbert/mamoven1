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
                SELECT r.rating, COALESCE(r.comment, '') as review, u.full_name, p.name as product_name
                FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN products p ON r.product_id = p.id
        ORDER BY r.rating DESC, r.created_at DESC
        LIMIT 7
    ");
    $top_reviews = $stmt_rev->fetchAll();
} catch (PDOException $e) {
    $top_reviews = [];
}
?>

<main>
    <!-- Hero Section -->
    <section class="hero-section animate-on-scroll fade-in-up" style="background-color: var(--color-background); padding: 80px 0; overflow: visible;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0 text-center text-lg-start">
                    <h1 class="hero-title display-3 fw-bold text-dark mb-3" style="font-family: 'Playfair Display', serif;">Baked with Love,<br><span class="text-primary">Just for You</span></h1>
                    <p class="hero-description lead text-muted mb-5 pe-lg-4">
                        Discover the taste of authentic, homemade goodness. From celebratory cakes to daily delights, every bite is a piece of heaven brought straight to your table.
                    </p>
                    <div class="hero-buttons d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                        <a href="products.php" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-sm" style="font-weight: 600;">Explore Our Menu</a>
                        <a href="about.php" class="btn btn-outline-primary btn-lg rounded-pill px-5 py-3 bg-white shadow-sm" style="font-weight: 600;">Our Story</a>
                    </div>
                </div>
                <div class="col-lg-6 position-relative text-center">
                    <div class="position-absolute top-50 start-50 translate-middle rounded-circle bg-primary opacity-10" style="width: 450px; height: 450px; z-index: 0; filter: blur(50px);"></div>
                    <img src="assets/image2/new.jpg" alt="Delicious baked goods" class="img-fluid border border-5 border-white shadow-lg organic-blob position-relative z-1" style="max-height: 500px; width: 100%; object-fit: cover; animation-duration: 15s;">
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
                            <div class="product-card text-center pb-4">
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
                <div class="reviews-marquee-container" style="background: rgba(243, 156, 106, 0.1); padding: 20px 0; border-radius: 12px; overflow: hidden; white-space: nowrap;">
                    <marquee direction="left" scrollamount="6" onmouseover="this.stop();" onmouseout="this.start();">
                        <div style="display: flex; gap: 30px; align-items: stretch; padding: 10px;">
                            <?php foreach ($top_reviews as $r): ?>
                                <div class="card shadow-sm border-0" style="min-width: 300px; max-width: 350px; display: inline-block; white-space: normal; vertical-align: top;">
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
                    </marquee>
                </div>
                <?php else: ?>
                    <div class="text-center text-muted"><p>No reviews available yet. Be the first to leave one!</p></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script>
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
                    const stars = '⭐'.repeat(Math.round(product.avg_rating));
                    reviewsHTML += `
                        <div class="col-lg-4 col-md-6 review-card-wrapper animate-on-scroll fade-in-scale" style="animation-delay: ${index * 0.15}s;">
                            <div class="review-card text-center h-100 p-4 position-relative">
                                <div class="review-product-image mb-3 mx-auto" style="width: 150px; height: 150px;">
                                    <img src="${product.image || 'assets/images/placeholder.jpg'}" alt="${product.name}" class="w-100 h-100 object-fit-cover organic-blob">
                                </div>
                                <div class="review-product-info">
                                    <h5 class="fw-bold mb-1">${product.name}</h5>
                                    <div class="rating-stars text-warning mb-2">${stars} <span class="rating-text text-muted small">${product.avg_rating}/5</span></div>
                                    <p class="review-count small text-muted mb-3">${product.review_count} customer reviews</p>
                                    
                                    <div class="review-comment-box bg-light rounded p-3 mb-3 position-relative">
                                        <i class="fas fa-quote-left text-primary opacity-25 position-absolute top-0 start-0 pt-2 ps-2"></i>
                                        ${product.latest_comment ? `<p class="review-comment fst-italic small mb-0 position-relative z-1">"${product.latest_comment.substring(0, 100)}${product.latest_comment.length > 100 ? '...' : ''}"</p>` : '<p class="text-muted small mb-0">No comment text.</p>'}
                                    </div>
                                    
                                    ${product.latest_reviewer ? `<p class="reviewer-name fw-bold small">- ${product.latest_reviewer}</p>` : ''}
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

function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // observer.unobserve(entry.target); // Optional: if you want it to trigger only once
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-on-scroll').forEach((el) => {
        observer.observe(el);
    });
}

$(document).ready(function() {
    loadDashboardReviews();
    initScrollAnimations();
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
