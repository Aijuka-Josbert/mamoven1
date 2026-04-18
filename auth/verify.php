<?php
$page_title = 'Verify Your Account';
require_once __DIR__ . '/../config/database.php';
// Remove session_start() because it's started in header.php
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['verify_email'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$email = $_SESSION['verify_email'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_code'])) {
        $code = trim($_POST['code']);
        
        $stmt = $pdo->prepare("SELECT id, verification_code FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && $user['verification_code'] === $code) {
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE email = ?");
            $stmt->execute([$email]);
            
            // Auto login
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user_data = $stmt->fetch();
            
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['full_name'] = $user_data['full_name'];
            $_SESSION['role'] = $user_data['role'];
            unset($_SESSION['verify_email']);
            
            $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/index.php?registered=1';
            unset($_SESSION['redirect_after_login']);
            
            echo "<script>window.location.href='{$redirect}';</script>";
            exit;
        } else {
            $error = 'Invalid verification code. Please try again.';
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-envelope-open-text text-primary" style="font-size: 3rem;"></i>
                        <h2 class="auth-title mt-3 mb-1">Verify Email</h2>
                        <p class="text-muted mb-0">We sent a 6-digit code to <strong><?php echo htmlspecialchars($email); ?></strong></p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle flex-shrink-0 me-2"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="code" class="form-label text-muted small text-uppercase fw-bold">Verification Code</label>
                            <input type="text" class="form-control form-control-lg text-center" id="code" name="code" 
                                   maxlength="6" autocomplete="off" required 
                                   style="letter-spacing: 0.5em; font-size: 1.5rem;"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" name="verify_code" class="btn btn-primary btn-lg">
                                Verify Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
