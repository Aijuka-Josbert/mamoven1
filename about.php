<?php
session_start();
include_once 'config/database.php';

$page_title = 'About Us';
include_once 'includes/header.php';
?>

<div class="container my-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="section-title">About Mama's Oven Uganda</h1>
            <p class="lead">Bringing you the finest homemade bakery products since day one</p>
        </div>
    </div>

    <!-- Story Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6">
            <img src="assets/images/about-story.jpg" alt="Our Story" class="img-fluid rounded shadow">
        </div>
        <div class="col-lg-6">
            <h2 class="mb-4">Our Story</h2>
            <p class="lead">
                Mama's Oven Uganda was born from a passion for creating delicious, homemade bakery products that bring joy to families across Uganda.
            </p>
            <p>
                What started as a small kitchen operation has grown into a beloved bakery that serves fresh, quality products to customers throughout Kampala and beyond. We believe that every celebration, big or small, deserves something special.
            </p>
            <p>
                Our commitment to using only the finest ingredients and traditional baking methods ensures that every product we create meets the highest standards of taste and quality.
            </p>
        </div>
    </div>

    <!-- Mission & Vision -->
    <div class="row mb-5">
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow">
                <div class="card-body text-center p-4">
                    <i class="fas fa-bullseye fa-3x text-primary mb-3"></i>
                    <h3 class="card-title">Our Mission</h3>
                    <p class="card-text">
                        To provide fresh, delicious, and affordable bakery products while delivering exceptional customer service that exceeds expectations.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow">
                <div class="card-body text-center p-4">
                    <i class="fas fa-eye fa-3x text-primary mb-3"></i>
                    <h3 class="card-title">Our Vision</h3>
                    <p class="card-text">
                        To be Uganda's most trusted bakery, known for quality, innovation, and bringing sweetness to every celebration.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Values Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">Our Values</h2>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="text-center">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-heart fa-2x text-white"></i>
                </div>
                <h5>Quality</h5>
                <p class="text-muted">We use only the finest ingredients and traditional methods</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="text-center">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-clock fa-2x text-white"></i>
                </div>
                <h5>Freshness</h5>
                <p class="text-muted">All products are baked fresh daily for optimal taste</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="text-center">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-users fa-2x text-white"></i>
                </div>
                <h5>Community</h5>
                <p class="text-muted">We're committed to serving our local community</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="text-center">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-smile fa-2x text-white"></i>
                </div>
                <h5>Service</h5>
                <p class="text-muted">Customer satisfaction is our top priority</p>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">Meet Our Team</h2>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow text-center">
                <div class="card-body p-4">
                    <img src="assets/images/team-chef.jpg" alt="Head Chef" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                    <h5 class="card-title">Sarah Nakato</h5>
                    <p class="text-primary">Head Chef & Founder</p>
                    <p class="card-text">With over 15 years of baking experience, Sarah leads our team in creating exceptional products.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow text-center">
                <div class="card-body p-4">
                    <img src="assets/images/team-manager.jpg" alt="Operations Manager" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                    <h5 class="card-title">John Ssekandi</h5>
                    <p class="text-primary">Operations Manager</p>
                    <p class="card-text">John ensures smooth operations and maintains our high standards of quality control.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow text-center">
                <div class="card-body p-4">
                    <img src="assets/images/team-decorator.jpg" alt="Cake Decorator" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                    <h5 class="card-title">Grace Namuli</h5>
                    <p class="text-primary">Lead Cake Decorator</p>
                    <p class="card-text">Grace brings artistic flair to our custom cakes and special occasion treats.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="row">
        <div class="col-12">
            <div class="bg-primary text-white rounded p-5 text-center">
                <h3 class="mb-3">Ready to Taste the Difference?</h3>
                <p class="lead mb-4">Experience the quality and care that goes into every product we make</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="products.php" class="btn btn-light btn-lg">Browse Our Products</a>
                    <a href="contact.php" class="btn btn-outline-light btn-lg">Get in Touch</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
