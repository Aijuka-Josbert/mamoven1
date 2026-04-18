<?php
// Start session and include database configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';

// --- Security Check ---
// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access Denied. Please log in.");
}
// 2. Validate order ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Order ID.");
}
$order_id = (int)$_GET['id'];

// --- Data Fetching ---
try {
    // Fetch the main order details
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    // 3. Verify order exists and belongs to the user OR user is an admin
    if (!$order || ($order['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin')) {
        die("Order not found or you do not have permission to view this receipt.");
    }
    
    // Fetch customer details
    $user_stmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
    $user_stmt->execute([$order['user_id']]);
    $user = $user_stmt->fetch();

    // Fetch business settings
    $settings_stmt = $pdo->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('business_address', 'business_phone', 'business_email')");
    $settings_stmt->execute();
    $settings_raw = $settings_stmt->fetchAll();
    $settings = [];
    foreach ($settings_raw as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }

    // Fetch all items associated with this order
    $items_stmt = $pdo->prepare("
        SELECT oi.quantity, oi.unit_price, oi.total_price, p.name as product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $items_stmt->execute([$order_id]);
    $order_items = $items_stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: Could not retrieve order details.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt for Order #<?php echo htmlspecialchars($order['order_number']); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #A7D7C5;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .receipt-container {
            width: 80mm; /* Standard thermal printer width */
            max-width: 100%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .receipt-header img {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .receipt-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .receipt-details, .customer-details {
            margin-bottom: 20px;
        }
        .receipt-details p, .customer-details p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 4px;
            text-align: left;
        }
        thead {
            border-bottom: 1px solid #333;
        }
        tbody tr td {
            border-bottom: 1px dotted #ccc;
        }
        .totals {
            margin-top: 20px;
            border-top: 1px solid #333;
            padding-top: 10px;
        }
        .totals .row {
            display: flex;
            justify-content: space-between;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        /* Print-specific styles */
        @media print {
            @page {
                margin: 0;
                size: auto;
            }
            body {
                background-color: #fff;
            }
            .print-button {
                display: none;
            }
            .receipt-container {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <button class="print-button" onclick="window.print();">Print Receipt</button>

    <div class="receipt-container">
        <div class="receipt-header">
            <img src="<?php echo htmlspecialchars(BASE_URL); ?>/assets/images/logo.jpeg" alt="Business Logo" style="max-width: 150px; height: auto;">
            <p><strong>Receipt</strong></p>
            <p><?php echo htmlspecialchars($settings['business_address'] ?? 'Kampala, Uganda'); ?></p>
            <p>+256 747 686189</p>
            <p>mamasovenug@gmail.com</p>
        </div>

        <div class="receipt-details">
            <p><strong>Order #:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
            <p><strong>Date:</strong> <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
            
        </div>

        <div class="customer-details">
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? $order['delivery_phone']); ?></p>
            <p><strong>Delivery To:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($order['delivery_phone']); ?></p>
            <?php if (!empty($order['special_instructions'])): ?>
                <p><strong>Special Instructions:</strong> <?php echo htmlspecialchars($order['special_instructions']); ?></p>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subtotal = 0;
                foreach ($order_items as $item):
                    $subtotal += $item['total_price'];
                ?>
                <tr>
                    <td>
                        <?php echo htmlspecialchars($item['product_name']); ?><br>
                        <small>(@ <?php echo number_format($item['unit_price']); ?>)</small>
                    </td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($item['total_price']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <div class="row">
                <span>Subtotal</span>
                <span><?php echo number_format($subtotal); ?></span>
            </div>
            <div class="row">
                <span>Delivery Fee</span>
                <span><?php echo number_format($order['total_amount'] - $subtotal); ?></span>
            </div>
            <div class="row" style="font-weight: bold; font-size: 18px; margin-top: 10px;">
                <span>Grand Total</span>
                <span>UGX <?php echo number_format($order['total_amount']); ?></span>
            </div>
        </div>

        <div class="receipt-footer">
            <p>Thank you for your purchase!</p>
            <p>Payment Method: Cash on Delivery</p>
        </div>
    </div>

    <script>
        // Automatically trigger the print dialog when the page loads
        window.onload = function() {
            window.print();
        };
    </script>

</body>
</html>