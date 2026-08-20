<?php
/**
 * Health check endpoint for external uptime monitoring.
 *
 * Point a free monitor (UptimeRobot, cron-job.org, StatusCake) at:
 *   https://yourdomain.com/mamoven1/health.php
 *
 * Checks real database connectivity, not just "PHP responded" — a server
 * that's up but can't reach its database is still down in every way that
 * matters to a customer.
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

$status = 'ok';
$checks = [];
$httpCode = 200;

try {
    $pdo->query('SELECT 1');
    $checks['database'] = 'ok';
} catch (Throwable $e) {
    $checks['database'] = 'failed';
    $status = 'unhealthy';
    $httpCode = 503;
    error_log('Health check: database connection failed - ' . $e->getMessage());
}

if (PESAJET_ENABLED && PESAJET_API_KEY === '') {
    $checks['pesajet_config'] = 'warning: enabled but no API key set';
} else {
    $checks['pesajet_config'] = 'ok';
}

http_response_code($httpCode);
echo json_encode([
    'status' => $status,
    'timestamp' => date('c'),
    'checks' => $checks,
]);
