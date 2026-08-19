<?php
$page_title = 'Edit Product';
require_once __DIR__ . '/includes/header.php';
require_admin();

// Validate product ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . 'products.php');
    exit;
}
$product_id = (int)$_GET['id'];

// Fetch the product from the database to populate the form
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        // If product not found, redirect with an error message
        header("Location: " . 'products.php?status=notfound');
        exit;
    }
} catch (PDOException $e) {
    // A real site should log this error, not die
    die("Database error fetching product: " . $e->getMessage());
}

$errors = [];

// Fetch categories for the select dropdown
try {
    $categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Could not fetch categories: " . $e->getMessage();
    $categories = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    } else {
        // Sanitize and validate inputs
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
        $image_base64 = $product['image']; // Keep existing base64 image by default

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];

            // Validate file type and size
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/avif', 'image/webp'];
            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = "Invalid file type. Only JPG, PNG, GIF, AVIF and WEBP are allowed.";
            } elseif ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
                $errors[] = "Image file is too large. Maximum size is 2MB.";
            } else {
                // Read the image file content
                $image_data = file_get_contents($file['tmp_name']);
                if ($image_data !== false) {
                    // Convert to base64
                    $image_base64 = 'data:' . $file['type'] . ';base64,' . base64_encode($image_data);
                } else {
                    $errors[] = "Failed to read the image file.";
                }
            }
        }

        if (empty($errors)) {
            try {
                $sql = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, stock_quantity = ?, status = ?, featured = ?, image = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $price, $category_id, $stock_quantity, $status, $featured, $image_base64, $product_id]);

                // Redirect with success message
                header("Location: " . 'products.php?status=updated');
                exit;
            } catch (PDOException $e) {
                $errors[] = "Database update failed: " . $e->getMessage();
            }
        }
        // If there were errors, repopulate the product array with submitted data to keep the form filled
        $product['name'] = $name;
        $product['description'] = $description;
        $product['price'] = $price;
        $product['category_id'] = $category_id;
        $product['stock_quantity'] = $stock_quantity;
        $product['status'] = $status;
        $product['featured'] = $featured;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Edit Product</h1>
    <a href="products.php" class="btn btn-secondary">Back to Products</a>
</div>


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
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (UGX) *</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category *</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?php echo ($product['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($product['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="featured" name="featured" value="1" <?php echo ($product['featured'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="featured">Featured Product</label>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Change Product Image</label>
                        <input class="form-control" type="file" id="image" name="image" onchange="previewImage(this, 'imagePreview')">
                        <p class="form-text">Leave blank to keep the current image.</p>
                        <img id="imagePreview"
                            src="<?php echo $product['image'] ? BASE_URL . '/' . $product['image'] : '#'; ?>"
                            alt="Current Image"
                            class="mt-3 img-fluid rounded"
                            style="<?php echo $product['image'] ? 'display:block;' : 'display:none;'; ?> max-height: 200px;">
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>