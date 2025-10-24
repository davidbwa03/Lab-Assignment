<?php
session_start();
require_once 'config/db.php';
require_once 'classes/User.php';

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: products.php");
    exit;
}

$database = new DatabaseConfig();
$db = $database->getConnection();
$user = new User($db);

$error = '';

if($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if(empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        if($user->login($username, $password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['email'] = $user->email;
            $_SESSION['phone'] = $user->phone;
            
            // Check if 2FA is enabled
            if(!empty($user->two_factor_secret)) {
                $_SESSION['require_2fa'] = true;
                $_SESSION['temp_user_id'] = $user->id;
                header("Location: verify_2fa.php");
            } else {
                header("Location: products.php");
            }
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Business Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
        }
        .card {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 15px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Business Manager</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="register.php">Register</a>
                <a class="nav-link" href="index.php">Home</a>
            </div>
        </div>
    </nav>

    <div class="container login-container">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h2 class="mb-0">Welcome Back</h2>
                        <p class="mb-0 mt-2">Sign in to your account</p>
                    </div>
                    <div class="card-body p-5">
                        <?php if($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-4">
                                <label for="username" class="form-label">Username or Email</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                           placeholder="Enter your username or email" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter your password" required>
                                </div>
                            </div>
                            
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-3 mb-4">
                                <strong>Sign In</strong>
                            </button>
                            
                            <div class="text-center">
                                <a href="#" class="text-decoration-none">Forgot your password?</a>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-0">Don't have an account? 
                                <a href="register.php" class="text-decoration-none fw-bold">Create one here</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Demo Credentials -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Demo Credentials:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Admin Account:</strong></p>
                                <p class="mb-1">Username: <code>admin</code></p>
                                <p class="mb-1">Password: <code>admin123</code></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Test Account:</strong></p>
                                <p class="mb-1">Username: <code>testuser</code></p>
                                <p class="mb-1">Password: <code>test123</code></p>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <a href="setup.php" class="text-decoration-none">Run setup first if these accounts don't work</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">Business Management System &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>