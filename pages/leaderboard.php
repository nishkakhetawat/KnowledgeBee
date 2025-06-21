<?php
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Leaderboard';

// Get filter parameters
$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'global';
$skillId = isset($_GET['skill']) ? (int)$_GET['skill'] : 0;

// Get all skills for filter dropdown
$skills = getAllSkills();

// Get leaderboard data based on type
if ($type === 'global') {
    $leaderboard = getGlobalLeaderboard(50);
    $title = 'Global Leaderboard';
    $subtitle = 'Top contributors by Buzz Coins';
} else {
    $skill = getSkillById($skillId);
    if ($skill) {
        $leaderboard = getSkillLeaderboard($skillId, 50);
        $title = $skill['name'] . ' Leaderboard';
        $subtitle = 'Top contributors in ' . $skill['name'];
    } else {
        $leaderboard = getGlobalLeaderboard(50);
        $title = 'Global Leaderboard';
        $subtitle = 'Top contributors by Buzz Coins';
    }
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
                        <i class="bi bi-trophy"></i> <?php echo $title; ?>
                    </h1>
                    <p class="mb-0"><?php echo $subtitle; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="btn-group w-100" role="group">
                <a href="?type=global" class="btn btn-outline-primary <?php echo $type === 'global' ? 'active' : ''; ?>">
                    <i class="bi bi-globe"></i> Global
                </a>
                <a href="?type=skill" class="btn btn-outline-primary <?php echo $type === 'skill' ? 'active' : ''; ?>">
                    <i class="bi bi-award"></i> By Skill
                </a>
            </div>
        </div>
        
        <?php if ($type === 'skill'): ?>
        <div class="col-md-6">
            <form method="GET" action="" class="d-flex">
                <input type="hidden" name="type" value="skill">
                <select name="skill" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">Select Skill</option>
                    <optgroup label="Tech Skills">
                        <?php foreach ($skills as $skill): ?>
                            <?php if ($skill['category'] === 'tech'): ?>
                                <option value="<?php echo $skill['id']; ?>" 
                                        <?php echo $skillId == $skill['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($skill['name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Non-Tech Skills">
                        <?php foreach ($skills as $skill): ?>
                            <?php if ($skill['category'] === 'non-tech'): ?>
                                <option value="<?php echo $skill['id']; ?>"
                                        <?php echo $skillId == $skill['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($skill['name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Leaderboard -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ol"></i> Rankings
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($leaderboard): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="60">Rank</th>
                                        <th>User</th>
                                        <?php if ($type === 'global'): ?>
                                            <th>Buzz Coins</th>
                                            <th>Badges</th>
                                            <th>Content</th>
                                        <?php else: ?>
                                            <th>Content Count</th>
                                            <th>Total Likes</th>
                                            <th>Skill Level</th>
                                        <?php endif; ?>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leaderboard as $index => $user): ?>
                                        <tr class="<?php echo $index < 3 ? 'table-warning' : ''; ?>">
                                            <td>
                                                <?php if ($index === 0): ?>
                                                    <span class="badge bg-warning fs-6">🥇</span>
                                                <?php elseif ($index === 1): ?>
                                                    <span class="badge bg-secondary fs-6">🥈</span>
                                                <?php elseif ($index === 2): ?>
                                                    <span class="badge bg-bronze fs-6">🥉</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">#<?php echo $index + 1; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="/KnowledgeBee/assets/uploads/<?php echo $user['profile_pic']; ?>" 
                                                         class="rounded-circle me-3" width="40" height="40" 
                                                         onerror="this.src='/KnowledgeBee/assets/uploads/default.jpg'">
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                                        <?php if ($user['is_verified'] ?? false): ?>
                                                            <small class="text-success">
                                                                <i class="bi bi-check-circle"></i> Verified
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <?php if ($type === 'global'): ?>
                                                <td>
                                                    <span class="coin-badge fs-6">
                                                        <?php echo number_format($user['coins']); ?> 🐝
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo $user['badge_count']; ?> Badges
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $contentCount = $db->count('content', ['user_id' => $user['id'], 'status' => 'approved']);
                                                    ?>
                                                    <span class="text-muted"><?php echo $contentCount; ?> items</span>
                                                </td>
                                            <?php else: ?>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo $user['content_count']; ?> Content
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-success">
                                                        <i class="bi bi-heart-fill"></i> <?php echo $user['total_likes'] ?? 0; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badge = $db->fetchOne("SELECT * FROM badges WHERE user_id = ? AND skill_id = ?", [$user['id'], $skillId]);
                                                    if ($badge): ?>
                                                        <span class="badge bg-warning">
                                                            <i class="bi bi-award"></i> <?php echo htmlspecialchars($badge['badge_title']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Learning</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                            <td>
                                                <a href="/KnowledgeBee/pages/profile.php?user=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-person"></i> View Profile
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-trophy display-1 text-muted"></i>
                            <h4 class="mt-3">No rankings yet</h4>
                            <p class="text-muted">Start contributing to appear on the leaderboard!</p>
                            <a href="/KnowledgeBee/pages/upload.php" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Upload Content
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h3 text-primary">
                        <?php
                        $topUser = $leaderboard[0] ?? null;
                        echo $topUser ? number_format($topUser['coins'] ?? 0) : '0';
                        ?>
                    </div>
                    <small class="text-muted">Top User Coins</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h3 text-success">
                        <?php
                        $totalUsers = $db->count('users', ['status' => 'active']);
                        echo $totalUsers;
                        ?>
                    </div>
                    <small class="text-muted">Active Users</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h3 text-info">
                        <?php
                        $totalBadges = $db->count('badges');
                        echo $totalBadges;
                        ?>
                    </div>
                    <small class="text-muted">Badges Awarded</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h3 text-warning">
                        <?php
                        $totalContent = $db->count('content', ['status' => 'approved']);
                        echo $totalContent;
                        ?>
                    </div>
                    <small class="text-muted">Content Items</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Your Ranking -->
    <?php if ($leaderboard): ?>
        <?php
        $userRank = null;
        foreach ($leaderboard as $index => $user) {
            if ($user['id'] == $_SESSION['user_id']) {
                $userRank = $index + 1;
                break;
            }
        }
        ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-person-badge"></i> Your Ranking
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($userRank): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">You are ranked #<?php echo $userRank; ?></h5>
                                    <p class="text-muted mb-0">
                                        <?php if ($type === 'global'): ?>
                                            Keep earning coins to climb higher!
                                        <?php else: ?>
                                            Keep contributing to <?php echo htmlspecialchars($skill['name']); ?> to improve your ranking!
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <?php if ($type === 'global'): ?>
                                        <div class="h4 text-primary">
                                            <?php echo number_format(getUserCoins($_SESSION['user_id'])); ?> 🐝
                                        </div>
                                        <small class="text-muted">Your Buzz Coins</small>
                                    <?php else: ?>
                                        <div class="h4 text-info">
                                            <?php
                                            $userContentCount = $db->count('content', ['user_id' => $_SESSION['user_id'], 'skill_id' => $skillId, 'status' => 'approved']);
                                            echo $userContentCount;
                                            ?>
                                        </div>
                                        <small class="text-muted">Your Content</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <p class="mb-2">You're not on this leaderboard yet.</p>
                                <a href="/KnowledgeBee/pages/upload.php" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Start Contributing
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.bg-bronze {
    background-color: #cd7f32 !important;
    color: white !important;
}
</style>

<?php include '../templates/footer.php'; ?> 