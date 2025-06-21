<?php
// Create admin user script
require_once 'includes/config.php';

echo "<h2>Create Admin User</h2>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    
    if ($stmt->fetch()) {
        echo "Admin user already exists. Updating password...<br>";
        
        // Update admin password
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, coins = 1000 WHERE username = ?");
        $stmt->execute([$hashedPassword, 'admin']);
        
        echo "✅ Admin password updated successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        
    } else {
        echo "Creating new admin user...<br>";
        
        // Create admin user
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin, is_verified, coins) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@knowledgebee.com', $hashedPassword, TRUE, TRUE, 1000]);
        
        echo "✅ Admin user created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    }
    
    // Also create/update other demo users
    $demoUsers = [
        ['john_doe', 'john@example.com', 'password', 'Web developer passionate about teaching', 'HTML,CSS,JavaScript', 'Python,Guitar', 250],
        ['jane_smith', 'jane@example.com', 'password', 'Yoga instructor and wellness coach', 'Yoga,Cooking', 'Photography,Public Speaking', 180],
        ['mike_dev', 'mike@example.com', 'password', 'Full-stack developer', 'PHP,Python', 'Guitar,Cooking', 320]
    ];
    
    foreach ($demoUsers as $user) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user[0]]);
        
        if (!$stmt->fetch()) {
            $hashedPassword = password_hash($user[2], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, bio, skills_teach, skills_learn, coins) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user[0], $user[1], $hashedPassword, $user[3], $user[4], $user[5], $user[6]]);
            echo "✅ Created user: {$user[0]}<br>";
        }
    }
    
    echo "<hr>";
    echo "<h3>All Demo Accounts:</h3>";
    echo "<ul>";
    echo "<li><strong>admin</strong> / admin123 (Administrator)</li>";
    echo "<li><strong>john_doe</strong> / password (Regular User)</li>";
    echo "<li><strong>jane_smith</strong> / password (Regular User)</li>";
    echo "<li><strong>mike_dev</strong> / password (Regular User)</li>";
    echo "</ul>";
    
    echo "<p><a href='auth/login.php'>Go to Login Page</a></p>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?> 