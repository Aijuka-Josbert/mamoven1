<?php
require_once __DIR__ . '/config/database.php';

header('Location: ' . BASE_URL . '/footer_pages/terms_of_service.php', true, 301);
exit;