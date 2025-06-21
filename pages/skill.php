<?php
require_once '../includes/functions.php';
requireLogin();

$skillId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$skill = getSkillById($skillId);

if (!$skill) {
    setFlashMessage('error', 'Skill not found.');
    redirect('home.php');
}

$page_title = $skill['name'];

// Get filter parameters
$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get content for this skill
$contentData = getContentBySkill($skillId, $type, $sort, $page);
$content = $contentData['data'];

include '../templates/header.php';
?>

<div class="container">
    <!-- Skill Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-2">
                                <i class="bi bi-<?php echo $skill['category'] === 'tech' ? 'code-slash' : 'heart'; ?>"></i>
                                <?php echo htmlspecialchars($skill['name']); ?>
                            </h1>
                            <p class="mb-0"><?php echo htmlspecialchars($skill['description']); ?></p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="h4"><?php echo $contentData['total']; ?> Content Items</div>
                            <small class="text-light">Available for learning</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-funnel"></i> Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="id" value="<?php echo $skillId; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Content Type</label>
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="video" <?php echo $type === 'video' ? 'selected' : ''; ?>>Video</option>
                                <option value="blog" <?php echo $type === 'blog' ? 'selected' : ''; ?>>Blog</option>
                                <option value="quiz" <?php echo $type === 'quiz' ? 'selected' : ''; ?>>Quiz</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Sort By</label>
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            </select>
                        </div>
                        
                        <div class="d-grid">
                            <a href="skill.php?id=<?php echo $skillId; ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-clockwise"></i> Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-bar-chart"></i> Stats
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    $videoCount = $db->count('content', ['skill_id' => $skillId, 'type' => 'video', 'status' => 'approved']);
                    $blogCount = $db->count('content', ['skill_id' => $skillId, 'type' => 'blog', 'status' => 'approved']);
                    $quizCount = $db->count('content', ['skill_id' => $skillId, 'type' => 'quiz', 'status' => 'approved']);
                    ?>
                    <div class="mb-2">
                        <small class="text-muted">Videos</small>
                        <div class="fw-bold"><?php echo $videoCount; ?></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Blogs</small>
                        <div class="fw-bold"><?php echo $blogCount; ?></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Quizzes</small>
                        <div class="fw-bold"><?php echo $quizCount; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content List -->
        <div class="col-lg-9">
            <?php if ($content): ?>
                <div class="row">
                    <?php foreach ($content as $item): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card content-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-<?php echo $item['type'] === 'video' ? 'danger' : ($item['type'] === 'quiz' ? 'warning' : 'primary'); ?>">
                                            <?php echo ucfirst($item['type']); ?>
                                        </span>
                                        <span class="text-muted small">
                                            <?php echo formatDate($item['upload_date']); ?>
                                        </span>
                                    </div>
                                    
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                    <p class="card-text text-muted">
                                        <?php echo truncateText($item['description'], 100); ?>
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/uploads/<?php echo $item['profile_pic']; ?>" 
                                                 class="rounded-circle me-2" width="24" height="24" 
                                                 onerror="this.src='../assets/uploads/default.jpg'">
                                            <small class="text-muted"><?php echo htmlspecialchars($item['username']); ?></small>
                                        </div>
                                        <span class="coin-badge">
                                            <?php echo $item['coin_cost']; ?> 🐝
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-sm btn-outline-primary me-2" 
                                                    onclick="voteContent(<?php echo $item['id']; ?>, 'like')">
                                                <i class="bi bi-hand-thumbs-up"></i> <?php echo $item['likes_count']; ?>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary me-2" 
                                                    onclick="voteContent(<?php echo $item['id']; ?>, 'dislike')">
                                                <i class="bi bi-hand-thumbs-down"></i> <?php echo $item['dislikes_count']; ?>
                                            </button>
                                        </div>
                                        
                                        <?php if ($item['coin_cost'] > 0): ?>
                                            <button class="btn btn-primary btn-sm" 
                                                    onclick="spendCoins(<?php echo $item['coin_cost']; ?>, <?php echo $item['id']; ?>)">
                                                Unlock (<?php echo $item['coin_cost']; ?> 🐝)
                                            </button>
                                        <?php else: ?>
                                            <a href="view-content.php?id=<?php echo $item['id']; ?>" class="btn btn-success btn-sm">
                                                View Free
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($contentData['pages'] > 1): ?>
                    <nav aria-label="Content pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($contentData['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?id=<?php echo $skillId; ?>&type=<?php echo $type; ?>&sort=<?php echo $sort; ?>&page=<?php echo $contentData['current_page'] - 1; ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $contentData['pages']; $i++): ?>
                                <li class="page-item <?php echo $i === $contentData['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?id=<?php echo $skillId; ?>&type=<?php echo $type; ?>&sort=<?php echo $sort; ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($contentData['current_page'] < $contentData['pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?id=<?php echo $skillId; ?>&type=<?php echo $type; ?>&sort=<?php echo $sort; ?>&page=<?php echo $contentData['current_page'] + 1; ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="mt-3">No content available</h4>
                        <p class="text-muted">Be the first to share content about <?php echo htmlspecialchars($skill['name']); ?>!</p>
                        <a href="upload.php?skill=<?php echo $skillId; ?>" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Upload Content
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?> 