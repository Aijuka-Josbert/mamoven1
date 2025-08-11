<?php
session_start();
include_once 'config/database.php';

$page_title = 'Our Products';
include_once 'includes/header.php';

// Get categories for filter
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

$current_category = $_GET['category'] ?? '';
$search_query = $_GET['search'] ?? '';
?>

<div class="container my-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="section-title">Our Delicious Products</h1>
            <p class="lead">Fresh, homemade bakery items made with love</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Category:</label>
                            <select class="form-select" id="category-filter" onchange="filterProducts()">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['name']; ?>" 
                                            <?php echo $current_category === $category['name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Search Products:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search-input" 
                                       placeholder="Search for products..." 
                                       value="<?php echo htmlspecialchars($search_query); ?>">
                                <button class="btn btn-primary" onclick="filterProducts()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="d-flex justify-content-end align-items-end h-100">
                <div class="text-end">
                    <span class="text-muted" id="product-count">Loading products...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row" id="products-container">
        <!-- Products will be loaded here via AJAX -->
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-12">
            <div id="pagination-container"></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadProducts(
        '<?php echo $current_category; ?>', 
        '<?php echo $search_query; ?>'
    );
});

function filterProducts() {
    const category = $('#category-filter').val();
    const search = $('#search-input').val();
    loadProducts(category, search);
    
    // Update URL without reloading page
    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }
    window.history.pushState({}, '', url);
}

function clearFilters() {
    $('#category-filter').val('');
    $('#search-input').val('');
    loadProducts('', '');
    
    // Clear URL parameters
    const url = new URL(window.location);
    url.searchParams.delete('category');
    url.searchParams.delete('search');
    window.history.pushState({}, '', url);
}

function loadProducts(category = '', search = '', page = 1) {
    $.ajax({
        url: 'api/get_products.php',
        method: 'GET',
        data: {
            category: category,
            search: search,
            page: page
        },
        dataType: 'json',
        beforeSend: function() {
            $('#products-container').html('<div class="col-12 text-center"><div class="loading"></div> Loading products...</div>');
            $('#product-count').text('Loading...');
        },
        success: function(response) {
            displayProducts(response.products);
            displayPagination(response.pagination);
            updateProductCount(response.pagination);
        },
        error: function() {
            $('#products-container').html('<div class="col-12"><div class="alert alert-danger">Failed to load products</div></div>');
            $('#product-count').text('Error loading products');
        }
    });
}

function displayProducts(products) {
    let html = '';
    if (products.length === 0) {
        html = '<div class="col-12"><div class="alert alert-info text-center">No products found matching your criteria</div></div>';
    } else {
        products.forEach(function(product) {
            html += `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="product-card" data-product-id="${product.id}">
                        <img src="${product.image || 'assets/images/placeholder.jpg'}" 
                             alt="${product.name}" class="product-image">
                        <div class="product-info">
                            <h5 class="product-name">${product.name}</h5>
                            <p class="product-price">UGX ${product.price.toLocaleString()}</p>
                            <p class="product-description">${product.description || ''}</p>
                            ${product.flavours ? `<p class="text-muted small"><strong>Flavours:</strong> ${product.flavours}</p>` : ''}
                            <div class="d-flex gap-2">
                                <a href="product-details.php?id=${product.id}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <button onclick="addToCart(${product.id})" class="btn btn-primary btn-sm">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    $('#products-container').html(html);
}

function displayPagination(pagination) {
    if (pagination.total_pages <= 1) {
        $('#pagination-container').html('');
        return;
    }

    let html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous page
    if (pagination.current_page > 1) {
        html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadProducts($('#category-filter').val(), $('#search-input').val(), ${pagination.current_page - 1})">
                        Previous
                    </a>
                </li>`;
    }
    
    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === pagination.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else if (i === 1 || i === pagination.total_pages || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="loadProducts($('#category-filter').val(), $('#search-input').val(), ${i})">
                            ${i}
                        </a>
                    </li>`;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next page
    if (pagination.current_page < pagination.total_pages) {
        html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadProducts($('#category-filter').val(), $('#search-input').val(), ${pagination.current_page + 1})">
                        Next
                    </a>
                </li>`;
    }
    
    html += '</ul></nav>';
    $('#pagination-container').html(html);
}

function updateProductCount(pagination) {
    const start = (pagination.current_page - 1) * pagination.per_page + 1;
    const end = Math.min(pagination.current_page * pagination.per_page, pagination.total_products);
    $('#product-count').text(`Showing ${start}-${end} of ${pagination.total_products} products`);
}

// Make userLoggedIn available to main.js
<?php if (isset($_SESSION['user_id'])): ?>
var userLoggedIn = true;
<?php else: ?>
var userLoggedIn = false;
<?php endif; ?>
</script>

<?php include_once 'includes/footer.php'; ?>
