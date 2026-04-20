    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-12">
                    <h5 class="footer-title"><?php echo SITE_NAME; ?></h5>
                    <p>Freshly baked delights delivered to your doorstep. Experience the taste of homemade goodness with our premium cakes, snacks, and pastries.</p>
                      <div class="social-links mt-3">
                        <a href="https://www.instagram.com/mamas__oven?igsh=MTdhYzdoemJvZW03bA%3D%3D&utm_source=qr" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://api.whatsapp.com/send?phone=256747686189" target="_blank" class="social-link" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4">
                    <h5 class="footer-title">Company</h5>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>/about.php">About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/products.php">Our Products</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-4">
                    <h5 class="footer-title">Help</h5>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>/footer_pages/faq.php">FAQ</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/footer_pages/shipping_returns.php">Bakery Care & Delivery</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/footer_pages/privacy_policy.php">Privacy Policy</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/termsandconditions.php">Terms & Conditions</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/footer_pages/terms_of_service.php">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-4">
                    <h5 class="footer-title">Contact Us</h5>
                    <address class="mb-0" style="font-style: normal;">
                        Kampala, Uganda<br>
                        <strong>Email:</strong> <a href="mailto:mamasovenug@gmail.com">mamasovenug@gmail.com</a><br>
                        <strong>Phone:</strong> <a href="tel:+256747686189">+256 747 686189</a>
                    </address>
                </div>
            </div>
            
            <hr class="footer-divider my-4">
            
            <div class="text-center">
                <p class="copyright mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Chat Button -->
    <a href="https://api.whatsapp.com/send?phone=256747686189&text=Hi%20Mama%27s%20Oven%2C%20I%20have%20a%20question" 
       class="whatsapp-btn" target="_blank" title="Chat with us on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Application JS -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>