<?php
$page_title = 'Our Products';
require_once __DIR__ . '/includes/header.php';

// Get filter and search parameters from the URL
$category_filter = $_GET['category'] ?? '';
$search_query = trim($_GET['search'] ?? '');

// Fetch categories for the filter dropdown
try {
    $categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Base SQL query
$sql = "SELECT p.id, p.name, p.price, p.image, p.description, p.stock_quantity, p.featured, c.name AS category_name
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'";
$params = [];

// Add category filter to the query if selected
if (!empty($category_filter)) {
    $sql .= " AND c.name = ?";
    $params[] = $category_filter;
}

// Add search filter to the query if a search term is provided
if (!empty($search_query)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.flavours LIKE ? OR p.ingredients LIKE ?)";
    $search_term = '%' . $search_query . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$sql .= " ORDER BY p.featured DESC, p.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $error_message = "Could not fetch products. Please try again later.";
}
?>

<div class="container my-5 products-page">
    <!-- Page Header -->
    <div class="products-hero text-center mb-4">
        <span class="products-hero-kicker">Freshly Baked • Daily</span>
        <h1 class="section-title">Our Delicious Menu</h1>
        <p class="lead text-muted mb-2">Browse our selection of handcrafted cakes, snacks, and pastries.</p>
        <p class="small text-muted mb-0">
            Current payment method: <strong>Cash on Delivery</strong>. Other payment methods are coming soon.
        </p>
    </div>

    <!-- Trust Strip -->
    <div class="trust-strip mb-5">
        <div class="trust-strip-item">
            <i class="fas fa-bread-slice"></i>
            <span>Baked Fresh Daily</span>
        </div>
        <div class="trust-strip-item">
            <i class="fas fa-heart"></i>
            <span>Handcrafted With Love</span>
        </div>
        <div class="trust-strip-item">
            <i class="fas fa-leaf"></i>
            <span>Quality Ingredients</span>
        </div>
        <div class="trust-strip-item">
            <i class="fas fa-truck"></i>
            <span>Cash on Delivery</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm filter-shell">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label for="category-filter" class="form-label">Filter by Category:</label>
                            <select class="form-select" id="category-filter" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['name']); ?>" 
                                            <?php echo ($category_filter === $category['name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="search-input" class="form-label">Search Products:</label>
                            <input type="text" class="form-control" id="search-input" name="search"
                                   placeholder="e.g., Chocolate Cake" 
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (!isset($error_message)): ?>
        <div class="products-meta d-flex flex-wrap justify-content-between align-items-center mb-4">
            <p class="mb-2 mb-md-0 text-muted">
                Showing <strong><?php echo count($products); ?></strong> product<?php echo count($products) === 1 ? '' : 's'; ?>
            </p>
            <div class="d-flex flex-wrap gap-2">
                <?php if ($category_filter !== ''): ?>
                    <span class="badge rounded-pill text-bg-light border">Category: <?php echo htmlspecialchars($category_filter); ?></span>
                <?php endif; ?>
                <?php if ($search_query !== ''): ?>
                    <span class="badge rounded-pill text-bg-light border">Search: <?php echo htmlspecialchars($search_query); ?></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Products Grid -->
    <div class="row gy-4">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    <h4 class="alert-heading">No Products Found</h4>
                    <p>We couldn't find any products matching your criteria. Try adjusting your filters or search term.</p>
                    <hr>
                    <a href="products.php" class="btn btn-outline-primary">Clear Filters</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $index => $product): ?>
                <div class="col-lg-4 col-md-6 animate-on-scroll fade-in-up" style="animation-delay: <?php echo $index * 0.08; ?>s;">
                    <div class="product-card catalog-card">
                        <?php if (!empty($product['featured'])): ?>
                            <span class="catalog-ribbon"><i class="fas fa-star"></i> Bestseller</span>
                        <?php endif; ?>
                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="product-image-wrapper catalog-image-frame">
                            <span class="blob-frame">
                                <img src="<?php echo htmlspecialchars(product_image_url($product['image'])); ?>" 
     alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image organic-blob">
                            </span>
                            <span class="quick-view-overlay">
                                <span class="quick-view-btn"><i class="fas fa-eye me-1"></i> Quick View</span>
                            </span>
                        </a>
                        <div class="product-info">
                            <div>
                                <?php if (!empty($product['category_name'])): ?>
                                    <span class="catalog-category-chip"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                <?php endif; ?>
                                <h5 class="product-name">
                                    <a href="product-details.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h5>
                                <p class="product-price">UGX <?php echo number_format($product['price']); ?></p>
                                <?php
                                $productDescription = (string)($product['description'] ?? '');
                                if (strlen($productDescription) > 80) {
                                    $productDescription = substr($productDescription, 0, 80) . '...';
                                }
                                ?>
                                <p class="small text-muted mb-2"><?php echo htmlspecialchars($productDescription); ?></p>
                                <div class="mb-2">
                                    <?php if ($product['stock_quantity'] > 10): ?>
                                        <span class="stock-badge in-stock"><i class="fas fa-check-circle me-1"></i> In Stock</span>
                                    <?php elseif ($product['stock_quantity'] > 0): ?>
                                        <span class="stock-badge low-stock"><i class="fas fa-exclamation-circle me-1"></i> Only <?php echo $product['stock_quantity']; ?> left!</span>
                                    <?php else: ?>
                                        <span class="stock-badge out-of-stock"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-primary w-100" onclick="addToCart(<?php echo $product['id']; ?>)" <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-shopping-bag me-2"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>