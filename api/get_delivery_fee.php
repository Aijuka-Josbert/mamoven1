<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (isset($_GET['location_id'])) {
    $id = (int)$_GET['location_id'];
    $stmt = $pdo->prepare("SELECT fee FROM delivery_locations WHERE id = ?");
    $stmt->execute([$id]);
    $fee = $stmt->fetchColumn();
    
    if ($fee !== false) {
        echo json_encode(['success' => true, 'fee' => (float)$fee]);
        exit;
    }
}
echo json_encode(['success' => false, 'fee' => 0]);