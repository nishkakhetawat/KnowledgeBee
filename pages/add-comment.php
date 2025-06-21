<?php
require_once '../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method.');
    redirect('explore.php');
}

$contentId = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;
$commentText = isset($_POST['comment_text']) ? sanitizeInput($_POST['comment_text']) : '';

if (!$contentId || !$commentText) {
    setFlashMessage('error', 'Invalid comment data.');
    redirect('explore.php');
}

// Get content details
$content = getContentById($contentId);
if (!$content) {
    setFlashMessage('error', 'Content not found.');
    redirect('explore.php');
}

// Check if user has access to this content
$hasAccess = $db->fetchOne("SELECT * FROM content_access WHERE user_id = ? AND content_id = ?", 
                          [$_SESSION['user_id'], $contentId]);

// If user doesn't have access and it's not their own content, redirect to purchase
if (!$hasAccess && $content['user_id'] != $_SESSION['user_id']) {
    $cost = $content['coin_cost'] ?? 10; // Default cost
    setFlashMessage('info', "This content costs $cost Buzz Coins to view.");
    redirect("explore.php");
}

try {
    // Add comment
    $sql = "INSERT INTO comments (content_id, user_id, comment_text) VALUES (?, ?, ?)";
    $commentId = $db->insert($sql, [$contentId, $_SESSION['user_id'], $commentText]);
    
    if ($commentId) {
        // Award coins for commenting (if not their own content)
        if ($content['user_id'] != $_SESSION['user_id']) {
            addCoins($_SESSION['user_id'], 2, "Comment on content: " . $content['title'], $contentId);
        }
        
        setFlashMessage('success', 'Comment added successfully!');
    } else {
        setFlashMessage('error', 'Failed to add comment.');
    }
} catch (Exception $e) {
    setFlashMessage('error', 'An error occurred while adding the comment.');
}

redirect("view-content.php?id=$contentId");
?> 