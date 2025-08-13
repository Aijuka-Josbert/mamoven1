<?php
$page_title = 'About Us';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="section-title">Our Story</h1>
        <p class="lead text-muted">Baking with passion, serving with love.</p>
    </div>

    <div class="row align-items-center g-5">
        <div class="col-lg-6">
            <img src="assets/image2/about-us.jpeg" alt="The team at Mama's Oven" class="img-fluid rounded shadow">
        </div>
        <div class="col-lg-6">
            <h2 class="display-6">From Our Kitchen to Your Heart</h2>
            <p class="text-muted mt-3">
                Mama's Oven was born from a simple idea: to share the joy of traditional, homemade baking with our community. What started as a small home kitchen, filled with the aroma of fresh bread and cakes, has blossomed into a beloved local bakery.
            </p>
            <p class="text-muted">
                Our secret isn't just in our recipes—it's in the love and care we pour into every single item we create. We believe in the power of good food to bring people together, to celebrate life's moments, and to create lasting memories.
            </p>
            <a href="contact.php" class="btn btn-primary mt-3">Get in Touch</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>