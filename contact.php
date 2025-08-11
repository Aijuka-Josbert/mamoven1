<?php
session_start();
include_once 'config/database.php';

$page_title = 'Contact Us';
include_once 'includes/header.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    $errors = [];
    
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $success = 'Thank you for your message! We will get back to you soon.';
        } catch (Exception $e) {
            $errors[] = 'Failed to send message. Please try again.';
        }
    }
}
?>

<div class="container my-5">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="section-title">Contact Us</h1>
            <p class="lead">We'd love to hear from you! Get in touch with any questions or feedback.</p>
        </div>
    </div>

    <div class="row">
        <!-- Contact Information -->
        <div class="col-lg-4 mb-5">
            <div class="card border-0 shadow h-100">
                <div class="card-body p-4">
                    <h3 class="card-title mb-4">Get in Touch</h3>
                    
                    <div class="contact-item mb-4">
                        <div class="d-flex align-items-center">
                            <div class="contact-icon me-3">
                                <i class="fas fa-map-marker-alt fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Our Location</h6>
                                <p class="text-muted mb-0">Kampala, Uganda<br>Plot 123, Main Street</p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-item mb-4">
                        <div class="d-flex align-items-center">
                            <div class="contact-icon me-3">
                                <i class="fas fa-phone fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Call Us</h6>
                                <p class="text-muted mb-0">
                                    <a href="tel:+256700123456" class="text-decoration-none">+256 700 123456</a><br>
                                    <a href="tel:+256750987654" class="text-decoration-none">+256 750 987654</a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-item mb-4">
                        <div class="d-flex align-items-center">
                            <div class="contact-icon me-3">
                                <i class="fas fa-envelope fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Email Us</h6>
                                <p class="text-muted mb-0">
                                    <a href="mailto:info@mamasovenug.com" class="text-decoration-none">info@mamasovenug.com</a><br>
                                    <a href="mailto:orders@mamasovenug.com" class="text-decoration-none">orders@mamasovenug.com</a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-item mb-4">
                        <div class="d-flex align-items-center">
                            <div class="contact-icon me-3">
                                <i class="fas fa-clock fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Business Hours</h6>
                                <p class="text-muted mb-0">
                                    Monday - Saturday: 8:00 AM - 8:00 PM<br>
                                    Sunday: 10:00 AM - 6:00 PM
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="social-links mt-4">
                        <h6 class="mb-3">Follow Us</h6>
                        <a href="#" class="social-link me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="social-link me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="social-link me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-whatsapp fa-lg"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-body p-4">
                    <h3 class="card-title mb-4">Send us a Message</h3>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <select class="form-select" id="subject" name="subject">
                                        <option value="">Select a subject</option>
                                        <option value="General Inquiry" <?php echo ($_POST['subject'] ?? '') === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                        <option value="Order Question" <?php echo ($_POST['subject'] ?? '') === 'Order Question' ? 'selected' : ''; ?>>Order Question</option>
                                        <option value="Custom Cake Request" <?php echo ($_POST['subject'] ?? '') === 'Custom Cake Request' ? 'selected' : ''; ?>>Custom Cake Request</option>
                                        <option value="Feedback" <?php echo ($_POST['subject'] ?? '') === 'Feedback' ? 'selected' : ''; ?>>Feedback</option>
                                        <option value="Complaint" <?php echo ($_POST['subject'] ?? '') === 'Complaint' ? 'selected' : ''; ?>>Complaint</option>
                                        <option value="Business Partnership" <?php echo ($_POST['subject'] ?? '') === 'Business Partnership' ? 'selected' : ''; ?>>Business Partnership</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" 
                                     placeholder="Please tell us how we can help you..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-body p-0">
                    <div class="map-container" style="height: 400px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
                            <h5>Find Us on the Map</h5>
                            <p class="text-muted">Interactive map coming soon</p>
                            <a href="https://goo.gl/maps" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt"></i> View on Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.contact-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 107, 53, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.contact-item {
    border-left: 3px solid var(--primary-color);
    padding-left: 1rem;
}

.social-link {
    color: var(--primary-color);
    transition: all 0.3s ease;
}

.social-link:hover {
    color: var(--secondary-color);
    transform: translateY(-2px);
}
</style>

<?php include_once 'includes/footer.php'; ?>
