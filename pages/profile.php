<?php
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Profile';

$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Get user's badges
$badges = $db->fetchAll("
    SELECT b.*, s.name as skill_name 
    FROM badges b 
    JOIN skills s ON b.skill_id = s.id 
    WHERE b.user_id = ? 
    ORDER BY b.earned_date DESC
", [$userId]);

// Get user's content
$userContent = getUserContent($userId);

// Get coin transactions
$transactions = $db->fetchAll("
    SELECT * FROM coin_transactions 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
", [$userId]);

// Get user stats
$totalContent = $db->count('content', ['user_id' => $userId]);
$approvedContent = $db->count('content', ['user_id' => $userId, 'status' => 'approved']);
$totalLikes = $db->fetchOne("
    SELECT SUM(likes) as total FROM content 
    WHERE user_id = ? AND status = 'approved'
", [$userId]);
$totalLikes = $totalLikes ? $totalLikes['total'] : 0;

include '../templates/header.php';
?>

<div class="container">
    <!-- Profile Header -->
    <div class="profile-header mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="../assets/uploads/<?php echo $user['profile_pic']; ?>" 
                         class="rounded-circle mb-3" width="120" height="120" 
                         onerror="this.src='../assets/uploads/default.jpg'">
                </div>
                <div class="col-md-6">
                    <h2 class="mb-2"><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p class="mb-2"><?php echo htmlspecialchars($user['bio'] ?? 'No bio yet.'); ?></p>
                    <div class="d-flex align-items-center">
                        <span class="coin-badge fs-5 me-3">
                            <?php echo $user['coins']; ?> Buzz Coins
                        </span>
                        <?php if ($user['is_verified']): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Verified
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="edit-profile.php" class="btn btn-light">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h3 text-primary"><?php echo $user['coins']; ?></div>
                    <small class="text-muted">Buzz Coins</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h3 text-success"><?php echo count($badges); ?></div>
                    <small class="text-muted">Badges Earned</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h3 text-info"><?php echo $approvedContent; ?></div>
                    <small class="text-muted">Approved Content</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h3 text-warning"><?php echo $totalLikes; ?></div>
                    <small class="text-muted">Total Likes</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Skills Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-gear"></i> Skills
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">I Can Teach</h6>
                            <?php if ($user['skills_teach']): ?>
                                <?php foreach (explode(',', $user['skills_teach']) as $skill): ?>
                                    <span class="badge bg-primary me-1 mb-1"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No skills listed yet.</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">I Want to Learn</h6>
                            <?php if ($user['skills_learn']): ?>
                                <?php foreach (explode(',', $user['skills_learn']) as $skill): ?>
                                    <span class="badge bg-success me-1 mb-1"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No skills listed yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Content -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-collection"></i> My Content
                    </h5>
                    <a href="upload.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Upload New
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($userContent): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Skill</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Coins</th>
                                        <th>Likes</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userContent as $content): ?>
                                        <tr>
                                            <td>
                                                <a href="view-content.php?id=<?php echo $content['id']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($content['title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($content['skill_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $content['type'] === 'video' ? 'danger' : ($content['type'] === 'quiz' ? 'warning' : 'primary'); ?>">
                                                    <?php echo ucfirst($content['type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $content['status'] === 'approved' ? 'success' : ($content['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                    <?php echo ucfirst($content['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $content['coin_cost']; ?> 🐝</td>
                                            <td><?php echo $content['likes']; ?></td>
                                            <td><?php echo formatDate($content['upload_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h5 class="mt-3">No content yet</h5>
                            <p class="text-muted">Start sharing your knowledge to earn Buzz Coins!</p>
                            <a href="upload.php" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Upload Your First Content
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-currency-exchange"></i> Recent Coin Transactions
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($transactions): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?php echo $transaction['transaction_type'] === 'earned' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($transaction['transaction_type']); ?>
                                                </span>
                                            </td>
                                            <td class="<?php echo $transaction['transaction_type'] === 'earned' ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $transaction['transaction_type'] === 'earned' ? '+' : '-'; ?><?php echo $transaction['amount']; ?> 🐝
                                            </td>
                                            <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                            <td><?php echo formatDateTime($transaction['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="transactions.php" class="btn btn-outline-primary btn-sm">View All Transactions</a>
                    <?php else: ?>
                        <p class="text-muted text-center">No transactions yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Badges -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-award"></i> Badges Earned
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($badges): ?>
                        <?php foreach ($badges as $badge): ?>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-award-fill text-warning me-3 fs-4"></i>
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($badge['badge_title']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($badge['skill_name']); ?></small>
                                    <br>
                                    <small class="text-muted">Earned <?php echo formatDate($badge['earned_date']); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="bi bi-award text-muted fs-1"></i>
                            <p class="text-muted mt-2">No badges yet</p>
                            <small class="text-muted">Upload quality content to earn badges!</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-person"></i> Account Info
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Member since</small>
                        <div><?php echo formatDate($user['created_at']); ?></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Status</small>
                        <div>
                            <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($user['is_verified']): ?>
                        <div class="mb-2">
                            <small class="text-muted">Verification</small>
                            <div>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Verified User
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="upload.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-upload"></i> Upload Content
                        </a>
                        <a href="explore.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-compass"></i> Explore Skills
                        </a>
                        <a href="leaderboard.php" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-trophy"></i> View Leaderboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?> 