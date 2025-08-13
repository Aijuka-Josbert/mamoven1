<?php
// Turn on error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- DATABASE CREDENTIALS ---
$host = 'localhost';
$dbname = 'mamaove';
$username = 'root'; 
$password = '!Log19tan88'; // Change this to your database password
$charset = 'utf8mb4';

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
// Define a base URL to make links and paths consistent across the site.
// If your site is in a subfolder (e.g., http://localhost/mamas-oven), change this.
define('BASE_URL', 'http://127.0.0.1/mamaove1/'); 
define('SITE_NAME', "Mama's Oven");
define('ADMIN_EMAIL', 'admin@mamasovenug.com');

// --- UPLOAD DIRECTORY ---
// Define the path for image uploads to keep it consistent.
define('UPLOAD_PATH', __DIR__ . '/../assets/images/');
?>