<?php
session_start();

// Redirect if not coming from login with 2FA requirement
if (!isset($_SESSION['require_2fa']) || !isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';
require_once 'classes/User.php';

$database = new DatabaseConfig();
$db = $database->getConnection();
$user = new User($db);

$error = '';

if($_POST) {
    $code = $_POST['code'] ?? '';
    
    if(empty($code)) {
        $error = "Please enter the verification code";
    } else {
        // Get user data
        $user_data = $user->getUserById($_SESSION['temp_user_id']);
        if($user_data) {
            $user->id = $user_data['id'];
            $user->two_factor_secret = $user_data['two_factor_secret'];
            
            if($user->verify2FA($code)) {
                // 2FA successful, complete login
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['username'] = $user_data['username'];
                $_SESSION['email'] = $user_data['email'];
                $_SESSION['phone'] = $user_data['phone'];
                
                // Clear 2FA session vars
                unset($_SESSION['require_2fa']);
                unset($_SESSION['temp_user_id']);
                
                header("Location: products.php");
                exit;
            } else {
                $error = "Invalid verification code. Please try again.";
            }
        } else {
            $error = "User not found. Please login again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - Business Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .auth-card {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card auth-card">
                        <div class="card-header bg-primary text-white text-center py-4">
                            <h3 class="mb-0"><i class="bi bi-shield-check"></i> Two-Factor Authentication</h3>
                        </div>
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="bi bi-phone display-4 text-primary"></i>
                                <p class="mt-3">Please enter the verification code from your authenticator app</p>
                            </div>

                            <?php if($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="mb-4">
                                    <label for="code" class="form-label">Verification Code</label>
                                    <input type="text" class="form-control form-control-lg text-center" 
                                           id="code" name="code" placeholder="000000" 
                                           maxlength="6" required autofocus>
                                    <div class="form-text text-center">
                                        Enter the 6-digit code from your authenticator app
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg w-100 py-3">
                                    <i class="bi bi-check-lg"></i> Verify & Continue
                                </button>
                            </form>

                            <div class="text-center mt-4">
                                <p class="text-muted">
                                    <small>
                                        Don't have access to your authenticator?<br>
                                        <a href="login.php" class="text-decoration-none">Try another way to sign in</a>
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Demo Note -->
                    <div class="card mt-4">
                        <div class="card-body text-center">
                            <small class="text-muted">
                                <strong>Demo Note:</strong> For testing purposes, use code: <code>123456</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus and auto-tab for code input
        document.getElementById('code').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    </script>
</body>
</html>