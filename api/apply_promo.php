<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

require_csrf_or_fail();

$user_id = $_SESSION['user_id'];
$promo_code = strtoupper(trim($_POST['promo_code'] ?? ''));
$subtotal = (float)($_POST['subtotal'] ?? 0);

if (empty($promo_code)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a promo code']);
    exit;
}

try {
    // Get promo code
    $stmt = $pdo->prepare("
        SELECT * FROM promo_codes 
        WHERE code = ? 
        AND status = 'active'
        AND NOW() BETWEEN IFNULL(valid_from, NOW()) AND IFNULL(valid_until, NOW())
    ");
    $stmt->execute([$promo_code]);
    $promo = $stmt->fetch();

    if (!$promo) {
        throw new Exception('Promo code is invalid or expired');
    }

    $usage_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM promo_usage WHERE promo_id = ?");
    $usage_count_stmt->execute([$promo['id']]);
    $usage_count = (int)$usage_count_stmt->fetchColumn();

    // Check if max uses reached
    if ($promo['max_uses'] && $usage_count >= $promo['max_uses']) {
        throw new Exception('This promo code has reached its usage limit');
    }

    // Check minimum order amount
    if ($subtotal < $promo['min_order_amount']) {
        throw new Exception('Minimum order amount for this promo is UGX ' . number_format($promo['min_order_amount']));
    }

    // Check if user has already used this code
    $usage_check = $pdo->prepare("
        SELECT COUNT(*) as count FROM promo_usage 
        WHERE promo_id = ? AND user_id = ?
    ");
    $usage_check->execute([$promo['id'], $user_id]);
    $usage = $usage_check->fetch();

    // Allow multiple uses unless specified
    // For now, we'll allow multiple uses per user

    // Calculate discount safely for both percentage and fixed values.
    $discount = 0.0;
    if ($promo['discount_type'] === 'percentage') {
        $percent = max(0.0, min(100.0, (float)$promo['discount_value']));
        $discount = ($subtotal * $percent) / 100;
    } else {
        $discount = max(0.0, (float)$promo['discount_value']);
    }

    $discount = round(min($discount, $subtotal), 2);

    echo json_encode([
        'success' => true,
        'message' => 'Promo code applied successfully!',
        'discount' => $discount,
        'usage_count' => $usage_count,
        'discount_type' => $promo['discount_type'],
        'discount_value' => $promo['discount_value'],
        'promo_id' => $promo['id'],
        'promo_code' => $promo['code'],
        'description' => $promo['description']
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
