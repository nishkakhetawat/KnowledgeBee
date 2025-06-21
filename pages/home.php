<?php
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Home';

// Get user data
$user = getUserById($_SESSION['user_id']);

// Get skills by category
$techSkills = getAllSkills('tech');
$nonTechSkills = getAllSkills('non-tech');

// Get recent content
$recentContent = $db->fetchAll("
    SELECT c.*, u.username, u.profile_pic, s.name as skill_name,
           (SELECT COUNT(*) FROM upvotes WHERE content_id = c.id AND vote_type = 'like') as likes_count
    FROM content c 
    JOIN users u ON c.user_id = u.id 
    JOIN skills s ON c.skill_id = s.id 
    WHERE c.status = 'approved' 
    ORDER BY c.upload_date DESC 
    LIMIT 6
");

// Get trending content
$trendingContent = $db->fetchAll("
    SELECT c.*, u.username, u.profile_pic, s.name as skill_name,
           (SELECT COUNT(*) FROM upvotes WHERE content_id = c.id AND vote_type = 'like') as likes_count
    FROM content c 
    JOIN users u ON c.user_id = u.id 
    JOIN skills s ON c.skill_id = s.id 
    WHERE c.status = 'approved' 
    ORDER BY likes_count DESC, c.upload_date DESC 
    LIMIT 6
");

// Get user's badges
$userBadges = $db->fetchAll("
    SELECT b.*, s.name as skill_name 
    FROM badges b 
    JOIN skills s ON b.skill_id = s.id 
    WHERE b.user_id = ? 
    ORDER BY b.earned_date DESC
", [$_SESSION['user_id']]);

// Get user's recent content
$userContent = getUserContent($_SESSION['user_id'], null, 3);

include '../templates/header.php';
?>

<div class="container">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">Welcome back, <?php echo htmlspecialchars($user['username']); ?>! 🐝</h2>
                            <p class="mb-0">Ready to share your knowledge or learn something new?</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="coin-badge fs-4">
                                <?php echo $user['coins']; ?> Buzz Coins
                            </div>
                            <small class="text-light">Keep contributing to earn more!</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <a href="upload.php" class="btn btn-primary btn-lg w-100 mb-2">
                        <i class="bi bi-upload"></i> Upload Content
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="explore.php" class="btn btn-outline-primary btn-lg w-100 mb-2">
                        <i class="bi bi-compass"></i> Explore Skills
                    </a>
                </div>
            </div>

            <!-- Trending Content -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-fire"></i> Trending Content
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($trendingContent): ?>
                        <div class="row">
                            <?php foreach ($trendingContent as $content): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card content-card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-primary"><?php echo ucfirst($content['type']); ?></span>
                                                <span class="text-muted small">
                                                    <i class="bi bi-heart-fill text-danger"></i> <?php echo $content['likes_count']; ?>
                                                </span>
                                            </div>
                                            <h6 class="card-title"><?php echo htmlspecialchars($content['title']); ?></h6>
                                            <p class="card-text small text-muted">
                                                <?php echo truncateText($content['description'], 80); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    by <?php echo htmlspecialchars($content['username']); ?>
                                                </small>
                                                <span class="coin-badge small">
                                                    <?php echo $content['coin_cost']; ?> 🐝
                                                </span>
                                            </div>
                                            <a href="skill.php?id=<?php echo $content['skill_id']; ?>" class="stretched-link"></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No trending content yet. Be the first to upload!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Content -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock"></i> Recently Added
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($recentContent): ?>
                        <div class="row">
                            <?php foreach ($recentContent as $content): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card content-card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-success"><?php echo ucfirst($content['type']); ?></span>
                                                <span class="text-muted small">
                                                    <?php echo formatDate($content['upload_date']); ?>
                                                </span>
                                            </div>
                                            <h6 class="card-title"><?php echo htmlspecialchars($content['title']); ?></h6>
                                            <p class="card-text small text-muted">
                                                <?php echo truncateText($content['description'], 80); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    in <?php echo htmlspecialchars($content['skill_name']); ?>
                                                </small>
                                                <span class="coin-badge small">
                                                    <?php echo $content['coin_cost']; ?> 🐝
                                                </span>
                                            </div>
                                            <a href="skill.php?id=<?php echo $content['skill_id']; ?>" class="stretched-link"></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No recent content. Start exploring!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- User Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-person"></i> Your Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 text-primary"><?php echo $user['coins']; ?></div>
                            <small class="text-muted">Buzz Coins</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-success"><?php echo count($userBadges); ?></div>
                            <small class="text-muted">Badges</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Your Badges -->
            <?php if ($userBadges): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-award"></i> Your Badges
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($userBadges as $badge): ?>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-award-fill text-warning me-2"></i>
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($badge['badge_title']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($badge['skill_name']); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Your Recent Content -->
            <?php if ($userContent): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-collection"></i> Your Content
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($userContent as $content): ?>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-<?php echo $content['type'] === 'video' ? 'play-circle' : ($content['type'] === 'quiz' ? 'question-circle' : 'file-text'); ?> me-2"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small"><?php echo htmlspecialchars($content['title']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($content['skill_name']); ?></small>
                                </div>
                                <span class="badge bg-<?php echo $content['status'] === 'approved' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($content['status']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                        <a href="my-content.php" class="btn btn-sm btn-outline-primary w-100">View All</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Skill Categories -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-grid"></i> Skill Categories
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">Tech Skills</h6>
                        <?php foreach (array_slice($techSkills, 0, 5) as $skill): ?>
                            <a href="skill.php?id=<?php echo $skill['id']; ?>" class="d-block text-decoration-none mb-1">
                                <i class="bi bi-code-slash"></i> <?php echo htmlspecialchars($skill['name']); ?>
                                <span class="badge bg-secondary float-end"><?php echo $skill['content_count']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <div>
                        <h6 class="text-success">Non-Tech Skills</h6>
                        <?php foreach (array_slice($nonTechSkills, 0, 5) as $skill): ?>
                            <a href="skill.php?id=<?php echo $skill['id']; ?>" class="d-block text-decoration-none mb-1">
                                <i class="bi bi-heart"></i> <?php echo htmlspecialchars($skill['name']); ?>
                                <span class="badge bg-secondary float-end"><?php echo $skill['content_count']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <a href="explore.php" class="btn btn-sm btn-outline-primary w-100 mt-3">Explore All Skills</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?> 