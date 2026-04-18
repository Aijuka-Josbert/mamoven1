<?php
session_start();
include_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
ob_clean();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $count = $stmt->fetchColumn() ?: 0;
    echo json_encode(['count' => (int)$count]);
    exit;
} catch (Exception $e) {
    echo json_encode(['count' => 0]);
    exit;
}
