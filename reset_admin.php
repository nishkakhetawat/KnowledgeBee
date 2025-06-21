<?php
// reset_admin.php
require_once 'includes/config.php'; // Make sure this path is correct

$new_password = 'admin123'; // New password you want to set
$hashed = password_hash($new_password, PASSWORD_DEFAULT); // Securely hash it

// Update password for user 'admin'
$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed);

if ($stmt->execute()) {
    echo "✅ Admin password reset successfully!";
} else {
    echo "❌ Error resetting password: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
