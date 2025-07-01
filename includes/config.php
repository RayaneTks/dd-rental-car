<?php
// Database configuration
define('DB_PATH', __DIR__ . '/../database/dd_rental.sqlite');

// Application configuration
define('SITE_NAME', 'DD RENTAL CAR');
define('SITE_URL', 'http://localhost:8000');
define('ADMIN_EMAIL', 'admin@ddrental.com');

// Security configuration
define('SECURE', true); // Set to true if using HTTPS
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if(SECURE) {
    ini_set('session.cookie_secure', 1);
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Session configuration
session_start();

// XSS Protection
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
if(SECURE) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
}
