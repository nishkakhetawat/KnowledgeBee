<?php
require_once 'database.php';

// Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: auth/login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: pages/home.php');
        exit();
    }
}

// User Functions
function getUserById($userId) {
    global $db;
    return $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
}

function getUserByUsername($username) {
    global $db;
    return $db->fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
}

function updateUserProfile($userId, $data) {
    global $db;
    $sql = "UPDATE users SET bio = ?, skills_teach = ?, skills_learn = ? WHERE id = ?";
    return $db->execute($sql, [$data['bio'], $data['skills_teach'], $data['skills_learn'], $userId]);
}

// Buzz Coin Functions
function addCoins($userId, $amount, $description, $contentId = null) {
    global $db;
    
    // Update user coins
    $sql = "UPDATE users SET coins = coins + ? WHERE id = ?";
    $db->execute($sql, [$amount, $userId]);
    
    // Log transaction
    $sql = "INSERT INTO coin_transactions (user_id, transaction_type, amount, description, related_content_id) VALUES (?, 'earned', ?, ?, ?)";
    $db->insert($sql, [$userId, $amount, $description, $contentId]);
    
    // Add notification
    addNotification($userId, "You earned $amount Buzz Coins: $description", 'coin_earned');
}

function spendCoins($userId, $amount, $description, $contentId = null) {
    global $db;
    
    // Check if user has enough coins
    $user = getUserById($userId);
    if ($user['coins'] < $amount) {
        return false;
    }
    
    // Update user coins
    $sql = "UPDATE users SET coins = coins - ? WHERE id = ?";
    $db->execute($sql, [$amount, $userId]);
    
    // Log transaction
    $sql = "INSERT INTO coin_transactions (user_id, transaction_type, amount, description, related_content_id) VALUES (?, 'spent', ?, ?, ?)";
    $db->insert($sql, [$userId, $amount, $description, $contentId]);
    
    return true;
}

function getUserCoins($userId) {
    global $db;
    $user = $db->fetchOne("SELECT coins FROM users WHERE id = ?", [$userId]);
    return $user ? $user['coins'] : 0;
}

// Content Functions
function getContentById($contentId) {
    global $db;
    $sql = "SELECT c.*, u.username, u.profile_pic, s.name as skill_name 
            FROM content c 
            JOIN users u ON c.user_id = u.id 
            JOIN skills s ON c.skill_id = s.id 
            WHERE c.id = ?";
    return $db->fetchOne($sql, [$contentId]);
}

function getContentBySkill($skillId, $type = null, $sort = 'newest', $page = 1) {
    global $db;
    
    $where = ["c.skill_id = ?", "c.status = 'approved'"];
    $params = [$skillId];
    
    if ($type) {
        $where[] = "c.type = ?";
        $params[] = $type;
    }
    
    $sql = "SELECT c.*, u.username, u.profile_pic, s.name as skill_name,
                   (SELECT COUNT(*) FROM upvotes WHERE content_id = c.id AND vote_type = 'like') as likes_count,
                   (SELECT COUNT(*) FROM upvotes WHERE content_id = c.id AND vote_type = 'dislike') as dislikes_count
            FROM content c 
            JOIN users u ON c.user_id = u.id 
            JOIN skills s ON c.skill_id = s.id 
            WHERE " . implode(' AND ', $where);
    
    switch ($sort) {
        case 'rating':
            $sql .= " ORDER BY (likes_count - dislikes_count) DESC";
            break;
        case 'popular':
            $sql .= " ORDER BY likes_count DESC";
            break;
        default:
            $sql .= " ORDER BY c.upload_date DESC";
    }
    
    return $db->paginate($sql, $params, $page, 12);
}

function getUserContent($userId, $skillId = null) {
    global $db;
    
    $where = ["c.user_id = ?"];
    $params = [$userId];
    
    if ($skillId) {
        $where[] = "c.skill_id = ?";
        $params[] = $skillId;
    }
    
    $sql = "SELECT c.*, s.name as skill_name 
            FROM content c 
            JOIN skills s ON c.skill_id = s.id 
            WHERE " . implode(' AND ', $where) . " 
            ORDER BY c.upload_date DESC";
    
    return $db->fetchAll($sql, $params);
}

function createContent($data) {
    global $db;
    
    $sql = "INSERT INTO content (user_id, skill_id, type, title, description, content_data, coin_cost) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $contentId = $db->insert($sql, [
        $data['user_id'],
        $data['skill_id'],
        $data['type'],
        $data['title'],
        $data['description'],
        $data['content_data'],
        $data['coin_cost']
    ]);
    
    if ($contentId) {
        // Award coins for upload
        addCoins($data['user_id'], COINS_UPLOAD_REWARD, "Content upload reward", $contentId);
        
        // Check if user is verified (auto-approve)
        $user = getUserById($data['user_id']);
        if ($user['is_verified']) {
            $db->execute("UPDATE content SET status = 'approved' WHERE id = ?", [$contentId]);
        }
        
        return $contentId;
    }
    
    return false;
}

