<?php
require_once '../includes/functions.php';

$page_title = 'Login';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('pages/home.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $user = getUserByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'banned') {
                $error = 'Your account has been banned. Please contact support.';
            } else {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                $_SESSION['is_verified'] = $user['is_verified'];
                
                // Award daily login coins (if not already awarded today)
                $today = date('Y-m-d');
                $lastLogin = isset($_SESSION['last_login_date']) ? $_SESSION['last_login_date'] : '';
                
                if ($lastLogin !== $today) {
                    addCoins($user['id'], COINS_DAILY_LOGIN, "Daily login reward");
                    $_SESSION['last_login_date'] = $today;
                }
                
                setFlashMessage('success', 'Welcome back, ' . $user['username'] . '!');
                redirect('pages/home.php');
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

include '../templates/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-bee display-1 text-warning"></i>
                        <h2 class="mt-3">Welcome Back</h2>
                        <p class="text-muted">Sign in to continue learning and sharing</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-custom" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <?php echo csrfInput(); ?>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-2">
                            <a href="forgot-password.php" class="text-decoration-none">Forgot your password?</a>
                        </p>
                        <p class="mb-0">
                            Don't have an account? 
                            <a href="signup.php" class="text-decoration-none fw-bold">Sign up here</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Demo Accounts Info -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle"></i> Demo Accounts
                    </h6>
                    <small class="text-muted">
                        <strong>Admin:</strong> admin / admin123<br>
                        <strong>User:</strong> john_doe / password<br>
                        <strong>User:</strong> jane_smith / password
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?> 