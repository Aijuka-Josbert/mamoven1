<?php

// Load simple .env file (if present) so getenv() works without external libs.
function load_dotenv($path)
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '//') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

load_dotenv(__DIR__ . '/../.env');

$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocalhost = in_array($httpHost, ['localhost', '127.0.0.1', 'localhost:8000', '127.0.0.1:80'], true);
$isInfinityHosting =
    stripos($httpHost, 'infinityfreeapp.com') !== false ||
    stripos($httpHost, '.epizy.com') !== false ||
    stripos($httpHost, '.rf.gd') !== false;

$appEnv = strtolower((string)(getenv('APP_ENV') ?: ($isLocalhost ? 'development' : 'production')));
$isProduction = $appEnv === 'production';

// Prevent PCRE JIT warnings on restricted hosts (common on shared hosting).
ini_set('pcre.jit', '0');

error_reporting(E_ALL);
ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('log_errors', '1');

$host = getenv('DB_HOST') ?: ($isLocalhost ? 'localhost' : 'sql303.infinityfree.com');
$dbname = getenv('DB_NAME') ?: ($isLocalhost ? 'mamaove' : 'if0_40616210_mamaove');
$username = getenv('DB_USER') ?: ($isLocalhost ? 'root' : 'if0_40616210');
$password = getenv('DB_PASS') ?: '';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

if (getenv('BASE_URL')) {
    define('BASE_URL', rtrim((string)getenv('BASE_URL'), '/'));
} else {
    $https = $_SERVER['HTTPS'] ?? '';
    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    $isHttps = $https === 'on' || $https === '1' || strtolower($forwardedProto) === 'https';
    $protocol = $isHttps ? 'https' : 'http';

    if ($isLocalhost) {
        define('BASE_URL', $protocol . '://' . $httpHost . '/mamoven1');
    } else {
        define('BASE_URL', $protocol . '://mamoven.infinityfreeapp.com/mamoven1');
    }
}

define('IS_PRODUCTION', $isProduction);
define('IS_INFINITY_HOSTING', $isInfinityHosting);
define('SITE_NAME', getenv('SITE_NAME') ?: "Mama's Oven");
define('EMAIL_TRANSPORT', strtolower((string)(getenv('EMAIL_TRANSPORT') ?: 'auto')));

define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 587));
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');

$adminEmail = getenv('ADMIN_EMAIL') ?: (getenv('SMTP_USER') ?: 'info@mamasoven.com');
define('ADMIN_EMAIL', $adminEmail);

$hostWithoutPort = preg_replace('/:\\d+$/', '', $httpHost);
$defaultMailFrom = $hostWithoutPort ? ('noreply@' . $hostWithoutPort) : 'noreply@localhost';
define('MAIL_FROM', getenv('MAIL_FROM') ?: $defaultMailFrom);
define('PASSWORD_RESET_EXPIRY', 7200); // 2 hours in seconds

define('UPLOAD_PATH', __DIR__ . '/../assets/images/');
define('UPLOAD_URL', BASE_URL . '/assets/images/');

function should_use_smtp_transport()
{
    if (EMAIL_TRANSPORT === 'smtp') {
        return SMTP_USER !== '' && SMTP_PASS !== '';
    }

    if (EMAIL_TRANSPORT === 'mail') {
        return false;
    }

    // auto mode: prefer mail on Infinity hosting, otherwise use SMTP if configured
    if (IS_INFINITY_HOSTING) {
        return false;
    }

    return SMTP_USER !== '' && SMTP_PASS !== '';
}

function configure_mailer_transport($mail)
{
    if (should_use_smtp_transport()) {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
    } else {
        // InfinityFree blocks outbound SMTP ports; PHP mail() is the default safe transport.
        $mail->isMail();
    }
}

function default_mail_from_address()
{
    if (should_use_smtp_transport()) {
        return SMTP_USER;
    }

    return MAIL_FROM;
}

function send_mail_with_fallback($mail)
{
    try {
        configure_mailer_transport($mail);
        return $mail->send();
    } catch (Throwable $primaryError) {
        error_log('Primary mail transport failed: ' . $primaryError->getMessage());

        // If SMTP failed, try local mail transport as fallback.
        if (should_use_smtp_transport()) {
            try {
                $mail->isMail();
                return $mail->send();
            } catch (Throwable $fallbackError) {
                error_log('Fallback mail() transport failed: ' . $fallbackError->getMessage());
            }
        }

        return false;
    }
}

function email_logo_path()
{
    static $logoPath = null;
    static $resolved = false;

    if ($resolved) {
        return $logoPath;
    }

    $resolved = true;

    $candidates = [
        __DIR__ . '/../assets/images/logo.jpeg',
        __DIR__ . '/../assets/images/logo.jpg',
        __DIR__ . '/../assets/images/logo.png',
        __DIR__ . '/../assets/image2/logo.jpeg',
        __DIR__ . '/../assets/image2/logo.jpg',
        __DIR__ . '/../assets/image2/logo.png',
    ];

    foreach ($candidates as $candidate) {
        if (is_readable($candidate)) {
            $logoPath = $candidate;
            break;
        }
    }

    return $logoPath;
}

function email_logo_html($mail, $height = 80)
{
    $height = max(24, (int)$height);
    $style = "height: {$height}px; margin-bottom: 15px;";
    $alt = htmlspecialchars(SITE_NAME . ' Logo', ENT_QUOTES, 'UTF-8');
    $logoPath = email_logo_path();

    if ($logoPath && is_object($mail) && method_exists($mail, 'addEmbeddedImage')) {
        try {
            $mail->addEmbeddedImage($logoPath, 'site-logo', basename($logoPath));
            return "<img src='cid:site-logo' alt='{$alt}' style='{$style}'>";
        } catch (Throwable $e) {
            error_log('Could not embed email logo: ' . $e->getMessage());
        }
    }

    return "<img src='" . BASE_URL . "/assets/images/logo.jpeg' alt='{$alt}' style='{$style}'>";
}