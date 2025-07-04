<?php
require_once '../includes/functions.php';
requireLogin();

$contentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$contentId) {
    setFlashMessage('error', 'Invalid content ID.');
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

// Get content creator info
$creator = getUserById($content['user_id']);
$skill = getSkillById($content['skill_id']);

$page_title = $content['title'];

include '../templates/header.php';
?>

<div class="container">
    <!-- Content Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title mb-2"><?php echo htmlspecialchars($content['title']); ?></h1>
                    <div class="d-flex align-items-center mb-2">
                        <img src="../assets/uploads/<?php echo $creator['profile_pic']; ?>" 
                             class="rounded-circle me-2" width="30" height="30"
                             onerror="this.src='../assets/uploads/default.jpg'">
                        <span class="text-muted">by <?php echo htmlspecialchars($creator['username']); ?></span>
                        <span class="badge bg-primary ms-2"><?php echo htmlspecialchars($skill['name']); ?></span>
                        <span class="badge bg-secondary ms-1"><?php echo ucfirst($content['type']); ?></span>
                    </div>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar"></i> <?php echo formatDate($content['upload_date']); ?>
                        <?php if ($content['coin_cost']): ?>
                            <span class="ms-3">
                                <i class="bi bi-coin"></i> <?php echo $content['coin_cost']; ?> Buzz Coins
                            </span>
                        <?php endif; ?>
                    </p>
                    <p class="card-text mt-3"><?php echo nl2br(htmlspecialchars($content['description'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Display -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-play-circle"></i> Content
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($content['type'] === 'video'): ?>
                        <?php if (strpos($content['content_data'], 'youtube.com') !== false || strpos($content['content_data'], 'youtu.be') !== false): ?>
                            <?php
                            $videoId = '';
                            if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $content['content_data'], $matches)) {
                                $videoId = $matches[1];
                            } elseif (preg_match('/youtube\.com\/embed\/([^&]+)/', $content['content_data'], $matches)) {
                                $videoId = $matches[1];
                            } elseif (preg_match('/youtu\.be\/([^&]+)/', $content['content_data'], $matches)) {
                                $videoId = $matches[1];
                            }
                            ?>
                            <?php if ($videoId): ?>
                                <div class="ratio ratio-16x9">
                                    <iframe src="https://www.youtube.com/embed/<?php echo $videoId; ?>" 
                                            title="<?php echo htmlspecialchars($content['title']); ?>"
                                            frameborder="0" allowfullscreen></iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Invalid YouTube URL format
                                    <br><small class="text-muted">URL: <?php echo htmlspecialchars($content['content_data']); ?></small>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center">
                                <i class="bi bi-play-circle display-1 text-muted mb-3"></i>
                                <h5><?php echo htmlspecialchars($content['title']); ?></h5>
                                <p class="text-muted">Video Content</p>
                                <div class="content-text">
                                    <?php echo nl2br(htmlspecialchars($content['content_data'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($content['type'] === 'blog'): ?>
                        <div class="content-text">
                            <?php echo nl2br(htmlspecialchars($content['content_data'])); ?>
                        </div>
                    <?php elseif ($content['type'] === 'quiz'): ?>
                        <div class="text-center">
                            <i class="bi bi-question-circle display-1 text-muted mb-3"></i>
                            <h5><?php echo htmlspecialchars($content['title']); ?></h5>
                            <p class="text-muted">Interactive Quiz</p>
                            <a href="take-quiz.php?id=<?php echo $content['id']; ?>" class="btn btn-primary">
                                <i class="bi bi-play-circle"></i> Start Quiz
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <i class="bi bi-file-earmark-text display-1 text-muted mb-3"></i>
                            <h5><?php echo htmlspecialchars($content['title']); ?></h5>
                            <p class="text-muted">Content Type: <?php echo ucfirst($content['type']); ?></p>
                            <div class="content-text">
                                <?php echo nl2br(htmlspecialchars($content['content_data'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-chat"></i> Comments</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="add-comment.php" class="mb-4">
                        <input type="hidden" name="content_id" value="<?php echo $content['id']; ?>">
                        <div class="mb-3">
                            <textarea class="form-control" name="comment_text" rows="3" placeholder="Share your thoughts about this content..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Post Comment</button>
                    </form>

                    <?php
                    $comments = $db->fetchAll("
                        SELECT c.*, u.username, u.profile_pic 
                        FROM comments c 
                        JOIN users u ON c.user_id = u.id 
                        WHERE c.content_id = ? 
                        ORDER BY c.created_at DESC
                    ", [$content['id']]);
                    ?>

                    <?php if ($comments): ?>
                        <div class="comments-list">
                            <?php foreach ($comments as $comment): ?>
                                <div class="d-flex mb-3">
                                    <img src="../assets/uploads/<?php echo $comment['profile_pic']; ?>" 
                                         class="rounded-circle me-3" width="40" height="40"
                                         onerror="this.src='../assets/uploads/default.jpg'">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($comment['username']); ?></h6>
                                                <p class="mb-1"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                                <small class="text-muted"><?php echo formatDateTime($comment['created_at']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="bi bi-chat-dots display-4"></i>
                            <p class="mt-2">No comments yet. Be the first to share your thoughts!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-heart-fill text-danger"></i>
                            <span class="text-muted"><?php echo $content['likes']; ?> likes</span>
                        </div>
                        <div>
                            <?php if ($content['user_id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-outline-primary me-2" onclick="voteContent(<?php echo $content['id']; ?>, 'like')">
                                    <i class="bi bi-heart"></i> Like
                                </button>
                            <?php endif; ?>
                            <a href="explore.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Explore
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
