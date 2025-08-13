<?php
$page_title = 'Manage Products';
require_once __DIR__ . '/includes/header.php';

// Handle soft delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$product_id]);
        $success_message = "Product has been successfully deactivated.";
    } catch(PDOException $e) {
        $error_message = "Failed to deactivate product: " . $e->getMessage();
    }
}

// Fetch all products with their category names
try {
    $stmt = $pdo->query("
        SELECT p.id, p.name, p.price, p.image, p.status, p.stock_quantity, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Could not fetch products: " . $e->getMessage();
    $products = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Manage Products</h1>
    <a href="add_product.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Product
    </a>
</div>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="7" class="text-center">No products found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo BASE_URL . '/' . ($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                </td>
                                <td><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                <td>UGX <?php echo number_format($product['price']); ?></td>
                                <td><?php echo $product['stock_quantity']; ?></td>
                                <td>
                                    <span class="badge <?php echo $product['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" onclick="confirmDelete('products.php?action=delete&id=<?php echo $product['id']; ?>')" class="btn btn-sm btn-outline-danger" title="Deactivate">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>```

---

#### `admin/add_product.php`
A fully functional form for adding new products with image uploads.

```php
<?php
$page_title = 'Add New Product';
require_once __DIR__ . '/includes/header.php';

$errors = [];
$inputs = [];

// Fetch categories for the select dropdown
try {
    $categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Could not fetch categories: " . $e->getMessage();
    $categories = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = filter_var($_POST['category_id'] ?? null, FILTER_SANITIZE_NUMBER_INT);
    $stock_quantity = filter_var($_POST['stock_quantity'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
    $status = $_POST['status'] ?? 'active';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // --- Validation ---
    if (empty($name)) $errors[] = "Product name is required.";
    if ($price <= 0) $errors[] = "Price must be a positive number.";
    if (empty($category_id)) $errors[] = "Please select a category.";

    // --- Image Upload Logic ---
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $upload_dir = UPLOAD_PATH;
        
        // Ensure upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Create a unique filename to prevent overwriting
        $filename = uniqid('prod_', true) . '_' . basename($file['name']);
        $target_path = $upload_dir . $filename;
        $relative_path = 'assets/images/products/' . $filename;

        // Check file type and size
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
        } elseif ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
            $errors[] = "Image file is too large. Maximum size is 2MB.";
        } else {
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $image_path = $relative_path;
            } else {
                $errors[] = "Failed to upload the image.";
            }
        }
    }
    
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO products (name, description, price, category_id, stock_quantity, status, featured, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $description, $price, $category_id, $stock_quantity, $status, $featured, $image_path]);
            
            // Redirect with success message
            header("Location: " . 'products.php?status=added');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<h1 class="h3 mb-4">Add New Product</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (UGX) *</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="0">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category *</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="featured" name="featured" value="1">
                        <label class="form-check-label" for="featured">Featured Product</label>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input class="form-control" type="file" id="image" name="image" onchange="previewImage(this, 'imagePreview')">
                        <img id="imagePreview" src="#" alt="Image Preview" class="mt-3 img-fluid rounded" style="display:none; max-height: 200px;">
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Add Product</button>
                <a href="products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>