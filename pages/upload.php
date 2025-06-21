<?php
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Upload Content';

$error = '';
$success = '';

// Get all skills for dropdown
$skills = getAllSkills();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    $skillId = (int)$_POST['skill_id'];
    $type = sanitizeInput($_POST['type']);
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $coinCost = (int)$_POST['coin_cost'];
    $contentData = $_POST['content_data'];
    
    // Validation
    if (empty($skillId) || empty($type) || empty($title) || empty($description)) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($type, ['video', 'blog', 'quiz'])) {
        $error = 'Invalid content type.';
    } elseif (strlen($title) < 5 || strlen($title) > 255) {
        $error = 'Title must be between 5 and 255 characters.';
    } elseif ($coinCost < 0 || $coinCost > 50) {
        $error = 'Coin cost must be between 0 and 50.';
    } else {
        // Check if user has reached upload limit (for new users)
        $userContentCount = $db->count('content', ['user_id' => $_SESSION['user_id']]);
        $user = getUserById($_SESSION['user_id']);
        
        if (!$user['is_verified'] && $userContentCount >= NEW_USER_UPLOAD_LIMIT) {
            $error = 'New users can only upload ' . NEW_USER_UPLOAD_LIMIT . ' pieces of content. Get verified to upload more!';
        } else {
            // Check for duplicate title
            if ($db->exists('content', ['title' => $title, 'user_id' => $_SESSION['user_id']])) {
                $error = 'You already have content with this title. Please choose a different title.';
            } else {
                // Prepare content data based on type
                $finalContentData = $contentData;
                
                if ($type === 'video') {
                    // Validate YouTube URL
                    if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/', $contentData)) {
                        $error = 'Please enter a valid YouTube URL.';
                    } else {
                        // Convert to embed URL
                        $videoId = '';
                        if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $contentData, $matches)) {
                            $videoId = $matches[1];
                        } elseif (preg_match('/youtu\.be\/([^?]+)/', $contentData, $matches)) {
                            $videoId = $matches[1];
                        }
                        
                        if ($videoId) {
                            $finalContentData = "https://www.youtube.com/embed/$videoId";
                        } else {
                            $error = 'Could not extract video ID from URL.';
                        }
                    }
                } elseif ($type === 'quiz') {
                    // Validate quiz data
                    $quizData = json_decode($contentData, true);
                    if (!$quizData || !isset($quizData['questions']) || empty($quizData['questions'])) {
                        $error = 'Please add at least one quiz question.';
                    } else {
                        $finalContentData = $contentData; // Keep as JSON
                    }
                }
                
                if (!$error) {
                    // Create content
                    $contentData = [
                        'user_id' => $_SESSION['user_id'],
                        'skill_id' => $skillId,
                        'type' => $type,
                        'title' => $title,
                        'description' => $description,
                        'content_data' => $finalContentData,
                        'coin_cost' => $coinCost
                    ];
                    
                    $contentId = createContent($contentData);
                    
                    if ($contentId) {
                        // If quiz, create quiz questions
                        if ($type === 'quiz') {
                            $quizData = json_decode($finalContentData, true);
                            foreach ($quizData['questions'] as $question) {
                                $sql = "INSERT INTO quizzes (content_id, question, option_a, option_b, option_c, option_d, correct_option, explanation) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                                $db->insert($sql, [
                                    $contentId,
                                    $question['question'],
                                    $question['options']['a'],
                                    $question['options']['b'],
                                    $question['options']['c'],
                                    $question['options']['d'],
                                    $question['correct'],
                                    $question['explanation'] ?? ''
                                ]);
                            }
                        }
                        
                        $success = 'Content uploaded successfully! You earned ' . COINS_UPLOAD_REWARD . ' Buzz Coins.';
                        
                        // Check for badge eligibility
                        checkAndAwardBadges($_SESSION['user_id'], $skillId);
                        
                        // Clear form
                        $_POST = [];
                    } else {
                        $error = 'An error occurred while uploading content. Please try again.';
                    }
                }
            }
        }
    }
}

