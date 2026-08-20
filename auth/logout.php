<?php
session_start();
include_once __DIR__ . '/../config/database.php';
if (isset($_SESSION['user_id'])) {
    log_audit_event(($_SESSION['role'] ?? '') === 'admin' ? 'admin_logout' : 'customer_logout', 'user', $_SESSION['user_id']);
}
clear_auth_session_data();
header('Location: ../index.php?logout=1');
exit;