    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-12">
                    <h5 class="footer-title"><?php echo SITE_NAME; ?></h5>
                    <p>Freshly baked delights delivered to your doorstep. Experience the taste of homemade goodness with our premium cakes, snacks, and pastries.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link" title="Twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4">
                    <h5 class="footer-title">Company</h5>
                    <ul class="footer-links">
                        <li><a href="<?php echo asset_url('about.php'); ?>">About Us</a></li>
                        <li><a href="<?php echo asset_url('products.php'); ?>">Our Products</a></li>
                        <li><a href="<?php echo asset_url('contact.php'); ?>">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-4">
                    <h5 class="footer-title">Help</h5>
                    <ul class="footer-links">
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Shipping & Returns</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-4">
                    <h5 class="footer-title">Contact Us</h5>
                    <address class="mb-0" style="font-style: normal;">
                        Kampala, Uganda<br>
                        <strong>Email:</strong> <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?></a><br>
                        <strong>Phone:</strong> <a href="tel:+256700123456">+256 700 123456</a>
                    </address>
                </div>
            </div>
            
            <hr class="footer-divider my-4">
            
            <div class="text-center">
                <p class="copyright mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Application JS -->
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>