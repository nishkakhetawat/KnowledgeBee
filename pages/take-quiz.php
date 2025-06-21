<?php
require_once '../includes/functions.php';
requireLogin();

$contentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$contentId) {
    setFlashMessage('error', 'Invalid quiz ID.');
    redirect('explore.php');
}

// Get content details
$content = getContentById($contentId);
if (!$content) {
    setFlashMessage('error', 'Quiz not found.');
    redirect('explore.php');
}

// Check if it's actually a quiz
if ($content['type'] !== 'quiz') {
    setFlashMessage('error', 'This is not a quiz.');
    redirect('explore.php');
}

// Check if user has access to this content
$hasAccess = $db->fetchOne("SELECT * FROM content_access WHERE user_id = ? AND content_id = ?", 
                          [$_SESSION['user_id'], $contentId]);

// If user doesn't have access and it's not their own content, redirect to purchase
if (!$hasAccess && $content['user_id'] != $_SESSION['user_id']) {
    $cost = $content['coin_cost'] ?? 10; // Default cost
    setFlashMessage('info', "This quiz costs $cost Buzz Coins to take.");
    redirect("explore.php");
}

// Get quiz questions
$questions = $db->fetchAll("SELECT * FROM quizzes WHERE content_id = ? ORDER BY id", [$contentId]);

$page_title = $content['title'];

include '../templates/header.php';
?>

<div class="container">
    <!-- Quiz Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title mb-2"><?php echo htmlspecialchars($content['title']); ?></h1>
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

    <!-- Quiz Questions -->
    <?php if ($questions): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-question-circle"></i> Quiz Questions (<?php echo count($questions); ?> questions)
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="submit-quiz.php">
                            <input type="hidden" name="content_id" value="<?php echo $content['id']; ?>">
                            
                            <?php foreach ($questions as $index => $question): ?>
                                <div class="mb-4">
                                    <h6 class="mb-3">Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></h6>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="answer_<?php echo $question['id']; ?>" 
                                               id="q<?php echo $question['id']; ?>_a" value="a" required>
                                        <label class="form-check-label" for="q<?php echo $question['id']; ?>_a">
                                            <?php echo htmlspecialchars($question['option_a']); ?>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="answer_<?php echo $question['id']; ?>" 
                                               id="q<?php echo $question['id']; ?>_b" value="b" required>
                                        <label class="form-check-label" for="q<?php echo $question['id']; ?>_b">
                                            <?php echo htmlspecialchars($question['option_b']); ?>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="answer_<?php echo $question['id']; ?>" 
                                               id="q<?php echo $question['id']; ?>_c" value="c" required>
                                        <label class="form-check-label" for="q<?php echo $question['id']; ?>_c">
                                            <?php echo htmlspecialchars($question['option_c']); ?>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="answer_<?php echo $question['id']; ?>" 
                                               id="q<?php echo $question['id']; ?>_d" value="d" required>
                                        <label class="form-check-label" for="q<?php echo $question['id']; ?>_d">
                                            <?php echo htmlspecialchars($question['option_d']); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Submit Quiz
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle display-1 text-warning mb-3"></i>
                        <h4>No Questions Available</h4>
                        <p class="text-muted">This quiz doesn't have any questions yet.</p>
                        <a href="explore.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Explore
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?> 