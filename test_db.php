<?php
// Database connection test
require_once 'includes/config.php';

echo "<h2>Database Connection Test</h2>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Database connection successful!<br>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists<br>";
        
        // Check user count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "✅ Found $count users in database<br>";
        
        // Show sample users
        $stmt = $pdo->query("SELECT username, email FROM users LIMIT 5");
        echo "<h3>Sample Users:</h3>";
        echo "<ul>";
        while ($user = $stmt->fetch()) {
            echo "<li>{$user['username']} ({$user['email']})</li>";
        }
        echo "</ul>";
        
        // Test password verification
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "✅ Admin user found<br>";
            if (password_verify('admin123', $admin['password'])) {
                echo "✅ Admin password is correct<br>";
            } else {
                echo "❌ Admin password verification failed<br>";
            }
        } else {
            echo "❌ Admin user not found<br>";
        }
        
    } else {
        echo "❌ Users table does not exist<br>";
        echo "Please import the database schema from database/knowledgebee.sql<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Configuration:</h3>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "DB_USER: " . DB_USER . "<br>";
echo "DB_PASS: " . (DB_PASS ? '***' : 'empty') . "<br>";
?> 