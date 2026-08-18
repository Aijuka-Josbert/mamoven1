<?php
session_start();
include_once __DIR__ . '/../config/database.php';
clear_auth_session_data();
header('Location: ../index.php?logout=1');
exit;