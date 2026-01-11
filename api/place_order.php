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

$user_id = $_SESSION['user_id'];
$delivery_address = trim($_POST['delivery_address'] ?? '');
$delivery_phone = trim($_POST['delivery_phone'] ?? '');
$special_instructions = trim($_POST['special_instructions'] ?? '');

if (!$delivery_address || !$delivery_phone) {
    echo json_encode(['success' => false, 'message' => 'Delivery address and phone are required']);
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

    // Get delivery fee
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'delivery_fee'");
    $stmt->execute();
    $delivery_fee = (float)($stmt->fetchColumn() ?: 5000);

    $total_amount = $subtotal + $delivery_fee;

    // Generate order number
    $order_number = 'MO' . date('Ymd') . sprintf('%04d', rand(1, 9999));

    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, order_number, total_amount, delivery_address, delivery_phone, special_instructions) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $order_number, $total_amount, $delivery_address, $delivery_phone, $special_instructions]);
    $order_id = $pdo->lastInsertId();

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
        
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_USER, SITE_NAME);
        $mail->addAddress($user['email'], $user['full_name']);

        // ALSO send to admin
        $mail->addAddress(ADMIN_EMAIL);

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

        $mail->send();

        // --- Send personalized admin notification ---
        $adminMail = new PHPMailer(true);
        $adminMail->isSMTP();
        $adminMail->Host = SMTP_HOST;
        $adminMail->SMTPAuth = true;
        $adminMail->Username = SMTP_USER;
        $adminMail->Password = SMTP_PASS;
        $adminMail->SMTPSecure = SMTP_SECURE;
        $adminMail->Port = SMTP_PORT;

        $adminMail->setFrom(SMTP_USER, SITE_NAME . ' Orders');
        $adminMail->addAddress(ADMIN_EMAIL);

        $adminMail->isHTML(true);
        $adminMail->Subject = "New Order Placed - {$order_number}";

        $adminMail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
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

        $adminMail->send();
    } catch (Exception $e) {
        // Log email error but don't fail the order
        error_log('Order confirmation email failed: ' . $e->getMessage());
    }
    header('Location: ../print_receipt.php?id=' . $order_id);
    echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'order_number' => $order_number, 'order_id' => $order_id]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
