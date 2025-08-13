<?php
$page_title = 'Welcome to Mama\'s Oven';
require_once __DIR__ . '/includes/header.php';

// Fetch featured products from the database
try {
    $stmt = $pdo->query("
        SELECT id, name, price, image, description 
        FROM products 
        WHERE status = 'active' AND featured = 1 
        ORDER BY created_at DESC 
        LIMIT 6
    ");
    $featured_products = $stmt->fetchAll();
} catch (PDOException $e) {
    // Gracefully handle DB error
    $featured_products = [];
    // Optional: log the error
    // error_log("Error fetching featured products: " . $e->getMessage());
}
?>

<main>
    <!-- Hero Section -->
    <section class="hero-section text-center text-lg-start">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">Baked with Love, Just for You</h1>
                    <p class="hero-description my-4">
                        Discover the taste of authentic, homemade goodness. From celebratory cakes to daily delights, every bite is a piece of heaven.
                    </p>
                    <div class="hero-buttons">
                        <a href="products.php" class="btn btn-primary btn-lg">Explore Our Menu</a>
                        <a href="about.php" class="btn btn-outline-primary btn-lg ms-2">Our Story</a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="assets/images/Untitled.jpeg" alt="A collection of delicious cakes and pastries" class="img-fluid hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products py-5">
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
                    <?php foreach ($featured_products as $product): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="product-card">
                                <a href="product-details.php?id=<?php echo $product['id']; ?>" class="product-image-wrapper">
                                    <img src="<?php echo htmlspecialchars($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                                </a>
                                <div class="product-info">
                                    <div>
                                        <h5 class="product-name">
                                            <a href="product-details.php?id=' . $product['id']; " class="text-decoration-none">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h5>
                                        <p class="product-price">UGX <?php echo number_format($product['price']); ?></p>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
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
                <a href="products.php" class="btn btn-lg btn-outline-primary">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Why Choose Mama's Oven?</h2>
            </div>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <i class="fas fa-seedling fa-3x text-primary mb-3"></i>
                    <h4>Finest Ingredients</h4>
                    <p class="text-muted">We use only the freshest, locally-sourced ingredients to ensure superior quality and taste.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <i class="fas fa-hand-holding-heart fa-3x text-primary mb-3"></i>
                    <h4>Baked with Passion</h4>
                    <p class="text-muted">Every item is crafted by hand with passion, care, and attention to detail.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <i class="fas fa-truck-fast fa-3x text-primary mb-3"></i>
                    <h4>Delivered Fresh</h4>
                    <p class="text-muted">Order online and get our delicious baked goods delivered fresh to your doorstep.</p>
                </div>
            </div>
        </div>
    </section>

</main>

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