<?php
session_start();
include_once 'config/database.php';
include_once 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h1 class="hero-title">Welcome to Mama's Oven Uganda</h1>
                        <p class="hero-description">Freshly baked delights delivered to your doorstep. Experience the taste of homemade goodness with our premium cakes, snacks, and pastries.</p>
                        <div class="hero-buttons">
                            <a href="./products.php" class="btn btn-primary btn-lg">View Our Products</a>
                            <a href="./about.php" class="btn btn-outline-light btn-lg">Learn More</a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <img src="./assets/images/Untitled.jpeg" alt="Delicious Cakes" class="hero-image">
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-overlay"></div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Featured Products</h2>
            <div class="row" id="featured-products">
                <!-- Products will be loaded here via AJAX -->
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">Our Services</h2>
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="service-card">
                        <i class="fas fa-birthday-cake service-icon"></i>
                        <h4>Custom Cakes</h4>
                        <p>Personalized cakes for your special occasions</p>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="service-card">
                        <i class="fas fa-truck service-icon"></i>
                        <h4>Home Delivery</h4>
                        <p>Fresh products delivered right to your door</p>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="service-card">
                        <i class="fas fa-clock service-icon"></i>
                        <h4>Quick Orders</h4>
                        <p>Fast and easy online ordering system</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
$(document).ready(function() {
    loadFeaturedProducts();
});

function loadFeaturedProducts() {
    $.ajax({
        url: 'api/get_featured_products.php',
        method: 'GET',
        dataType: 'json',
        success: function(products) {
            let html = '';
            products.slice(0, 6).forEach(function(product) {
                html += `
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="product-card">
                            <img src="${product.image}" alt="${product.name}" class="product-image">
                            <div class="product-info">
                                <h5 class="product-name">${product.name}</h5>
                                <p class="product-price">UGX ${product.price.toLocaleString()}</p>
                                <a href="product-details.php?id=${product.id}" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#featured-products').html(html);
        }
    });
}
</script>

<?php include_once 'includes/footer.php'; ?>
