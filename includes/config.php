<?php
// Knowledge Bee Configuration File
// Database and application settings

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'knowledgebee');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('SITE_NAME', 'Knowledge Bee');
define('SITE_URL', 'http://localhost/knowledgebee');
define('UPLOAD_PATH', '../assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Buzz Coin Configuration
define('COINS_UPLOAD_REWARD', 10);
define('COINS_UPVOTE_REWARD', 5);
define('COINS_QUIZ_PASS_REWARD', 7);
define('COINS_BADGE_REWARD', 15);
define('COINS_DAILY_LOGIN', 2);

// Badge Requirements
define('BADGE_UPLOAD_MIN', 3);
define('BADGE_UPVOTES_MIN', 30);
define('BADGE_QUIZ_COMPLETIONS_MIN', 10);
define('BADGE_RATING_MIN', 4.0);

// Content Moderation
define('MAX_REPORTS_BEFORE_HIDE', 3);
define('NEW_USER_UPLOAD_LIMIT', 3);
define('NEW_USER_UPVOTES_REQUIRED', 3);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Security: CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper function to validate CSRF token
function validateCSRF() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
}

// Helper function to generate CSRF token input
function csrfInput() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}
?> 