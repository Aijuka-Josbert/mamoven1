<?php
$page_title = 'About Us';
require_once __DIR__ . '/includes/header.php';

$feedback_success = '';
$feedback_error = '';
$feedback_input = [
    'name' => $_SESSION['full_name'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'rating' => '5',
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $feedback_input['name'] = trim($_POST['name'] ?? '');
    $feedback_input['email'] = trim($_POST['email'] ?? '');
    $feedback_input['rating'] = (string)((int)($_POST['rating'] ?? 0));
    $feedback_input['message'] = trim($_POST['message'] ?? '');

    $rating = (int)$feedback_input['rating'];

    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $feedback_error = 'Your session security token is missing or expired. Please refresh the page and try again.';
    } elseif ($feedback_input['name'] === '') {
        $feedback_error = 'Please enter your name.';
    } elseif ($feedback_input['message'] === '') {
        $feedback_error = 'Please write your feedback message.';
    } elseif ($rating < 1 || $rating > 5) {
        $feedback_error = 'Please select a rating between 1 and 5.';
    } elseif ($feedback_input['email'] !== '' && !filter_var($feedback_input['email'], FILTER_VALIDATE_EMAIL)) {
        $feedback_error = 'Please enter a valid email address.';
    } elseif (strlen($feedback_input['message']) > 1000) {
        $feedback_error = 'Feedback message is too long.';
    } else {
        try {
            $insert_stmt = $pdo->prepare(
                'INSERT INTO testimonials (user_id, name, email, message, rating, status) VALUES (?, ?, ?, ?, ?, "pending")'
            );
            $insert_stmt->execute([
                $_SESSION['user_id'] ?? null,
                $feedback_input['name'],
                $feedback_input['email'] !== '' ? $feedback_input['email'] : null,
                $feedback_input['message'],
                $rating,
            ]);

            $feedback_success = 'Thanks for your feedback. It has been submitted for admin review.';
            $feedback_input['message'] = '';
            $feedback_input['rating'] = '5';
        } catch (PDOException $e) {
            $feedback_error = 'Could not submit feedback right now. Please try again later.';
            error_log('Feedback submission failed: ' . $e->getMessage());
        }
    }
}

