<?php
$page_title = 'Security Settings';
require_once __DIR__ . '/includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/totp.php';

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

$stmt = $pdo->prepare("SELECT id, username, email, password, two_factor_enabled, two_factor_secret FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'start_2fa_setup') {
            // Generate a new secret and hold it in the session until the
            // admin proves they scanned it correctly with a valid code.
            $_SESSION['pending_2fa_secret'] = TOTP::generateSecret();
        } elseif ($action === 'confirm_2fa_setup') {
            $code = trim($_POST['code'] ?? '');
            $pendingSecret = $_SESSION['pending_2fa_secret'] ?? '';
            if (empty($pendingSecret)) {
                $errors[] = 'Your setup session expired. Please start again.';
            } elseif (!TOTP::verify($pendingSecret, $code)) {
                $errors[] = 'That code did not match. Please check your authenticator app and try again.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET two_factor_secret = ?, two_factor_enabled = 1 WHERE id = ?");
                $stmt->execute([$pendingSecret, $user_id]);
                unset($_SESSION['pending_2fa_secret']);
                log_audit_event('admin_2fa_enabled', 'user', $user_id, 'Two-factor authentication enabled.');
                $success = 'Two-factor authentication is now enabled on your account.';
                $admin['two_factor_enabled'] = 1;
            }
        } elseif ($action === 'disable_2fa') {
            $currentPassword = $_POST['current_password'] ?? '';
            if (!password_verify($currentPassword, $admin['password'])) {
                $errors[] = 'Incorrect password. 2FA was not disabled.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE id = ?");
                $stmt->execute([$user_id]);
                log_audit_event('admin_2fa_disabled', 'user', $user_id, 'Two-factor authentication disabled.');
                $success = 'Two-factor authentication has been disabled.';
                $admin['two_factor_enabled'] = 0;
                unset($_SESSION['pending_2fa_secret']);
            }
        }
    }
}

$pendingSecret = $_SESSION['pending_2fa_secret'] ?? null;
$otpAuthUrl = $pendingSecret ? TOTP::getOtpAuthUrl($pendingSecret, $admin['email'], SITE_NAME) : null;
$qrImageUrl = $otpAuthUrl ? 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($otpAuthUrl) : null;
?>

<div class="row">
    <div class="col-lg-7">
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Two-Factor Authentication</h6></div>
            <div class="card-body">
                <?php if (!empty($admin['two_factor_enabled'])): ?>
                    <p class="text-success"><i class="fas fa-check-circle me-2"></i> 2FA is currently <strong>enabled</strong> on your account.</p>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="disable_2fa">
                        <label class="form-label">Confirm your password to disable 2FA</label>
                        <div class="input-group" style="max-width: 400px;">
                            <input type="password" name="current_password" class="form-control" required>
                            <button type="submit" class="btn btn-outline-danger">Disable 2FA</button>
                        </div>
                    </form>
                <?php elseif ($pendingSecret): ?>
                    <p>Scan this QR code with Google Authenticator, Authy, or any TOTP app, then enter the 6-digit code it shows to confirm setup.</p>
                    <div class="text-center my-3">
                        <img src="<?php echo htmlspecialchars($qrImageUrl); ?>" alt="2FA QR Code" class="border rounded p-2">
                    </div>
                    <p class="text-muted small text-center">Can't scan? Enter this code manually: <code><?php echo htmlspecialchars($pendingSecret); ?></code></p>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="confirm_2fa_setup">
                        <label class="form-label">Enter the code from your app</label>
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" name="code" class="form-control text-center" maxlength="6" pattern="\d{6}" inputmode="numeric" required style="letter-spacing: 6px;">
                            <button type="submit" class="btn btn-primary">Confirm &amp; Enable</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-muted"><i class="fas fa-exclamation-circle me-2"></i> 2FA is currently <strong>disabled</strong>. A stolen admin password alone can log into this account.</p>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="start_2fa_setup">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-shield-alt me-2"></i> Set Up Two-Factor Authentication
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Activity</h6></div>
            <div class="card-body">
                <p class="mb-0">Review recent admin actions on <a href="audit_log.php">the audit log</a>.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
