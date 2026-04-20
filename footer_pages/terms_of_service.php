<?php
$page_title = 'Terms of Service';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container my-5">
    <h1 class="mb-4 section-title">Terms of Service</h1>
    <p class="text-muted mb-4">Effective date: <?php echo date('F j, Y'); ?></p>

    <h4>Order & Payment</h4>
    <p class="text-muted">
        Orders are confirmed once they are successfully placed in the system. At this time, payment is Cash on Delivery (COD).
        Mobile Money, card, and additional payment channels are planned and will be marked as "Coming Soon" until activated.
    </p>

    <h4>Delivery & Fulfillment</h4>
    <p class="text-muted">
        Delivery fees depend on your selected delivery area. Please provide an accurate address and reachable phone number so our team
        can complete delivery smoothly. Estimated timing may vary due to traffic, weather, and order volume.
    </p>

    <h4>Changes & Cancellations</h4>
    <p class="text-muted">
        Changes or cancellations should be requested as early as possible. Once baking or dispatch has started, some changes may no longer
        be possible. For custom and event orders, specific timelines may apply.
    </p>

    <h4>Food Quality & Handling</h4>
    <p class="text-muted">
        Our products are freshly prepared. For best quality, follow storage and handling instructions provided with your order,
        especially for cream-based and perishable items.
    </p>

    <h4>User Accounts</h4>
    <p class="text-muted">
        You are responsible for keeping your login credentials secure. Mama's Oven may suspend misuse, fraudulent activity,
        or abusive behavior to protect customers and service integrity.
    </p>

    <h4>Liability</h4>
    <p class="text-muted">
        We are not liable for delays or interruptions caused by factors outside our control, including network, weather, or transport disruptions.
        Please review your order details carefully before confirming.
    </p>

    <h4>Contact</h4>
    <p class="text-muted mb-0">
        For policy questions, contact us at <a href="mailto:mamasovenug@gmail.com">mamasovenug@gmail.com</a>
        or through our <a href="<?php echo BASE_URL; ?>/contact.php">contact page</a>.
    </p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>