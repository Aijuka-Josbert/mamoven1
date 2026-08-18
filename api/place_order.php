<?php
session_start();
include_once '../config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

require_csrf_or_fail();

$user_id = $_SESSION['user_id'];
$location_id = (int)($_POST['location_id'] ?? 0);
$delivery_address = trim($_POST['delivery_address'] ?? '');
$delivery_phone = trim($_POST['delivery_phone'] ?? '');
$special_instructions = trim($_POST['special_instructions'] ?? '');
$promo_code = strtoupper(trim($_POST['promo_code'] ?? ''));

if (!$location_id || !$delivery_address || !$delivery_phone) {
    echo json_encode(['success' => false, 'message' => 'Delivery location, address, and phone are required']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Get cart items
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.status = 'active'
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }

    // Calculate total
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    // Get delivery fee by location
    $stmt = $pdo->prepare("SELECT name, fee FROM delivery_locations WHERE id = ? AND is_active = 1");
    $stmt->execute([$location_id]);
    $locData = $stmt->fetch();

    if (!$locData) {
        throw new Exception('Invalid delivery location selected');
    }

    $delivery_fee = (float)$locData['fee'];
    $full_delivery_address = $locData['name'] . ' - ' . $delivery_address;

    // Handle promo code if provided
    $promo_code_id = null;
    $discount_amount = 0;
    if (!empty($promo_code)) {
        $promo_stmt = $pdo->prepare(
            "SELECT * FROM promo_codes
             WHERE code = ? AND status = 'active'
             AND NOW() BETWEEN IFNULL(valid_from, NOW()) AND IFNULL(valid_until, NOW())"
        );
        $promo_stmt->execute([$promo_code]);
        $promo = $promo_stmt->fetch();

        if (!$promo) {
            throw new Exception('Promo code is invalid or expired');
        }

        // Check usage limits
        $usage_stmt = $pdo->prepare("SELECT COUNT(*) FROM promo_usage WHERE promo_id = ?");
        $usage_stmt->execute([$promo['id']]);
        $usage_count = (int)$usage_stmt->fetchColumn();

        if ($promo['max_uses'] && $usage_count >= $promo['max_uses']) {
            throw new Exception('This promo code has reached its usage limit');
        }

        // Check minimum order
        if ($subtotal < $promo['min_order_amount']) {
            throw new Exception('Minimum order amount for this promo is UGX ' . number_format($promo['min_order_amount']));
        }

        // Calculate discount safely for both percentage and fixed values.
        if ($promo['discount_type'] === 'percentage') {
            $percent = max(0.0, min(100.0, (float)$promo['discount_value']));
            $discount_amount = ($subtotal * $percent) / 100;
        } else {
            $discount_amount = max(0.0, (float)$promo['discount_value']);
        }

        $discount_amount = round(min($discount_amount, $subtotal), 2);

        $promo_code_id = $promo['id'];
    }

    $total_amount = max(0, round($subtotal + $delivery_fee - $discount_amount, 2));

    // Generate a unique order number using cryptographically secure randomness.
    $check_order_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ?");
    $order_number = '';
    $is_unique_order_number = false;
    for ($i = 0; $i < 5; $i++) {
        $order_number = 'MO' . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));
        $check_order_stmt->execute([$order_number]);
        if ((int)$check_order_stmt->fetchColumn() === 0) {
            $is_unique_order_number = true;
            break;
        }
    }

    if (!$is_unique_order_number) {
        throw new Exception('Unable to generate order number. Please try again.');
    }

    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, order_number, total_amount, delivery_address, delivery_phone, special_instructions, promo_code_id, discount_amount) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $order_number, $total_amount, $full_delivery_address, $delivery_phone, $special_instructions, $promo_code_id, $discount_amount]);
    $order_id = $pdo->lastInsertId();

    // Log promo usage if code was applied
    if ($promo_code_id) {
        $usage_insert = $pdo->prepare("INSERT INTO promo_usage (promo_id, user_id, order_id) VALUES (?, ?, ?)");
        $usage_insert->execute([$promo_code_id, $user_id, $order_id]);

        $usage_count_update = $pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?");
        $usage_count_update->execute([$promo_code_id]);
    }

    // Reserve stock and create order items
    $insert_item_stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
        VALUES (?, ?, ?, ?, ?)
    ");
    $update_stock_stmt = $pdo->prepare("
        UPDATE products 
        SET stock_quantity = stock_quantity - ? 
        WHERE id = ? AND stock_quantity >= ?
    ");
    $select_stock_stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? FOR UPDATE");

    foreach ($cart_items as $item) {
        $product_id = $item['product_id'];
        $quantity = (int)$item['quantity'];
        $unit_price = (float)$item['price'];
        $total_price = $unit_price * $quantity;

        // Lock and check stock
        $select_stock_stmt->execute([$product_id]);
        $current_stock = $select_stock_stmt->fetchColumn();

        if ($current_stock === false) {
            throw new Exception("Product not found while reserving stock (ID: {$product_id})");
        }

        if ($current_stock < $quantity) {
            throw new Exception("Insufficient stock for product '{$item['name']}'. Available: {$current_stock}, requested: {$quantity}");
        }

        // Decrement stock atomically
        $update_stock_stmt->execute([$quantity, $product_id, $quantity]);
        if ($update_stock_stmt->rowCount() === 0) {
            throw new Exception("Failed to reserve stock for product '{$item['name']}' (ID: {$product_id})");
        }

        // Insert into order_items
        $insert_item_stmt->execute([
            $order_id,
            $product_id,
            $quantity,
            $unit_price,
            $total_price
        ]);
    }

    // Clear user's cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Get user details for email
    $stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    $pdo->commit();

    // Send order confirmation email
    try {
        $mail = new PHPMailer(true);

        configure_mailer_transport($mail);
        $mail->setFrom(default_mail_from_address(), SITE_NAME);
        $mail->addAddress($user['email'], $user['full_name']);

        // ALSO send to admin
        $mail->addAddress('mamasovenug@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = "Order Confirmation - " . $order_number;
        
        // Create order items list for email
        $items_html = '';
        foreach ($cart_items as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $items_html .= "<tr>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['name']}</td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: center;'>{$item['quantity']}</td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>UGX " . number_format($item['price']) . "</td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>UGX " . number_format($item_total) . "</td>
            </tr>";
        }

        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    " . email_logo_html($mail, 80) . "
                </div>
                <h2 style='color: #8B4513; text-align: center;'>Order Confirmation</h2>
                
                <p>Dear {$user['full_name']},</p>
                
                <p>Thank you for your order! We're excited to prepare your delicious treats.</p>
                
                <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #8B4513;'>Order Details</h3>
                    <p><strong>Order Number:</strong> {$order_number}</p>
                    <p><strong>Order Date:</strong> " . date('d M Y, H:i') . "</p>
                    <p><strong>Delivery Address:</strong> " . nl2br(htmlspecialchars($delivery_address)) . "</p>
                    <p><strong>Contact Phone:</strong> {$delivery_phone}</p>
                    " . (!empty($special_instructions) ? "<p><strong>Special Instructions:</strong> " . htmlspecialchars($special_instructions) . "</p>" : "") . "
                </div>

                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <thead>
                        <tr style='background: #8B4513; color: white;'>
                            <th style='padding: 10px; text-align: left;'>Item</th>
                            <th style='padding: 10px; text-align: center;'>Qty</th>
                            <th style='padding: 10px; text-align: right;'>Price</th>
                            <th style='padding: 10px; text-align: right;'>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$items_html}
                        <tr style='border-top: 2px solid #8B4513;'>
                            <td colspan='3' style='padding: 10px; text-align: right; font-weight: bold;'>Subtotal:</td>
                            <td style='padding: 10px; text-align: right; font-weight: bold;'>UGX " . number_format($subtotal) . "</td>
                        </tr>
                        <tr>
                            <td colspan='3' style='padding: 5px; text-align: right;'>Delivery Fee:</td>
                            <td style='padding: 5px; text-align: right;'>UGX " . number_format($delivery_fee) . "</td>
                        </tr>
                        " . ($discount_amount > 0 ? "
                        <tr style='background: #e8f5e8;'>
                            <td colspan='3' style='padding: 5px; text-align: right; color: green;'><strong>Discount:</strong></td>
                            <td style='padding: 5px; text-align: right; color: green;'><strong>-UGX " . number_format($discount_amount) . "</strong></td>
                        </tr>
                        " : "") . "
                        <tr style='background: #f0f0f0;'>
                            <td colspan='3' style='padding: 10px; text-align: right; font-weight: bold; font-size: 16px;'>Total Amount:</td>
                            <td style='padding: 10px; text-align: right; font-weight: bold; font-size: 16px; color: #8B4513;'>UGX " . number_format($total_amount) . "</td>
                        </tr>
                    </tbody>
                </table>

                <div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h4 style='margin-top: 0; color: #2d5a2d;'>What happens next?</h4>
                    <ul style='margin: 0; padding-left: 20px;'>
                        <li>We'll start preparing your order immediately</li>
                        <li>You'll receive updates on your order status</li>
                        <li>Our delivery team will contact you before delivery</li>
                        <li>Payment is Cash on Delivery</li>
                    </ul>
                </div>

                <p>You can track your order status by visiting your <a href='" . BASE_URL . "/orders.php' style='color: #8B4513;'>order history</a>.</p>
                
                <p>If you have any questions, feel free to <a href='" . BASE_URL . "/contact.php' style='color: #8B4513;'>contact us</a>.</p>
                
                <p style='margin-top: 30px;'>
                    Thank you for choosing Mama's Oven!<br>
                    <strong>The Mama's Oven Team</strong>
                </p>
            </div>
        </body>
        </html>";

        send_mail_with_fallback($mail);

        // --- Send personalized admin notification ---
        $adminMail = new PHPMailer(true);
        configure_mailer_transport($adminMail);
        $adminMail->setFrom(default_mail_from_address(), SITE_NAME . ' Orders');
        $adminMail->addAddress('mamasovenug@gmail.com');

        $adminMail->isHTML(true);
        $adminMail->Subject = "New Order Placed - {$order_number}";

        $adminMail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    " . email_logo_html($adminMail, 80) . "
                </div>
                <h2 style='color: #8B4513;'>New Order Notification</h2>
                <p>A new order has been placed by <strong>{$user['full_name']}</strong> (<a href='mailto:{$user['email']}'>{$user['email']}</a>).</p>
                <p><strong>Order Number:</strong> {$order_number}</p>
                <p><strong>Total Amount:</strong> UGX " . number_format($total_amount) . "</p>
                <p><strong>Delivery Address:</strong> " . nl2br(htmlspecialchars($delivery_address)) . "</p>
                <p><strong>Contact Phone:</strong> {$delivery_phone}</p>
                " . (!empty($special_instructions) ? "<p><strong>Special Instructions:</strong> " . htmlspecialchars($special_instructions) . "</p>" : "") . "
                <hr>
                <h4>Ordered Items:</h4>
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <thead>
                        <tr style='background: #8B4513; color: white;'>
                            <th style='padding: 10px; text-align: left;'>Item</th>
                            <th style='padding: 10px; text-align: center;'>Qty</th>
                            <th style='padding: 10px; text-align: right;'>Price</th>
                            <th style='padding: 10px; text-align: right;'>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$items_html}
                        <tr style='border-top: 2px solid #8B4513;'>
                            <td colspan='3' style='padding: 10px; text-align: right; font-weight: bold;'>Subtotal:</td>
                            <td style='padding: 10px; text-align: right; font-weight: bold;'>UGX " . number_format($subtotal) . "</td>
                        </tr>
                        <tr>
                            <td colspan='3' style='padding: 5px; text-align: right;'>Delivery Fee:</td>
                            <td style='padding: 5px; text-align: right;'>UGX " . number_format($delivery_fee) . "</td>
                        </tr>
                        " . ($discount_amount > 0 ? "
                        <tr style='background: #e8f5e8;'>
                            <td colspan='3' style='padding: 5px; text-align: right; color: green;'><strong>Discount:</strong></td>
                            <td style='padding: 5px; text-align: right; color: green;'><strong>-UGX " . number_format($discount_amount) . "</strong></td>
                        </tr>
                        " : "") . "
                        <tr style='background: #f0f0f0;'>
                            <td colspan='3' style='padding: 10px; text-align: right; font-weight: bold; font-size: 16px;'>Total Amount:</td>
                            <td style='padding: 10px; text-align: right; font-weight: bold; font-size: 16px; color: #8B4513;'>UGX " . number_format($total_amount) . "</td>
                        </tr>
                    </tbody>
                </table>
                <p>This order is awaiting your confirmation in the admin dashboard.</p>
            </div>
        </body>
        </html>";

        send_mail_with_fallback($adminMail);
    } catch (Exception $e) {
        // Log email error but don't fail the order
        error_log('Order confirmation email failed: ' . $e->getMessage());
    }
    header('Location: ../print_receipt.php?id=' . $order_id);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
