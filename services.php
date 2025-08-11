<?php
$page_title = 'Our Services';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="section-title">What We Offer</h1>
        <p class="lead text-muted">Crafting memorable experiences, one bake at a time.</p>
    </div>

    <!-- Service 1: Custom Cakes -->
    <div class="row align-items-center g-5 mb-5 pb-5 border-bottom">
        <div class="col-lg-6">
            <img src="<?php echo asset_url('assets/images/service-cakes.jpg'); ?>" alt="A beautiful custom celebration cake" class="img-fluid rounded shadow-sm">
        </div>
        <div class="col-lg-6">
            <h2 class="display-6">Custom Cakes</h2>
            <p class="text-muted mt-3">
                For birthdays, weddings, anniversaries, or any special occasion, our custom cakes are the perfect centerpiece. We work with you to bring your vision to life with delicious flavors and beautiful designs.
            </p>
            <ul class="list-unstyled mt-3">
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Personalized design consultations.</li>
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Wide range of flavors and fillings.</li>
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Options for various dietary needs.</li>
            </ul>
            <a href="<?php echo asset_url('contact.php'); ?>" class="btn btn-primary mt-3">Request a Quote</a>
        </div>
    </div>

    <!-- Service 2: Event Catering -->
    <div class="row align-items-center g-5 mb-5 pb-5 border-bottom flex-lg-row-reverse">
        <div class="col-lg-6">
            <img src="<?php echo asset_url('assets/images/service-catering.jpg'); ?>" alt="A dessert table at a catered event" class="img-fluid rounded shadow-sm">
        </div>
        <div class="col-lg-6">
            <h2 class="display-6">Event Catering</h2>
            <p class="text-muted mt-3">
                Elevate your corporate events, parties, and gatherings with our professional catering service. We offer a delightful selection of pastries, snacks, and desserts that will impress your guests.
            </p>
            <ul class="list-unstyled mt-3">
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Customizable menus for any event size.</li>
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Beautifully arranged dessert tables.</li>
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Professional and timely service.</li>
            </ul>
            <a href="<?php echo asset_url('contact.php'); ?>" class="btn btn-primary mt-3">Plan Your Event</a>
        </div>
    </div>

    <!-- Service 3: Home Delivery -->
    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
            <img src="<?php echo asset_url('assets/images/service-delivery.jpg'); ?>" alt="A delivery box from Mama's Oven" class="img-fluid rounded shadow-sm">
        </div>
        <div class="col-lg-6">
            <h2 class="display-6">Freshly Delivered</h2>
            <p class="text-muted mt-3">
                Craving our treats but can't make it to the store? No problem. We offer fast and reliable delivery service across Kampala, ensuring our baked goods arrive at your doorstep fresh and delicious.
            </p>
             <ul class="list-unstyled mt-3">
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Easy online ordering process.</li>
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Carefully packaged to ensure freshness.</li>
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Reliable delivery to your home or office.</li>
            </ul>
            <a href="<?php echo asset_url('products.php'); ?>" class="btn btn-primary mt-3">Order Now</a>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>