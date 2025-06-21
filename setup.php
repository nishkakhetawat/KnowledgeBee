<?php
/**
 * Knowledge Bee Setup Script
 * Run this file to check system requirements and setup the database
 */

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('Error: PHP 7.4 or higher is required. Current version: ' . PHP_VERSION);
}

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die('Error: Missing required PHP extensions: ' . implode(', ', $missing_extensions));
}

// Check if config file exists
if (!file_exists('includes/config.php')) {
    die('Error: Configuration file not found. Please ensure includes/config.php exists.');
}

// Include configuration
require_once 'includes/config.php';

// Test database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Database connection successful!\n";
} catch (PDOException $e) {
    die('❌ Database connection failed: ' . $e->getMessage() . "\n");
}

// Check if tables exist
$tables = ['users', 'skills', 'content', 'badges', 'upvotes', 'comments', 'notifications'];
$missing_tables = [];

foreach ($tables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() == 0) {
        $missing_tables[] = $table;
    }
}

if (!empty($missing_tables)) {
    echo "⚠️  Missing database tables: " . implode(', ', $missing_tables) . "\n";
    echo "Please import the database schema from database/knowledgebee.sql\n";
} else {
    echo "✅ All database tables exist!\n";
}

// Check upload directory
$upload_dir = 'assets/uploads/';
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "✅ Created upload directory: $upload_dir\n";
    } else {
        echo "❌ Failed to create upload directory: $upload_dir\n";
    }
} else {
    echo "✅ Upload directory exists: $upload_dir\n";
}

// Check if upload directory is writable
if (is_writable($upload_dir)) {
    echo "✅ Upload directory is writable\n";
} else {
    echo "❌ Upload directory is not writable. Please set permissions to 755\n";
}

// Check for sample data
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$user_count = $stmt->fetch()['count'];

if ($user_count == 0) {
    echo "⚠️  No users found in database. Sample data may not be imported.\n";
    echo "You can import sample data from database/knowledgebee.sql\n";
} else {
    echo "✅ Database contains $user_count users\n";
}

// Display setup information
echo "\n🎉 Knowledge Bee Setup Complete!\n";
echo "================================\n";
echo "Default admin credentials:\n";
echo "Username: admin\n";
echo "Password: admin123\n\n";
echo "Access your application at: http://localhost/knowledgebee/\n";
echo "Make sure to change the default admin password after first login!\n\n";

// Security recommendations
echo "🔒 Security Recommendations:\n";
echo "1. Change default admin password\n";
echo "2. Update database credentials in includes/config.php\n";
echo "3. Set error_reporting to 0 in production\n";
echo "4. Use HTTPS in production\n";
echo "5. Regularly backup your database\n";
?> 