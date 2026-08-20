<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Including database.php will also define BASE_URL and SITE_NAME
include_once __DIR__ . '/../config/database.php';

// A simple helper to determine the correct path prefix
function asset_url($path) {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | ' : ''; ?><?php echo SITE_NAME; ?></title>

    <?php
    $seo_description = isset($page_description) && $page_description !== ''
        ? $page_description
        : "Freshly baked cakes, snacks, and pastries delivered across Kampala. Order online from " . SITE_NAME . " — handcrafted daily with quality ingredients.";
    $seo_canonical = BASE_URL . '/' . ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    $seo_image = BASE_URL . '/assets/images/logo.png';
    ?>
    <meta name="description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($seo_canonical); ?>">

    <!-- Open Graph / social sharing -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars(SITE_NAME); ?>">
    <meta property="og:title" content="<?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | ' : ''; ?><?php echo htmlspecialchars(SITE_NAME); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($seo_canonical); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($seo_image); ?>">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Global JS variables -->
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";
        const userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="logo">
                <span class="brand-text"><?php echo SITE_NAME; ?></span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/contact.php">Contact</a></li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/customer_profile.php">My Profile</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/orders.php">My Orders</a></li>
                                <?php if($_SESSION['role'] === 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/dashboard.php">Admin Dashboard</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="<?php echo BASE_URL; ?>/cart.php">
                                <i class="fas fa-shopping-bag"></i>
                                <span class="cart-count badge" id="cart-count">0</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/auth/login.php">Login</a></li>
                        <li class="nav-item"><a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn-primary ms-2">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>