<?php
$page_title = 'Security Settings';
require_once __DIR__ . '/includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/totp.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

$stmt = $pdo->prepare("SELECT id, username, email, password, two_factor_enabled, two_factor_method, two_factor_secret FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

function send_2fa_setup_email(string $email, string $code): bool
{
    try {
        $mail = new PHPMailer(true);
        configure_mailer_transport($mail);
        $mail->setFrom(default_mail_from_address(), SITE_NAME);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your ' . SITE_NAME . ' Two-Factor Setup Code';
        $mail->Body = "<p>Your two-factor authentication code is:</p><h2 style='letter-spacing:4px;'>{$code}</h2><p>This code expires in 10 minutes.</p>";
        $mail->AltBody = "Your two-factor code is: $code (expires in 10 minutes)";
        return send_mail_with_fallback($mail);
    } catch (Exception $e) {
        error_log('2FA setup email failed: ' . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'start_2fa_setup_totp') {
            // Generate a new secret and hold it in the session until the
            // admin proves they scanned it correctly with a valid code.
            $_SESSION['pending_2fa_secret'] = TOTP::generateSecret();
            $_SESSION['pending_2fa_method'] = 'totp';
        } elseif ($action === 'start_2fa_setup_email') {
            // Reliability matters here more than app-based codes: no phone,
            // no lost-device lockout — just the email the admin already
            // owns. Send a real code now to prove delivery works before
            // enabling it.
            $code = sprintf('%06d', random_int(100000, 999999));
            $_SESSION['pending_2fa_email_code'] = $code;
            $_SESSION['pending_2fa_email_expires'] = time() + 600;
            $_SESSION['pending_2fa_method'] = 'email';
            if (!send_2fa_setup_email($admin['email'], $code)) {
                $errors[] = 'Could not send the setup email. Check your SMTP configuration and try again.';
                unset($_SESSION['pending_2fa_email_code'], $_SESSION['pending_2fa_method']);
            }
        } elseif ($action === 'confirm_2fa_setup_totp') {
            $code = trim($_POST['code'] ?? '');
            $pendingSecret = $_SESSION['pending_2fa_secret'] ?? '';
            if (empty($pendingSecret)) {
                $errors[] = 'Your setup session expired. Please start again.';
            } elseif (!TOTP::verify($pendingSecret, $code)) {
                $errors[] = 'That code did not match. Please check your authenticator app and try again.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET two_factor_secret = ?, two_factor_method = 'totp', two_factor_enabled = 1, email_otp_code = NULL, email_otp_expires = NULL WHERE id = ?");
                $stmt->execute([$pendingSecret, $user_id]);
                unset($_SESSION['pending_2fa_secret'], $_SESSION['pending_2fa_method']);
                log_audit_event('admin_2fa_enabled', 'user', $user_id, 'Two-factor authentication enabled (authenticator app).');
                $success = 'Two-factor authentication is now enabled using your authenticator app.';
                $admin['two_factor_enabled'] = 1;
                $admin['two_factor_method'] = 'totp';
            }
        } elseif ($action === 'confirm_2fa_setup_email') {
            $code = trim($_POST['code'] ?? '');
            $pendingCode = $_SESSION['pending_2fa_email_code'] ?? '';
            $expired = ($_SESSION['pending_2fa_email_expires'] ?? 0) < time();
            if (empty($pendingCode) || $expired) {
                $errors[] = 'Your setup code expired. Please request a new one.';
            } elseif (!hash_equals($pendingCode, $code)) {
                $errors[] = 'That code did not match. Please check your email and try again.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET two_factor_method = 'email', two_factor_enabled = 1, two_factor_secret = NULL, email_otp_code = NULL, email_otp_expires = NULL WHERE id = ?");
                $stmt->execute([$user_id]);
                unset($_SESSION['pending_2fa_email_code'], $_SESSION['pending_2fa_email_expires'], $_SESSION['pending_2fa_method']);
                log_audit_event('admin_2fa_enabled', 'user', $user_id, 'Two-factor authentication enabled (email code).');
                $success = 'Two-factor authentication is now enabled using email codes.';
                $admin['two_factor_enabled'] = 1;
                $admin['two_factor_method'] = 'email';
            }
        } elseif ($action === 'disable_2fa') {
            $currentPassword = $_POST['current_password'] ?? '';
            if (!password_verify($currentPassword, $admin['password'])) {
                $errors[] = 'Incorrect password. 2FA was not disabled.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET two_factor_secret = NULL, two_factor_method = NULL, two_factor_enabled = 0, email_otp_code = NULL, email_otp_expires = NULL WHERE id = ?");
                $stmt->execute([$user_id]);
                log_audit_event('admin_2fa_disabled', 'user', $user_id, 'Two-factor authentication disabled.');
                $success = 'Two-factor authentication has been disabled.';
                $admin['two_factor_enabled'] = 0;
                unset($_SESSION['pending_2fa_secret'], $_SESSION['pending_2fa_method'], $_SESSION['pending_2fa_email_code'], $_SESSION['pending_2fa_email_expires']);
            }
        }
    }
}

$pendingSecret = $_SESSION['pending_2fa_secret'] ?? null;
$pendingEmailCode = $_SESSION['pending_2fa_email_code'] ?? null;
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
                    <p class="text-success"><i class="fas fa-check-circle me-2"></i> 2FA is currently <strong>enabled</strong> via <?php echo $admin['two_factor_method'] === 'email' ? 'email codes' : 'an authenticator app'; ?>.</p>
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
                        <input type="hidden" name="action" value="confirm_2fa_setup_totp">
                        <label class="form-label">Enter the code from your app</label>
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" name="code" class="form-control text-center" maxlength="6" pattern="\d{6}" inputmode="numeric" required style="letter-spacing: 6px;">
                            <button type="submit" class="btn btn-primary">Confirm &amp; Enable</button>
                        </div>
                    </form>
                    <p class="mt-3"><a href="security_settings.php">Cancel and start over</a></p>
                <?php elseif ($pendingEmailCode): ?>
                    <p>A 6-digit code was sent to <strong><?php echo htmlspecialchars($admin['email']); ?></strong>. Enter it below to confirm setup.</p>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="confirm_2fa_setup_email">
                        <label class="form-label">Enter the code from your email</label>
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" name="code" class="form-control text-center" maxlength="6" pattern="\d{6}" inputmode="numeric" required style="letter-spacing: 6px;">
                            <button type="submit" class="btn btn-primary">Confirm &amp; Enable</button>
                        </div>
                    </form>
                    <form method="POST" class="mt-2">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="start_2fa_setup_email">
                        <button type="submit" class="btn btn-link btn-sm p-0">Resend code</button>
                    </form>
                <?php else: ?>
                    <p class="text-muted"><i class="fas fa-exclamation-circle me-2"></i> 2FA is currently <strong>disabled</strong>. A stolen admin password alone can log into this account.</p>
                    <p class="small text-muted">Choose whichever fits you better — an authenticator app code, or a code emailed to you. Email is a good fallback if you're worried about losing your phone.</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="action" value="start_2fa_setup_totp">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-mobile-alt me-2"></i> Use Authenticator App
                            </button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="action" value="start_2fa_setup_email">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-2"></i> Use Email Codes
                            </button>
                        </form>
                    </div>
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
