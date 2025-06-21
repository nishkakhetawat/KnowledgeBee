<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$contentId = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;
$voteType = isset($_POST['vote_type']) ? sanitizeInput($_POST['vote_type']) : '';

if (!$contentId || !in_array($voteType, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $result = voteContent($_SESSION['user_id'], $contentId, $voteType);
    echo json_encode(['success' => true, 'message' => 'Vote recorded successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error recording vote']);
}
?> 