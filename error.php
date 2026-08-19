<?php
$code = (int)($_GET['code'] ?? 404);
$messages = [
    403 => ['title' => 'Access Denied', 'text' => "You don't have permission to view this page."],
    404 => ['title' => 'Page Not Found', 'text' => "The page you're looking for doesn't exist or may have moved."],
    500 => ['title' => 'Something Went Wrong', 'text' => "We hit an unexpected error on our end. Please try again shortly."],
];
$info = $messages[$code] ?? $messages[404];
http_response_code($code);

$page_title = $info['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5 text-center">
    <div class="py-5">
        <i class="fas fa-cookie-bite fa-4x mb-4" style="color: #F39C6A;"></i>
        <h1 class="display-5 mb-3"><?php echo htmlspecialchars($info['title']); ?></h1>
        <p class="lead text-muted mb-4"><?php echo htmlspecialchars($info['text']); ?></p>
        <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-primary btn-lg rounded-pill px-4">
            <i class="fas fa-home me-2"></i> Back to Home
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
