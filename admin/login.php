<?php
/**
 * Admin Login
 * Holistic Prosperity Ministry Payment System
 */

// Initialize session
session_start();

// Include configuration and functions
require_once '../includes/config.php';
require_once '../includes/user-management.php';

// Check if user is already logged in
if (isUserLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
        header("Location: login.php");
        exit;
    }
    
    // Get form data
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password']; // Don't sanitize password
    
    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Please enter both username and password.";
        header("Location: login.php");
        exit;
    }
    
    try {
        // Verify user credentials
        $user = verifyUser($username, $password);
        
        if ($user) {
            // Log in user
            loginUser($user);
            
            // Update last login time
            $stmt = $pdo->prepare("
                UPDATE users
                SET last_login = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([$user['user_id']]);
            
            // Log activity
            logActivity($user['user_id'], "User logged in");
            
            // Redirect to dashboard
            header("Location: index.php");
            exit;
        } else {
            // Invalid credentials
            $_SESSION['error_message'] = "Invalid username or password.";
            header("Location: login.php");
            exit;
        }
    } catch (PDOException $e) {
        // Log error
        error_log("Login Error: " . $e->getMessage());
        
        // Set error message
        $_SESSION['error_message'] = "An error occurred during login. Please try again.";
        header("Location: login.php");
        exit;
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="css/admin.css">
    
    <style>
        body {
            background-color: #f8f9fc;
        }
        .login-container {
            max-width: 450px;
            margin: 100px auto;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo img {
            max-width: 150px;
        }
        .login-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .login-card .card-header {
            background-color: #4B0082;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0;
            padding: 1.5rem;
        }
        .login-card .card-body {
            padding: 2rem;
        }
        .btn-login {
            background-color: #4B0082;
            border-color: #4B0082;
            color: white;
            font-weight: bold;
            padding: 0.75rem;
        }
        .btn-login:hover {
            background-color: #3a006b;
            border-color: #3a006b;
            color: white;
        }
        .form-control:focus {
            border-color: #4B0082;
            box-shadow: 0 0 0 0.25rem rgba(75, 0, 130, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-logo">
                <img src="../images/hpm-logo.svg" alt="<?php echo SITE_NAME; ?> Logo">
            </div>
            
            <div class="card login-card">
                <div class="card-header">
                    <h4 class="mb-0">Admin Login</h4>
                </div>
                <div class="card-body">
                    <!-- Alert messages -->
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    
                    <form method="post" action="login.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me" value="1">
                                <label class="form-check-label" for="remember_me">
                                    Remember me
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4 text-muted">
                <small>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</small>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
