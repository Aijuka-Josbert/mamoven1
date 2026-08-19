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
        <div class="admin-sidebar-brand">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="admin-sidebar-logo">
            <div>
                <span class="admin-sidebar-title"><?php echo SITE_NAME; ?></span>
                <span class="admin-sidebar-subtitle">Admin Panel</span>
            </div>
        </div>

        <ul class="nav flex-column admin-nav">
            <li class="admin-nav-label">Overview</li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('dashboard.php'); ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt fa-fw"></i> Dashboard
                </a>
            </li>

            <li class="admin-nav-label">Sales</li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('orders.php') || is_active('order_details.php'); ?>" href="orders.php">
                    <i class="fas fa-box-open fa-fw"></i> Manage Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('promo_codes.php'); ?>" href="promo_codes.php">
                    <i class="fas fa-percent fa-fw"></i> Promo Codes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'delivery_locations.php') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/delivery_locations.php">
                    <i class="fas fa-truck fa-fw"></i> Delivery Zones
                </a>
            </li>

            <li class="admin-nav-label">Catalog</li>
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

            <li class="admin-nav-label">Customers &amp; Content</li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('customers.php') || is_active('edit_customer.php'); ?>" href="customers.php">
                    <i class="fas fa-users fa-fw"></i> Manage Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('testimonials.php'); ?>" href="testimonials.php">
                    <i class="fas fa-comment-dots fa-fw"></i> Manage Feedback
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo is_active('contact_messages.php'); ?>" href="contact_messages.php">
                    <i class="fas fa-envelope fa-fw"></i> Contact Messages
                </a>
            </li>

            <li class="nav-item mt-auto">
                <hr>
                <a class="nav-link" href="../index.php" target="_blank">
                    <i class="fas fa-globe fa-fw"></i> View Public Site
                </a>
                <a class="nav-link admin-logout-link" href="<?php echo BASE_URL . '/auth/logout.php'; ?>">
                    <i class="fas fa-sign-out-alt fa-fw"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <main class="admin-main-content flex-grow-1">
        <?php
        // Shared page header banner — renders automatically on every admin
        // page from $page_title, so each page gets a consistent icon/title
        // bar without needing to duplicate this markup individually.
        $admin_page_icons = [
            'Admin Dashboard' => 'fa-tachometer-alt',
            'Manage Orders' => 'fa-box-open',
            'Order Details' => 'fa-receipt',
            'Manage Products' => 'fa-cookie-bite',
            'Add New Product' => 'fa-plus-circle',
            'Edit Product' => 'fa-edit',
            'Manage Categories' => 'fa-tags',
            'Manage Customers' => 'fa-users',
            'Edit User' => 'fa-user-edit',
            'Contact Messages' => 'fa-envelope',
            'Manage Testimonials' => 'fa-comment-dots',
            'Promo Codes' => 'fa-percent',
            'Delivery Locations' => 'fa-truck',
        ];
        $admin_icon = $admin_page_icons[$page_title ?? ''] ?? 'fa-store';
        ?>
        <?php if (!empty($page_title)): ?>
        <div class="admin-page-banner">
            <span class="admin-page-icon"><i class="fas <?php echo $admin_icon; ?>"></i></span>
            <div>
                <h1 class="admin-page-title"><?php echo htmlspecialchars($page_title); ?></h1>
                <span class="admin-page-admin"><i class="fas fa-user-shield me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Administrator'); ?></span>
            </div>
        </div>
        <?php endif; ?>