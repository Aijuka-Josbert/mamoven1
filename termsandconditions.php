<?php
$page_title = 'Terms & Conditions';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
	<h1 class="mb-4 section-title">Terms & Conditions</h1>
	<p class="text-muted mb-4">By creating an account or placing an order with Mama's Oven, you agree to the following terms.</p>

	<h4>Account & Access</h4>
	<p class="text-muted">
		Provide accurate registration details and keep your account credentials private. We may restrict access where we detect suspicious,
		abusive, or fraudulent behavior.
	</p>

	<h4>Orders</h4>
	<p class="text-muted">
		Product availability and order acceptance depend on stock, production capacity, and delivery coverage. We reserve the right to decline
		or adjust an order where necessary and will communicate promptly.
	</p>

	<h4>Payments</h4>
	<p class="text-muted">
		Current live payment method is Cash on Delivery (COD). Other digital payment methods are listed as Coming Soon until formally activated.
	</p>

	<h4>Delivery</h4>
	<p class="text-muted">
		Delivery charges are calculated by selected area. Ensure your address and phone details are correct to avoid delays.
		For complete delivery policy details, see
		<a href="<?php echo BASE_URL; ?>/footer_pages/shipping_returns.php">Bakery Care & Delivery</a>.
	</p>

	<h4>Policy Reference</h4>
	<p class="text-muted mb-0">
		For expanded legal and service clauses, review our
		<a href="<?php echo BASE_URL; ?>/footer_pages/terms_of_service.php">Terms of Service</a> and
		<a href="<?php echo BASE_URL; ?>/footer_pages/privacy_policy.php">Privacy Policy</a>.
	</p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>