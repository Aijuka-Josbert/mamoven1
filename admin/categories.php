<?php

$page_title = 'Manage Categories';
require_once __DIR__ . '/includes/header.php';
require_admin();

$errors = [];
$success_message = '';

// Handle form submissions for adding or deleting a category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- ADD CATEGORY ---
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if (empty($name)) {
            $errors[] = "Category name is required.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description]);
                $success_message = "Category '" . htmlspecialchars($name) . "' was added successfully!";
            } catch (PDOException $e) {
                $errors[] = "Database error: Could not add category. It may already exist.";
            }
        }
    }
}

// --- DELETE CATEGORY ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session security token is missing or expired. Please refresh the page and try again.';
    } else {
    $category_id = (int)$_POST['id'];
    try {
        // First, check if any products are using this category
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $check_stmt->execute([$category_id]);
        if ($check_stmt->fetchColumn() > 0) {
            $errors[] = "Cannot delete category. It is currently assigned to one or more products.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $success_message = "Category has been successfully deleted.";
        }
    } catch(PDOException $e) {
        $errors[] = "Failed to delete category: " . $e->getMessage();
    }
    }
}

// Fetch all categories for display
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Could not fetch categories: " . $e->getMessage();
    $categories = [];
}
?>

<h1 class="h3 mb-4">Manage Categories</h1>

<div class="row">
    <!-- Add Category Form -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Add New Category</h6></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="col-lg-8">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <div class="card shadow">
            <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Existing Categories</h6></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr><td colspan="3" class="text-center">No categories found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                                    <td class="text-end">
                                        <form method="POST" id="delete-category-<?php echo $category['id']; ?>" class="d-none">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                        </form>
                                        <button type="button" onclick="confirmDelete('delete-category-<?php echo $category['id']; ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>