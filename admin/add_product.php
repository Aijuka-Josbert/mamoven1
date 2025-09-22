<?php
// Admin Add Product Page
require_once '../config/database.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$page_title = 'Add New Product';
require_once __DIR__ . '/includes/header.php';

$errors = [];

// Fetch categories for the select dropdown
try {
    $categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Could not fetch categories: " . $e->getMessage();
    $categories = [];
}

// Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $flavours = trim($_POST['flavours'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = filter_var($_POST['category_id'] ?? null, FILTER_SANITIZE_NUMBER_INT);
    $stock_quantity = filter_var($_POST['stock_quantity'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
    $status = $_POST['status'] ?? 'active';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // --- Validation ---
    if (empty($name)) $errors[] = "Product name is required.";
    if ($price <= 0) $errors[] = "Price must be a positive number.";
    if (empty($category_id)) $errors[] = "Please select a category.";

    // --- Improved Image Upload Logic ---
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF, JPEG, and WEBP are allowed.";
        } elseif ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
            $errors[] = "Image file is too large. Maximum size is 2MB.";
        } else {
            // Create directory if it doesn't exist
            $upload_dir = '../assets/images/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0775, true)) {
                    $errors[] = "Failed to create upload directory. Please contact administrator.";
                }
            }
            
            // Check if directory is writable
            if (!is_writable($upload_dir)) {
                $errors[] = "Upload directory is not writable. Please check permissions.";
            } else {
                $image_path = 'assets/images/' . basename($file['name']);
                
                if (!move_uploaded_file($file['tmp_name'], '../' . $image_path)) {
                    $errors[] = "Failed to upload the image: " . error_get_last()['message'];
                    $image_path = null;
                }
            }
        }
    } else {
        $errors[] = "Product image is required.";
    }
    
    if (empty($errors)) {
        try {
            // store flavours and ingredients in the products table
            $sql = "INSERT INTO products (name, description, flavours, ingredients, price, category_id, stock_quantity, status, featured, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name, 
                $description, 
                $flavours ?: null,    // allow null if empty
                $ingredients ?: null, // allow null if empty
                $price, 
                $category_id, 
                $stock_quantity, 
                $status, 
                $featured, 
                $image_path
            ]);
            
            // Redirect with success message
            header("Location: products.php?status=added");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Add New Product</h1>
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
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <!-- New: Flavours and Ingredients -->
                    <div class="mb-3">
                        <label for="flavours" class="form-label">Flavours (comma separated)</label>
                        <input type="text" class="form-control" id="flavours" name="flavours" 
                               value="<?php echo htmlspecialchars($_POST['flavours'] ?? ''); ?>" 
                               placeholder="e.g., Vanilla, Chocolate, Red Velvet">
                        <div class="form-text">Enter multiple flavours separated by commas.</div>
                    </div>

                    <div class="mb-3">
                        <label for="ingredients" class="form-label">Ingredients</label>
                        <textarea class="form-control" id="ingredients" name="ingredients" rows="3" placeholder="List main ingredients"><?php echo htmlspecialchars($_POST['ingredients'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (UGX) *</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? '0'); ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category *</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? '' : 'selected'; ?>>Active</option>
                            <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="featured" name="featured" value="1" <?php echo (isset($_POST['featured'])) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="featured">Featured Product</label>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image *</label>
                        <input class="form-control" type="file" id="image" name="image" required onchange="previewImage(this, 'imagePreview')">
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

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
