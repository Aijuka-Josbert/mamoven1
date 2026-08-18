<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include base configuration
require_once __DIR__ . '/../../config/database.php';

// Security Check: Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page with an error message
    header('Location: ' . BASE_URL . '/auth/login.php?error=unauthorized');
    exit();
}

// Helper to determine the active page for sidebar styling
function is_active($page_name) {
    return basename($_SERVER['PHP_SELF']) == $page_name ? 'active' : '';
}

// Helper for generating admin URLs
// function admin_url($path) {
//     return BASE_URL . '/admin/' . ltrim($path, '/');
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel'; ?> | <?php echo SITE_NAME; ?></title>
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL . '/assets/css/style.css'; ?>">
</head>
<body class="admin-body">

<div class="d-flex">
    <!-- Sidebar Navigation -->
    <div class="admin-sidebar vh-100 p-3">
        <h4 class="text-center mb-4 fw-bold"><?php echo SITE_NAME; ?></h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('dashboard.php'); ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt fa-fw"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('orders.php'); ?>" href="orders.php">
                    <i class="fas fa-box-open fa-fw"></i> Manage Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('products.php') || is_active('add_product.php') || is_active('edit_product.php'); ?>" href="products.php">
                    <i class="fas fa-cookie-bite fa-fw"></i> Manage Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('categories.php'); ?>" href="categories.php">
                    <i class="fas fa-tags fa-fw"></i> Manage Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('customers.php'); ?>" href="customers.php">
                    <i class="fas fa-users fa-fw"></i> Manage Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('contact_messages.php'); ?>" href="contact_messages.php">
                    <i class="fas fa-envelope fa-fw"></i> Contact Messages
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('testimonials.php'); ?>" href="testimonials.php">
                    <i class="fas fa-comment-dots fa-fw"></i> Manage Feedback
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('promo_codes.php'); ?>" href="promo_codes.php">
                    <i class="fas fa-percent fa-fw"></i> Promo Codes
                </a>
            </li>
            <li class="nav-item mt-auto">
                <hr>
                <a class="nav-link" href="../index.php" target="_blank">
                    <i class="fas fa-globe fa-fw"></i> View Public Site
                </a>
                <a class="nav-link" href="<?php echo BASE_URL . '/auth/logout.php'; ?>">
                    <i class="fas fa-sign-out-alt fa-fw"></i> Logout
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'delivery_locations.php') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/delivery_locations.php">
                    <i class="fas fa-truck fa-fw"></i> Delivery Zones
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <main class="admin-main-content flex-grow-1">