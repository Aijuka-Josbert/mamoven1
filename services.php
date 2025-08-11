<?php
session_start();
include_once 'config/database.php';

$page_title = 'Our Services';
include_once 'includes/header.php';
?>

<div class="container my-5">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="section-title">Our Services</h1>
            <p class="lead">From custom cakes to daily fresh bakery items, we've got you covered</p>
        </div>
    </div>

    <!-- Main Services -->
    <div class="row mb-5">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-card">
                <div class="service-image-container">
                    <img src="assets/images/service-custom-cakes.jpg" alt="Custom Cakes" class="service-image">
                    <div class="service-overlay">
                        <i class="fas fa-birthday-cake fa-3x"></i>
                    </div>
                </div>
                <div class="service-content">
                    <h4>Custom Cakes</h4>
                    <p>Personalized cakes for birthdays, weddings, anniversaries, and special occasions. Choose from various flavors, designs, and sizes.</p>
                    <ul class="service-features">
                        <li>Multiple flavor options</li>
                        <li>Custom designs and decorations</li>
                        <li>Various sizes available</li>
                        <li>Professional cake decorating</li>
                    </ul>
                    <a href="products.php?category=Custom Orders" class="btn btn-primary">View Custom Cakes</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-card">
                <div class="service-image-container">
                    <img src="assets/images/service-delivery.jpg" alt="Home Delivery" class="service-image">
                    <div class="service-overlay">
                        <i class="fas fa-truck fa-3x"></i>
                    </div>
                </div>
                <div class="service-content">
                    <h4>Home Delivery</h4>
                    <p>Fast and reliable delivery service across Kampala and surrounding areas. Fresh products delivered right to your doorstep.</p>
                    <ul class="service-features">
                        <li>Same-day delivery available</li>
                        <li>Temperature-controlled transport</li>
                        <li>Real-time order tracking</li>
                        <li>Contactless delivery options</li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary">Learn More</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-card">
                <div class="service-image-container">
                    <img src="assets/images/service-catering.jpg" alt="Event Catering" class="service-image">
                    <div class="service-overlay">
                        <i class="fas fa-utensils fa-3x"></i>
                    </div>
                </div>
                <div class="service-content">
                    <h4>Event Catering</h4>
                    <p>Complete catering solutions for corporate events, parties, and gatherings. Professional service with delicious food options.</p>
                    <ul class="service-features">
                        <li>Corporate event catering</li>
                        <li>Party packages</li>
                        <li>Buffet setups</li>
                        <li>Professional serving staff</li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary">Get Quote</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-card">
                <div class="service-image-container">
                    <img src="assets/images/service-wholesale.jpg" alt="Wholesale Orders" class="service-image">
                    <div class="service-overlay">
                        <i class="fas fa-boxes fa-3x"></i>
                    </div>
                </div>
                <div class="service-content">
                    <h4>Wholesale Orders</h4>
                    <p>Bulk orders for restaurants, cafes, and retail outlets. Competitive pricing for large quantity purchases.</p>
                    <ul class="service-features">
                        <li>Competitive wholesale pricing</li>
                        <li>Regular supply contracts</li>
                        <li>Quality consistency</li>
                        <li>Flexible delivery schedules</li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary">Contact Sales</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-card">
                <div class="service-image-container">
                    <img src="assets/images/service-consultation.jpg" alt="Baking Consultation" class="service-image">
                    <div class="service-overlay">
                        <i class="fas fa-comments fa-3x"></i>
                    </div>
                </div>
                <div class="service-content">
                    <h4>Baking Consultation</h4>
                    <p>Professional advice and consultation for your baking needs. Menu planning and recipe development services.</p>
                    <ul class="service-features">
                        <li>Menu planning assistance</li>
                        <li>Recipe development</li>
                        <li>Baking technique guidance</li>
                        <li>Equipment recommendations</li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary">Book Consultation</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-card">
                <div class="service-image-container">
                    <img src="assets/images/service-pickup.jpg" alt="Store Pickup" class="service-image">
                    <div class="service-overlay">
                        <i class="fas fa-store fa-3x"></i>
                    </div>
                </div>
                <div class="service-content">
                    <h4>Store Pickup</h4>
                    <p>Order online and pick up at our convenient location. Skip the lines with our pre-order pickup service.</p>
                    <ul class="service-features">
                        <li>Pre-order online</li>
                        <li>Skip the queue</li>
                        <li>Ready when you arrive</li>
                        <li>Convenient location</li>
                    </ul>
                    <a href="products.php" class="btn btn-primary">Order for Pickup</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">How It Works</h2>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="process-step text-center">
                <div class="process-number">1</div>
                <h5>Browse & Select</h5>
                <p>Choose from our wide range of products or request a custom order</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="process-step text-center">
                <div class="process-number">2</div>
                <h5>Place Order</h5>
                <p>Add items to cart and complete your order with delivery details</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="process-step text-center">
                <div class="process-number">3</div>
                <h5>We Prepare</h5>
                <p>Our skilled bakers prepare your order with care and attention to detail</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="process-step text-center">
                <div class="process-number">4</div>
                <h5>Enjoy</h5>
                <p>Receive your fresh, delicious order and enjoy with family and friends</p>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">Frequently Asked Questions</h2>
        </div>
        <div class="col-lg-6">
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            How far in advance should I place a custom cake order?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            For custom cakes, we recommend placing your order at least 48-72 hours in advance. For complex designs or large orders, please allow 5-7 days.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                            What areas do you deliver to?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We deliver throughout Kampala and surrounding areas including Entebbe, Mukono, and Wakiso. Delivery fees may vary based on location.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                            Do you accommodate dietary restrictions?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes! We can accommodate various dietary needs including sugar-free, gluten-free, and vegan options. Please specify your requirements when placing your order.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="accordion" id="faqAccordion2">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                            What payment methods do you accept?
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion2">
                        <div class="accordion-body">
                            We accept cash on delivery, mobile money (MTN Mobile Money, Airtel Money), and bank transfers. Online card payments coming soon!
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                            Can I cancel or modify my order?
                        </button>
                    </h2>
                    <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion2">
                        <div class="accordion-body">
                            Orders can be cancelled or modified up to 24 hours before the scheduled delivery time. Please contact us as soon as possible for any changes.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSix">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix">
                            Do you provide catering for large events?
                        </button>
                    </h2>
                    <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#faqAccordion2">
                        <div class="accordion-body">
                            Absolutely! We cater for events of all sizes, from small gatherings to large corporate events. Contact us for a custom quote based on your needs.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="row">
        <div class="col-12">
            <div class="bg-primary text-white rounded p-5 text-center">
                <h3 class="mb-3">Ready to Experience Our Services?</h3>
                <p class="lead mb-4">Let us make your next celebration or event truly special</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="products.php" class="btn btn-light btn-lg">Browse Products</a>
                    <a href="contact.php" class="btn btn-outline-light btn-lg">Request Quote</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.service-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.service-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.service-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.service-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 107, 53, 0.9), rgba(247, 147, 30, 0.9));
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    color: white;
}

.service-card:hover .service-overlay {
    opacity: 1;
}

.service-card:hover .service-image {
    transform: scale(1.1);
}

.service-content {
    padding: 1.5rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.service-features {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
    flex-grow: 1;
}

.service-features li {
    padding: 0.25rem 0;
    position: relative;
    padding-left: 1.5rem;
}

.service-features li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: var(--primary-color);
    font-weight: bold;
}

.process-step {
    padding: 2rem 1rem;
}

.process-number {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0 auto 1rem;
}

.accordion-button {
    font-weight: 500;
}

.accordion-button:not(.collapsed) {
    background-color: rgba(255, 107, 53, 0.1);
    border-color: var(--primary-color);
    color: var(--primary-color);
}
</style>

<?php include_once 'includes/footer.php'; ?>
