<?php
// Always start the session to access its variables
session_start();

// Include the base URL for a reliable redirect
include_once __DIR__ . '/../config/database.php';

// Unset all of the session variables
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect to the homepage with a logout message
header('Location: ' . BASE_URL . '/index.php?logout=1');
exit;
?>