<?php
$page_title = 'Contact Us';
require_once __DIR__ . '/includes/header.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

$supportEmail = 'mamasovenug@gmail.com';

$success_message = '';
$warning_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Your session security token is missing or expired. Please refresh the page and try again.';
    } elseif (empty($name) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please fill in all fields correctly.';
    } else {
        try {
            // Save message to the database
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $message]);

            // Notify admin, but never fail the user-facing success if notification fails.
            $subject = 'New contact message from ' . $name;
            $body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";
            $email_sent = false;

            try {
                $mail = new PHPMailer(true);
                configure_mailer_transport($mail);

                $mail->setFrom(default_mail_from_address(), SITE_NAME);
                $mail->addAddress($supportEmail);
                $mail->addReplyTo($email, $name);
                $mail->Subject = $subject;
                $mail->isHTML(true);
                $mail->Body = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            " . email_logo_html($mail, 70) . "
                        </div>
                        <h2 style='color: #8B4513;'>New Contact Message</h2>
                        <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                        <p><strong>Email:</strong> <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></p>
                        <div style='background: #f9f6f0; padding: 16px; border-radius: 8px; margin-top: 20px;'>
                            <p style='margin: 0; white-space: pre-wrap;'>" . nl2br(htmlspecialchars($message)) . "</p>
                        </div>
                    </div>
                </body>
                </html>
                ";
                $mail->AltBody = $body;

                $email_sent = send_mail_with_fallback($mail);
            } catch (Exception $e) {
                error_log('Contact notification failed: ' . $e->getMessage());
            }

            $success_message = 'Thank you, ' . htmlspecialchars($name) . '! Your message has been saved. We will reply within 24 hours.';
            if (!$email_sent) {
                $warning_message = 'Your message is safely saved in our system. We may respond with a slight delay.';
            }
        } catch (PDOException $e) {
            $error_message = 'Sorry, there was an error sending your message. Please try again later.';
            error_log('Contact message save failed: ' . $e->getMessage());
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
                    <?php if ($warning_message): ?>
                        <div class="alert alert-warning"><?php echo $warning_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!$success_message): // Hide form on success ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
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
             <div class="card shadow border-0 h-100 contact-info-card">
                <div class="card-body p-4 p-md-5">
                    <h4 class="mb-4">Contact Information</h4>
                    <div class="contact-info-row">
                        <span class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></span>
                        <span>Plot 123, Main Street, Kampala, Uganda</span>
                    </div>
                    <div class="contact-info-row">
                        <span class="contact-info-icon"><i class="fas fa-phone"></i></span>
                        <a href="tel:+256700123456">+256 700 123456</a>
                    </div>
                    <div class="contact-info-row">
                        <span class="contact-info-icon"><i class="fas fa-envelope"></i></span>
                        <a href="mailto:<?php echo htmlspecialchars($supportEmail); ?>"><?php echo htmlspecialchars($supportEmail); ?></a>
                    </div>
                    <hr>
                    <h5 class="h6 mt-4"><i class="fas fa-clock me-2" style="color:#F39C6A;"></i>Business Hours</h5>
                    <p class="text-muted mb-1">Everyday: 8:00 AM - 9:00 PM</p>
                </div>
             </div>
        </div>
    </div>

    <!-- Google Map Section
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow border-0">
                 <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h4 class="text-center">Find Us Here</h4>
                </div>
                <div class="card-body p-2">
                    <div class="map-responsive">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.7628742108036!2d32.55850780657654!3d0.30206277072840165!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x177dbcbe6b564e51%3A0x9318f49c4b969eff!2s13%20Nabunya%20Rd%2C%20Kampala!5e0!3m2!1sen!2sug!4v1755984427882!5m2!1sen!2sug"
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
</div> -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>