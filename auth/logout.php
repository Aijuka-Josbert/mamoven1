<?php
session_start();
include_once __DIR__ . '/../config/database.php';
if (($_SESSION['role'] ?? '') === 'admin') {
    log_audit_event('admin_logout', 'user', $_SESSION['user_id'] ?? null);
}
clear_auth_session_data();
header('Location: ../index.php?logout=1');
exit;