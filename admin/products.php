<?php
$page_title = 'Manage Products';
require_once __DIR__ . '/includes/header.php';
require_admin();

// Handle product deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    try {
        // Check if product exists first
        $check_stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $check_stmt->execute([$product_id]);
        if ($check_stmt->fetch()) {
            // Delete the product completely from the database
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $success_message = "Product has been permanently deleted.";
        } else {
            $error_message = "Product not found.";
        }
    } catch(PDOException $e) {
        $error_message = "Failed to delete product: " . $e->getMessage();
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
                                    <?php if (!empty($product['image']) && strpos($product['image'], 'data:image/') === 0): ?>
                                        <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <img src="<?php echo BASE_URL; ?>/assets/images/placeholder.jpg" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <?php endif; ?>
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
                                    <a href="javascript:void(0);" onclick="confirmDelete('products.php?action=delete&id=<?php echo $product['id']; ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>