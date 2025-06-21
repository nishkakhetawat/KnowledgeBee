<?php
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Search Results';

// Get search parameters
$query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
$skillId = isset($_GET['skill']) ? (int)$_GET['skill'] : 0;

// Get all skills for filter dropdown
$skills = getAllSkills();

// Perform search
$results = [];
if ($query) {
    $results = searchContent($query, $type, $skillId);
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
                        <i class="bi bi-search"></i> Search Results
                    </h1>
                    <?php if ($query): ?>
                        <p class="mb-0">Searching for: "<?php echo htmlspecialchars($query); ?>"</p>
                    <?php else: ?>
                        <p class="mb-0">Search for skills and content</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="q" class="form-label">Search Query</label>
                                    <input type="text" class="form-control" id="q" name="q" 
                                           value="<?php echo htmlspecialchars($query); ?>" 
                                           placeholder="Enter keywords to search...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Content Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="">All Types</option>
                                        <option value="video" <?php echo $type === 'video' ? 'selected' : ''; ?>>Video</option>
                                        <option value="document" <?php echo $type === 'document' ? 'selected' : ''; ?>>Document</option>
                                        <option value="link" <?php echo $type === 'link' ? 'selected' : ''; ?>>Link</option>
                                        <option value="text" <?php echo $type === 'text' ? 'selected' : ''; ?>>Text</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="skill" class="form-label">Skill</label>
                                    <select class="form-select" id="skill" name="skill">
                                        <option value="">All Skills</option>
                                        <?php foreach ($skills as $skill): ?>
                                            <option value="<?php echo $skill['id']; ?>" 
                                                    <?php echo $skillId == $skill['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($skill['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <?php if ($query): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i> 
                            <?php echo count($results); ?> Result<?php echo count($results) !== 1 ? 's' : ''; ?> Found
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($results): ?>
                            <div class="row">
                                <?php foreach ($results as $content): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card content-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <img src="/KnowledgeBee/assets/uploads/<?php echo $content['profile_pic']; ?>" 
                                                         class="rounded-circle me-2" width="30" height="30"
                                                         onerror="this.src='/KnowledgeBee/assets/uploads/default.jpg'">
                                                    <div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($content['username']); ?></small>
                                                        <br>
                                                        <small class="text-muted"><?php echo formatDate($content['upload_date']); ?></small>
                                                    </div>
                                                </div>
                                                
                                                <h6 class="card-title"><?php echo htmlspecialchars($content['title']); ?></h6>
                                                <p class="card-text text-muted">
                                                    <?php echo truncateText($content['description'], 100); ?>
                                                </p>
                                                
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($content['skill_name']); ?></span>
                                                    <span class="badge bg-secondary"><?php echo ucfirst($content['type']); ?></span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-heart-fill text-danger"></i>
                                                        <small class="text-muted"><?php echo $content['likes_count']; ?></small>
                                                    </div>
                                                    <a href="/KnowledgeBee/pages/skill.php?id=<?php echo $content['skill_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        View Content
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-search display-1 text-muted"></i>
                                <h4 class="mt-3">No results found</h4>
                                <p class="text-muted">Try adjusting your search terms or filters.</p>
                                <a href="/KnowledgeBee/pages/explore.php" class="btn btn-primary">
                                    <i class="bi bi-compass"></i> Explore Content
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Popular Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-fire"></i> Popular Content
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $popularContent = $db->fetchAll("
                            SELECT c.*, u.username, u.profile_pic, s.name as skill_name,
                                   (SELECT COUNT(*) FROM upvotes WHERE content_id = c.id AND vote_type = 'like') as likes_count
                            FROM content c 
                            JOIN users u ON c.user_id = u.id 
                            JOIN skills s ON c.skill_id = s.id 
                            WHERE c.status = 'approved' 
                            ORDER BY likes_count DESC, c.upload_date DESC 
                            LIMIT 6
                        ");
                        ?>
                        
                        <?php if ($popularContent): ?>
                            <div class="row">
                                <?php foreach ($popularContent as $content): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card content-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <img src="/KnowledgeBee/assets/uploads/<?php echo $content['profile_pic']; ?>" 
                                                         class="rounded-circle me-2" width="30" height="30"
                                                         onerror="this.src='/KnowledgeBee/assets/uploads/default.jpg'">
                                                    <div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($content['username']); ?></small>
                                                        <br>
                                                        <small class="text-muted"><?php echo formatDate($content['upload_date']); ?></small>
                                                    </div>
                                                </div>
                                                
                                                <h6 class="card-title"><?php echo htmlspecialchars($content['title']); ?></h6>
                                                <p class="card-text text-muted">
                                                    <?php echo truncateText($content['description'], 100); ?>
                                                </p>
                                                
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($content['skill_name']); ?></span>
                                                    <span class="badge bg-secondary"><?php echo ucfirst($content['type']); ?></span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-heart-fill text-danger"></i>
                                                        <small class="text-muted"><?php echo $content['likes_count']; ?></small>
                                                    </div>
                                                    <a href="/KnowledgeBee/pages/skill.php?id=<?php echo $content['skill_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        View Content
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-collection display-1 text-muted"></i>
                                <h4 class="mt-3">No content available</h4>
                                <p class="text-muted">Be the first to upload content!</p>
                                <a href="/KnowledgeBee/pages/upload.php" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Upload Content
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?> 