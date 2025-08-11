<?php
// Admin Add Product Page
require_once '../config/database.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Handle add product
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $desc = trim($_POST['description']);
    $img = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img = 'assets/images/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], '../' . $img);
    }
    $stmt = $pdo->prepare("INSERT INTO products (name, price, category_id, description, image) VALUES (:name, :price, :category_id, :description, :image)");
    $stmt->execute([
        'name' => $name,
        'price' => $price,
        'category_id' => $category_id,
        'description' => $desc,
        'image' => $img
    ]);
    $msg = 'Product added!';
}
// Fetch categories
$cats = $pdo->query("SELECT id, name FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Add Product</h2>
    <?php if($msg): ?><p><?= $msg ?></p><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Product Name" required><br>
        <input type="number" step="0.01" name="price" placeholder="Price" required><br>
        <select name="category_id" required>
            <option value="">Select Category</option>
            <?php while($cat = $cats->fetch()): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endwhile; ?>
        </select><br>
        <textarea name="description" placeholder="Description" required></textarea><br>
        <input type="file" name="image" accept="image/*" required><br>
        <button type="submit">Add Product</button>
    </form>
</body>
</html>