include '../templates/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-upload"></i> Upload Content
                    </h4>
                    <small class="text-muted">Share your knowledge and earn Buzz Coins</small>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-custom" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-custom" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="uploadForm">
                        <?php echo csrfInput(); ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="skill_id" class="form-label">Skill Category *</label>
                                    <select class="form-select" id="skill_id" name="skill_id" required>
                                        <option value="">Select a skill</option>
                                        <optgroup label="Tech Skills">
                                            <?php foreach ($skills as $skill): ?>
                                                <?php if ($skill['category'] === 'tech'): ?>
                                                    <option value="<?php echo $skill['id']; ?>" 
                                                            <?php echo (isset($_POST['skill_id']) && $_POST['skill_id'] == $skill['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($skill['name']); ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <optgroup label="Non-Tech Skills">
                                            <?php foreach ($skills as $skill): ?>
                                                <?php if ($skill['category'] === 'non-tech'): ?>
                                                    <option value="<?php echo $skill['id']; ?>"
                                                            <?php echo (isset($_POST['skill_id']) && $_POST['skill_id'] == $skill['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($skill['name']); ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Content Type *</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="">Select type</option>
                                        <option value="video" <?php echo (isset($_POST['type']) && $_POST['type'] === 'video') ? 'selected' : ''; ?>>Video Tutorial</option>
                                        <option value="blog" <?php echo (isset($_POST['type']) && $_POST['type'] === 'blog') ? 'selected' : ''; ?>>Blog Post</option>
                                        <option value="quiz" <?php echo (isset($_POST['type']) && $_POST['type'] === 'quiz') ? 'selected' : ''; ?>>Quiz</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                   required minlength="5" maxlength="255">
                            <div class="form-text">5-255 characters</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="coin_cost" class="form-label">Coin Cost</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="coin_cost" name="coin_cost" 
                                       value="<?php echo isset($_POST['coin_cost']) ? (int)$_POST['coin_cost'] : 0; ?>" 
                                       min="0" max="50">
                                <span class="input-group-text">🐝</span>
                            </div>
                            <div class="form-text">How many coins users need to spend to access this content (0-50)</div>
                        </div>
                        
                        <input type="hidden" id="content_data" name="content_data">
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-upload"></i> Upload Content
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Upload Guidelines -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Upload Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li><i class="bi bi-check-circle text-success"></i> Provide clear, helpful content</li>
                        <li><i class="bi bi-check-circle text-success"></i> Use descriptive titles and descriptions</li>
                        <li><i class="bi bi-check-circle text-success"></i> Set reasonable coin costs (0-50)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Earn <?php echo COINS_UPLOAD_REWARD; ?> coins per upload</li>
                        <li><i class="bi bi-check-circle text-success"></i> Get upvotes to earn more coins</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let questionCount = 1;

function showContentFields() {
    const type = document.getElementById('type').value;
    
    // Hide all content type fields
    document.querySelectorAll('.content-type-fields').forEach(field => {
        field.style.display = 'none';
    });
    
    // Show relevant fields
    if (type === 'video') {
        document.getElementById('videoFields').style.display = 'block';
    } else if (type === 'blog') {
        document.getElementById('blogFields').style.display = 'block';
    } else if (type === 'quiz') {
        document.getElementById('quizFields').style.display = 'block';
    }
}

function addQuizQuestion() {
    const container = document.getElementById('quizQuestions');
    const newQuestion = document.createElement('div');
    newQuestion.className = 'quiz-question mb-3 p-3 border rounded';
    newQuestion.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label">Question ${questionCount + 1}</label>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeQuestion(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <div class="mb-2">
            <input type="text" class="form-control" name="quiz_questions[${questionCount}][question]" placeholder="Enter your question">
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Option A</label>
                <input type="text" class="form-control" name="quiz_questions[${questionCount}][options][a]" placeholder="Option A">
            </div>
            <div class="col-md-6">
                <label class="form-label">Option B</label>
                <input type="text" class="form-control" name="quiz_questions[${questionCount}][options][b]" placeholder="Option B">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <label class="form-label">Option C</label>
                <input type="text" class="form-control" name="quiz_questions[${questionCount}][options][c]" placeholder="Option C">
            </div>
            <div class="col-md-6">
                <label class="form-label">Option D</label>
                <input type="text" class="form-control" name="quiz_questions[${questionCount}][options][d]" placeholder="Option D">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <label class="form-label">Correct Answer</label>
                <select class="form-select" name="quiz_questions[${questionCount}][correct]">
                    <option value="a">A</option>
                    <option value="b">B</option>
                    <option value="c">C</option>
                    <option value="d">D</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Explanation (Optional)</label>
                <input type="text" class="form-control" name="quiz_questions[${questionCount}][explanation]" placeholder="Why is this correct?">
            </div>
        </div>
    `;
    container.appendChild(newQuestion);
    questionCount++;
}

function removeQuestion(button) {
    button.closest('.quiz-question').remove();
}

document.getElementById('type').addEventListener('change', showContentFields);

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const type = document.getElementById('type').value;
    let contentData = '';
    
    if (type === 'video') {
        contentData = document.getElementById('video_url').value;
    } else if (type === 'blog') {
        contentData = document.getElementById('blog_content').value;
    } else if (type === 'quiz') {
        const questions = [];
        document.querySelectorAll('.quiz-question').forEach((questionDiv, index) => {
            const question = {
                question: questionDiv.querySelector('input[name*="[question]"]').value,
                options: {
                    a: questionDiv.querySelector('input[name*="[options][a]"]').value,
                    b: questionDiv.querySelector('input[name*="[options][b]"]').value,
                    c: questionDiv.querySelector('input[name*="[options][c]"]').value,
                    d: questionDiv.querySelector('input[name*="[options][d]"]').value
                },
                correct: questionDiv.querySelector('select[name*="[correct]"]').value,
                explanation: questionDiv.querySelector('input[name*="[explanation]"]').value
            };
            questions.push(question);
        });
        contentData = JSON.stringify({questions: questions});
    }
    
    document.getElementById('content_data').value = contentData;
});
</script>

<?php include '../templates/footer.php'; ?> 