// Fetch approved testimonials
try {
    $testimonials_stmt = $pdo->prepare("
        SELECT * FROM testimonials 
        WHERE status = 'approved' 
        ORDER BY created_at DESC 
        LIMIT 6
    ");
    $testimonials_stmt->execute();
    $testimonials = $testimonials_stmt->fetchAll();
} catch (PDOException $e) {
    $testimonials = [];
}
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="section-title">Our Story</h1>
        <p class="lead text-muted">Baking with passion, serving with love.</p>
    </div>

    <div class="row align-items-center g-5">
        <div class="col-lg-6">
            <img src="assets/image2/new.jpg" alt="The team at Mama's Oven" class="img-fluid rounded shadow about-hero-img" height="30px">
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

            <h4 class="mt-4">Our Values</h4>
            <div class="about-values-grid">
                <div class="about-value-item">
                    <i class="fas fa-award"></i>
                    <div><strong>Quality</strong><span>Premium ingredients and traditional techniques.</span></div>
                </div>
                <div class="about-value-item">
                    <i class="fas fa-hands-helping"></i>
                    <div><strong>Community</strong><span>Supporting local suppliers and giving back.</span></div>
                </div>
                <div class="about-value-item">
                    <i class="fas fa-cookie"></i>
                    <div><strong>Craftsmanship</strong><span>Attention to detail in every product.</span></div>
                </div>
                <div class="about-value-item">
                    <i class="fas fa-leaf"></i>
                    <div><strong>Sustainability</strong><span>Minimizing waste, responsible packaging.</span></div>
                </div>
            </div>

            <a href="contact.php" class="btn btn-primary mt-3">Get in Touch</a>
        </div>
    </div>

    <!-- Team Section -->
    <div class="row mt-5">
        <div class="col-12 text-center mb-4">
            <h3 class="section-title">Meet Our Bakers</h3>
            <p class="text-muted">A small team of passionate bakers and pastry artists.</p>
        </div>

        <!-- <div class="col-md-4 text-center"> -->
        <div class="text-center">
            <img src="assets/image2/sheba.jpeg" alt="Head Baker" class="about-avatar-frame mb-3">
            <h5 class="mb-0">Nkinzi Sheba</h5>
            <small class="text-muted d-block mb-2">Head Baker & Founder</small>
            <p class="text-muted">Recipe guardian and flavor innovator.</p>
        </div>

        <!-- <div class="col-md-4 text-center">
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
        </div> -->
    </div>

    <!-- Testimonials Section -->
    <div class="row mt-5">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <h3 class="section-title mb-3">Share Your Feedback</h3>
                    <p class="text-muted mb-4">Tell us about your experience with Mama's Oven. Approved feedback appears on this page.</p>

                    <?php if (!empty($feedback_success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($feedback_success); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($feedback_error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($feedback_error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="feedback_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" id="feedback_name" name="name" class="form-control" required value="<?php echo htmlspecialchars($feedback_input['name']); ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="feedback_email" class="form-label">Email (Optional)</label>
                                <input type="email" id="feedback_email" name="email" class="form-control" value="<?php echo htmlspecialchars($feedback_input['email']); ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="feedback_rating" class="form-label">Rating <span class="text-danger">*</span></label>
                                <select id="feedback_rating" name="rating" class="form-select" required>
                                    <option value="5" <?php echo $feedback_input['rating'] === '5' ? 'selected' : ''; ?>>5 - Excellent</option>
                                    <option value="4" <?php echo $feedback_input['rating'] === '4' ? 'selected' : ''; ?>>4 - Very Good</option>
                                    <option value="3" <?php echo $feedback_input['rating'] === '3' ? 'selected' : ''; ?>>3 - Good</option>
                                    <option value="2" <?php echo $feedback_input['rating'] === '2' ? 'selected' : ''; ?>>2 - Fair</option>
                                    <option value="1" <?php echo $feedback_input['rating'] === '1' ? 'selected' : ''; ?>>1 - Poor</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="feedback_message" class="form-label">Feedback <span class="text-danger">*</span></label>
                                <textarea id="feedback_message" name="message" class="form-control" rows="4" maxlength="1000" required><?php echo htmlspecialchars($feedback_input['message']); ?></textarea>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" name="submit_feedback" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i> Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($testimonials)): ?>
    <div class="row mt-5">
        <div class="col-12 text-center mb-4">
            <h3 class="section-title">What Our Customers Say</h3>
            <p class="text-muted">Hear from the people who love our baked goods.</p>
        </div>
    </div>

    <div class="row">
        <?php foreach ($testimonials as $testimonial): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="testimonial-card">
                <div class="mb-3">
                    <div class="stars">
                        <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                            ★
                        <?php endfor; ?>
                        <?php for ($i = $testimonial['rating']; $i < 5; $i++): ?>
                            ☆
                        <?php endfor; ?>
                    </div>
                </div>
                <p class="testimonial-text"><?php echo htmlspecialchars($testimonial['message']); ?></p>
                <p class="testimonial-author"><?php echo htmlspecialchars($testimonial['name']); ?></p>
                <?php if (!empty($testimonial['email'])): ?>
                    <p class="testimonial-role"><?php echo htmlspecialchars($testimonial['email']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Sustainability / Local Sourcing -->
    <div class="row mt-5">
        <div class="col-md-6">
            <h4><i class="fas fa-seedling me-2" style="color:#F39C6A;"></i>Local Ingredients</h4>
            <p class="text-muted">We partner with local farms and suppliers to source fresh produce and grains — supporting the community while ensuring superior flavor.</p>
        </div>
        <div class="col-md-6">
            <h4><i class="fas fa-recycle me-2" style="color:#F39C6A;"></i>Sustainable Practices</h4>
            <p class="text-muted">From recycled packaging choices to minimizing food waste, we strive to operate responsibly for our customers and the environment.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>