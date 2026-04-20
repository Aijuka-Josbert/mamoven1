<?php
// Always start the session to access its variables
session_start();

// Include the base URL for a reliable redirect
include_once __DIR__ . '/../config/database.php';

clear_auth_session_data();

// Redirect to the homepage with a logout message
header('Location: ../index.php?logout=1');
exit;
?>