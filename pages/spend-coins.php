<?php
require_once '../includes/functions.php';
requireLogin();

$contentId = isset($_GET['content_id']) ? (int)$_GET['content_id'] : 0;
$amount = isset($_GET['amount']) ? (int)$_GET['amount'] : 0;

if (!$contentId || !$amount) {
    setFlashMessage('error', 'Invalid request parameters.');
    redirect('../pages/explore.php');
}

// Get content details
$content = getContentById($contentId);
if (!$content) {
    setFlashMessage('error', 'Content not found.');
    redirect('../pages/explore.php');
}

// Check if user already has access to this content
$hasAccess = $db->fetchOne("SELECT * FROM content_access WHERE user_id = ? AND content_id = ?", 
                          [$_SESSION['user_id'], $contentId]);

if ($hasAccess) {
    // User already has access, redirect to view content
    redirect("view-content.php?id=$contentId");
}

// Check if user has enough coins
$userCoins = getUserCoins($_SESSION['user_id']);
if ($userCoins < $amount) {
    setFlashMessage('error', 'You don\'t have enough Buzz Coins to view this content.');
    redirect('../pages/explore.php');
}

// Process the transaction
try {
    // Start transaction
    $db->beginTransaction();
    
    // Deduct coins from user
    spendCoins($_SESSION['user_id'], $amount, "Purchased access to: " . $content['title'], $contentId);
    
    // Grant access to content
    $sql = "INSERT INTO content_access (user_id, content_id, coins_spent) VALUES (?, ?, ?)";
    $db->insert($sql, [$_SESSION['user_id'], $contentId, $amount]);
    
    // Award coins to content creator
    $creatorReward = floor($amount * 0.8); // 80% goes to creator
    addCoins($content['user_id'], $creatorReward, "Content purchased: " . $content['title'], $contentId);
    
    // Commit transaction
    $db->commit();
    
    setFlashMessage('success', "Successfully purchased access to the content for $amount Buzz Coins!");
    redirect("view-content.php?id=$contentId");
    
} catch (Exception $e) {
    // Rollback transaction
    $db->rollback();
    setFlashMessage('error', 'An error occurred while processing your purchase.');
    redirect('../pages/explore.php');
}
?> 