// Skill Functions
function getAllSkills($category = null) {
    global $db;
    
    $sql = "SELECT s.*, 
                   (SELECT COUNT(*) FROM content WHERE skill_id = s.id AND status = 'approved') as content_count
            FROM skills s 
            WHERE s.is_banned = 0";
    
    $params = [];
    if ($category) {
        $sql .= " AND s.category = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY s.name";
    return $db->fetchAll($sql, $params);
}

function getSkillById($skillId) {
    global $db;
    return $db->fetchOne("SELECT * FROM skills WHERE id = ?", [$skillId]);
}

// Voting Functions
function voteContent($userId, $contentId, $voteType) {
    global $db;
    
    // Check if user already voted
    $existing = $db->fetchOne("SELECT * FROM upvotes WHERE user_id = ? AND content_id = ?", [$userId, $contentId]);
    
    if ($existing) {
        // Update existing vote
        $sql = "UPDATE upvotes SET vote_type = ? WHERE user_id = ? AND content_id = ?";
        $db->execute($sql, [$voteType, $userId, $contentId]);
    } else {
        // Create new vote
        $sql = "INSERT INTO upvotes (user_id, content_id, vote_type) VALUES (?, ?, ?)";
        $db->insert($sql, [$userId, $contentId, $voteType]);
    }
    
    // Update content likes/dislikes count
    $likes = $db->count('upvotes', ['content_id' => $contentId, 'vote_type' => 'like']);
    $dislikes = $db->count('upvotes', ['content_id' => $contentId, 'vote_type' => 'dislike']);
    
    $sql = "UPDATE content SET likes = ?, dislikes = ? WHERE id = ?";
    $db->execute($sql, [$likes, $dislikes, $contentId]);
    
    // Award coins for upvote
    if ($voteType === 'like') {
        $content = getContentById($contentId);
        addCoins($content['user_id'], COINS_UPVOTE_REWARD, "Content upvoted", $contentId);
    }
    
    return true;
}

// Badge Functions
function checkAndAwardBadges($userId, $skillId) {
    global $db;
    
    // Get user's content count for this skill
    $contentCount = $db->count('content', ['user_id' => $userId, 'skill_id' => $skillId, 'status' => 'approved']);
    
    // Get total upvotes for user's content in this skill
    $sql = "SELECT SUM(c.likes) as total_likes 
            FROM content c 
            WHERE c.user_id = ? AND c.skill_id = ? AND c.status = 'approved'";
    $result = $db->fetchOne($sql, [$userId, $skillId]);
    $totalLikes = $result ? $result['total_likes'] : 0;
    
    // Check if user already has a badge for this skill
    $existingBadge = $db->fetchOne("SELECT * FROM badges WHERE user_id = ? AND skill_id = ?", [$userId, $skillId]);
    
    if (!$existingBadge && $contentCount >= BADGE_UPLOAD_MIN && $totalLikes >= BADGE_UPVOTES_MIN) {
        // Award badge
        $skill = getSkillById($skillId);
        $badgeTitle = $skill['name'] . " Mentor";
        
        $sql = "INSERT INTO badges (user_id, skill_id, badge_title, badge_type) VALUES (?, ?, ?, 'mentor')";
        $badgeId = $db->insert($sql, [$userId, $skillId, $badgeTitle]);
        
        if ($badgeId) {
            addCoins($userId, COINS_BADGE_REWARD, "Badge earned: $badgeTitle");
            addNotification($userId, "Congratulations! You earned the \"$badgeTitle\" badge!", 'badge');
        }
    }
}

// Notification Functions
function addNotification($userId, $message, $type, $relatedId = null) {
    global $db;
    $sql = "INSERT INTO notifications (user_id, message, type, related_id) VALUES (?, ?, ?, ?)";
    return $db->insert($sql, [$userId, $message, $type, $relatedId]);
}

function getUserNotifications($userId, $limit = 10) {
    global $db;
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    return $db->fetchAll($sql, [$userId, $limit]);
}

function getUnreadNotificationCount($userId) {
    global $db;
    return $db->count('notifications', ['user_id' => $userId, 'is_read' => 0]);
}

function markNotificationAsRead($notificationId) {
    global $db;
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    return $db->execute($sql, [$notificationId]);
}

// Search Functions
function searchContent($query, $type = null, $skillId = null) {
    global $db;
    
    $where = ["c.status = 'approved'"];
    $params = [];
    
    if ($query) {
        $where[] = "(c.title LIKE ? OR c.description LIKE ?)";
        $params[] = "%$query%";
        $params[] = "%$query%";
    }
    
    if ($type) {
        $where[] = "c.type = ?";
        $params[] = $type;
    }
    
    if ($skillId) {
        $where[] = "c.skill_id = ?";
        $params[] = $skillId;
    }
    
    $sql = "SELECT c.*, u.username, u.profile_pic, s.name as skill_name,
                   (SELECT COUNT(*) FROM upvotes WHERE content_id = c.id AND vote_type = 'like') as likes_count
            FROM content c 
            JOIN users u ON c.user_id = u.id 
            JOIN skills s ON c.skill_id = s.id 
            WHERE " . implode(' AND ', $where) . " 
            ORDER BY likes_count DESC, c.upload_date DESC";
    
    return $db->fetchAll($sql, $params);
}

// Leaderboard Functions
function getGlobalLeaderboard($limit = 10) {
    global $db;
    $sql = "SELECT u.id, u.username, u.profile_pic, u.coins,
                   (SELECT COUNT(*) FROM badges WHERE user_id = u.id) as badge_count
            FROM users u 
            WHERE u.status = 'active' 
            ORDER BY u.coins DESC, badge_count DESC 
            LIMIT ?";
    return $db->fetchAll($sql, [$limit]);
}

function getSkillLeaderboard($skillId, $limit = 10) {
    global $db;
    $sql = "SELECT u.id, u.username, u.profile_pic,
                   (SELECT COUNT(*) FROM content WHERE user_id = u.id AND skill_id = ? AND status = 'approved') as content_count,
                   (SELECT SUM(likes) FROM content WHERE user_id = u.id AND skill_id = ? AND status = 'approved') as total_likes
            FROM users u 
            WHERE u.status = 'active' 
            ORDER BY total_likes DESC, content_count DESC 
            LIMIT ?";
    return $db->fetchAll($sql, [$skillId, $skillId, $limit]);
}

// Utility Functions
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatDateTime($date) {
    return date('M j, Y g:i A', strtotime($date));
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateSlug($text) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?> 