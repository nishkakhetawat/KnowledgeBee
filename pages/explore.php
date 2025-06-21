<?php
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Explore Skills';

// Get filter parameters
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get skills with filtering
$skills = getAllSkills($category);

// Filter by search if provided
if ($search) {
    $skills = array_filter($skills, function($skill) use ($search) {
        return stripos($skill['name'], $search) !== false || 
               stripos($skill['description'], $search) !== false;
    });
}

include '../templates/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <h1 class="mb-2">
                        <i class="bi bi-compass"></i> Explore Skills
                    </h1>
                    <p class="mb-0">Discover new skills to learn and share your knowledge</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" action="" class="d-flex">
                <input type="text" class="form-control me-2" name="search" 
                       placeholder="Search skills..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Search
                </button>
            </form>
        </div>
        <div class="col-md-4">
            <div class="btn-group w-100" role="group">
                <a href="?<?php echo $search ? 'search=' . urlencode($search) . '&' : ''; ?>" 
                   class="btn btn-outline-primary <?php echo !$category ? 'active' : ''; ?>">
                    All
                </a>
                <a href="?category=tech<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="btn btn-outline-primary <?php echo $category === 'tech' ? 'active' : ''; ?>">
                    Tech
                </a>
                <a href="?category=non-tech<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="btn btn-outline-primary <?php echo $category === 'non-tech' ? 'active' : ''; ?>">
                    Non-Tech
                </a>
            </div>
        </div>
    </div>

    <!-- Skills Grid -->
    <div class="row">
        <?php if ($skills): ?>
            <?php foreach ($skills as $skill): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card skill-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="content-type-icon <?php echo $skill['category']; ?>">
                                    <i class="bi bi-<?php echo $skill['category'] === 'tech' ? 'code-slash' : 'heart'; ?>"></i>
                                </div>
                                <span class="badge bg-<?php echo $skill['category'] === 'tech' ? 'primary' : 'success'; ?>">
                                    <?php echo ucfirst($skill['category']); ?>
                                </span>
                            </div>
                            
                            <h5 class="card-title"><?php echo htmlspecialchars($skill['name']); ?></h5>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($skill['description']); ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">
                                    <?php echo $skill['content_count']; ?> content items
                                </span>
                                <a href="skill.php?id=<?php echo $skill['id']; ?>" class="btn btn-primary btn-sm">
                                    Explore
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search display-1 text-muted"></i>
                        <h4 class="mt-3">No skills found</h4>
                        <p class="text-muted">
                            <?php if ($search): ?>
                                No skills match your search for "<?php echo htmlspecialchars($search); ?>"
                            <?php else: ?>
                                No skills available in this category
                            <?php endif; ?>
                        </p>
                        <a href="explore.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Back to All Skills
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Stats Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart"></i> Platform Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <?php
                        $totalSkills = $db->count('skills', ['is_banned' => 0]);
                        $totalContent = $db->count('content', ['status' => 'approved']);
                        $totalUsers = $db->count('users', ['status' => 'active']);
                        $totalBadges = $db->count('badges');
                        ?>
                        <div class="col-md-3">
                            <div class="h3 text-primary"><?php echo $totalSkills; ?></div>
                            <small class="text-muted">Skills Available</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h3 text-success"><?php echo $totalContent; ?></div>
                            <small class="text-muted">Content Items</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h3 text-info"><?php echo $totalUsers; ?></div>
                            <small class="text-muted">Active Users</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h3 text-warning"><?php echo $totalBadges; ?></div>
                            <small class="text-muted">Badges Awarded</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?> 