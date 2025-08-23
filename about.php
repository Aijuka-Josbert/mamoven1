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
                Mama's Oven began as a small home kitchen where family recipes were handed down and perfected.
                Over the years we've grown into a community bakery, but our approach remains the same — every loaf,
                cake and pastry is crafted with attention to detail, quality ingredients, and heartfelt care.
            </p>

            <h4 class="mt-4">Our Mission</h4>
            <p class="text-muted">
                To bring comfort and joy through freshly baked goods made from locally sourced ingredients,
                and to make every celebration memorable with thoughtful flavours and beautiful presentation.
            </p>

            <h4 class="mt-3">Our Values</h4>
            <ul class="text-muted">
                <li><strong>Quality:</strong> We use premium ingredients and traditional techniques.</li>
                <li><strong>Community:</strong> Supporting local suppliers and giving back where we can.</li>
                <li><strong>Craftsmanship:</strong> Attention to detail in every product we make.</li>
                <li><strong>Sustainability:</strong> Minimizing waste and choosing responsible packaging.</li>
            </ul>

            <a href="contact.php" class="btn btn-primary mt-3">Get in Touch</a>
        </div>
    </div>

    <!-- Team Section -->
    <div class="row mt-5">
        <div class="col-12 text-center mb-4">
            <h3 class="section-title">Meet Our Bakers</h3>
            <p class="text-muted">A small team of passionate bakers and pastry artists.</p>
        </div>

        <div class="col-md-4 text-center">
            <img src="assets/image2/hf.jpeg" alt="Head Baker" class="img-fluid rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
            <h5 class="mb-0">Josie Taijuka</h5>
            <small class="text-muted d-block mb-2">Head Baker & Founder</small>
            <p class="text-muted">Recipe guardian and flavor innovator.</p>
        </div>

        <div class="col-md-4 text-center">
            <img src="assets/image2/pipo.jpeg" alt="Pastry Chef" class="img-fluid rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
            <h5 class="mb-0">Esther</h5>
            <small class="text-muted d-block mb-2">Pastry Chef</small>
            <p class="text-muted">Creates beautiful cakes and pastries for your celebrations.</p>
        </div>

        <div class="col-md-4 text-center">
            <img src="assets/image2/pp.jpeg" alt="Delivery & Logistics" class="img-fluid rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
            <h5 class="mb-0">Samuel</h5>
            <small class="text-muted d-block mb-2">Delivery & Logistics</small>
            <p class="text-muted">Ensures fresh goods get to you on time.</p>
        </div>
    </div>

    <!-- Sustainability / Local Sourcing -->
    <div class="row mt-5">
        <div class="col-md-6">
            <h4>Local Ingredients</h4>
            <p class="text-muted">We partner with local farms and suppliers to source fresh produce and grains — supporting the community while ensuring superior flavor.</p>
        </div>
        <div class="col-md-6">
            <h4>Sustainable Practices</h4>
            <p class="text-muted">From recycled packaging choices to minimizing food waste, we strive to operate responsibly for our customers and the environment.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>