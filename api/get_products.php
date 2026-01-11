<?php
include_once '../config/database.php';

header('Content-Type: application/json');

$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $where_conditions = ["p.status = 'active'"];
    $params = [];

    if ($category) {
        $where_conditions[] = "c.name = ?";
        $params[] = $category;
    }

    if ($search) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $where_clause = implode(' AND ', $where_conditions);

    // Get total count
    $count_sql = "SELECT COUNT(*) FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_products = $count_stmt->fetchColumn();

    // Get products
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE $where_clause 
            ORDER BY p.featured DESC, p.created_at DESC 
            LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Format products
    foreach ($products as &$product) {
        $product['price'] = (float)$product['price'];
        $product['image'] = $product['image'] ?: 'assets/images/placeholder.jpg';
    }

    $total_pages = ceil($total_products / $limit);

    echo json_encode([
        'products' => $products,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_products' => $total_products,
            'per_page' => $limit
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
