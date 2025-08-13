<?php
$page_title = 'Contact Us';
require_once __DIR__ . '/includes/header.php';

$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please fill in all fields correctly.';
    } else {
        try {
            // Save message to the database
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $message]);
            $success_message = 'Thank you, ' . htmlspecialchars($name) . '! Your message has been sent. We will get back to you soon.';
        } catch (PDOException $e) {
            $error_message = 'Sorry, there was an error sending your message. Please try again later.';
            // In a real app, you would log the error: error_log($e->getMessage());
        }
    }
}
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="section-title">Get In Touch</h1>
        <p class="lead text-muted">We'd love to hear from you! For questions, custom orders, or just to say hello.</p>
    </div>

    <div class="row g-5">
        <!-- Contact Form Column -->
        <div class="col-lg-7">
            <div class="card shadow border-0 h-100">
                <div class="card-body p-4 p-md-5">
                    <h4 class="mb-4">Send us a Message</h4>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!$success_message): // Hide form on success ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Your Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Contact Information Column -->
        <div class="col-lg-5">
             <div class="card shadow border-0 h-100">
                <div class="card-body p-4 p-md-5">
                    <h4 class="mb-4">Contact Information</h4>
                    <p class="mb-3"><i class="fas fa-map-marker-alt fa-fw me-2 text-primary"></i>Plot 123, Main Street, Kampala, Uganda</p>
                    <p class="mb-3"><i class="fas fa-phone fa-fw me-2 text-primary"></i><a href="tel:+256700123456">+256 700 123456</a></p>
                    <p class="mb-3"><i class="fas fa-envelope fa-fw me-2 text-primary"></i><a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?></a></p>
                    <hr>
                    <h5 class="h6 mt-4">Business Hours</h5>
                    <p class="text-muted mb-1">Monday - Saturday: 8:00 AM - 6:00 PM</p>
                    <p class="text-muted">Sunday & Public Holidays: Closed</p>
                </div>
             </div>
        </div>
    </div>

    <!-- Google Map Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow border-0">
                 <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h4 class="text-center">Find Us Here</h4>
                </div>
                <div class="card-body p-2">
                    <div class="map-responsive">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d127673.72898703057!2d32.51733695273995!3d0.3134372551461963!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x177dbc0f9836354b%3A0x4538903dd96b6f2b!2sKampala%2C%20Uganda!5e0!3m2!1sen!2sus!4v1699999999999!5m2!1sen!2sus" 
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 10px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>