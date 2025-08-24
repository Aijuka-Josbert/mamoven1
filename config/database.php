<?php
// Load simple .env file (if present) so getenv() works in PHP without external libs
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

        // Remove surrounding quotes if present
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load .env from project root (one level up from config/)
load_dotenv(__DIR__ . '/../.env');

// Turn on error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- DATABASE CREDENTIALS (read from environment) ---
$host    = getenv('DB_HOST') ?: 'localhost';
$dbname  = getenv('DB_NAME') ?: 'mamaove';
$username= getenv('DB_USER') ?: 'root';
$password= getenv('DB_PASS') ?: ''; // now loaded from .env if present
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

// --- PDO CONNECTION ---
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // For a live site, you would log this error and show a generic message.
    // For development, it's okay to show the detailed error.
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// --- SITE CONSTANTS ---
define('BASE_URL', getenv('BASE_URL') ?: 'http://127.0.0.1/mamaove1'); 
define('SITE_NAME', getenv('SITE_NAME') ?: "Mama's Oven");
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'joszialvin@gmail.com');

// --- SMTP / Email Settings (read from environment) ---
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');

// --- UPLOAD DIRECTORY ---
define('UPLOAD_PATH', __DIR__ . '/../assets/images/');
define('UPLOAD_URL', __DIR__ . '/assets/image2/');
?>