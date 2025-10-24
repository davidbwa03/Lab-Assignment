<?php
session_start();
require_once 'config/db.php';
require_once 'classes/User.php';
require_once 'classes/EmailService.php';
require_once 'includes/validation.php';

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: products.php");
    exit;
}

$database = new DatabaseConfig();
$db = $database->getConnection();
$user = new User($db);

$error = '';
$success = '';

if($_POST) {
    try {
        // Sanitize input data
        $data = Validation::sanitizeInput($_POST);
        
        // Validate inputs
        $usernameValidation = Validation::validateUsername($data['username']);
        $emailValidation = Validation::validateEmail($data['email']);
        $passwordValidation = Validation::validatePassword($data['password']);
        $phoneValidation = Validation::validatePhone($data['phone']);

        if($usernameValidation !== true) {
            $error = $usernameValidation;
        } elseif($emailValidation !== true) {
            $error = $emailValidation;
        } elseif($passwordValidation !== true) {
            $error = $passwordValidation;
        } elseif($phoneValidation !== true) {
            $error = $phoneValidation;
        } elseif($data['password'] !== $data['confirm_password']) {
            $error = "Passwords do not match";
        } else {
            // Register user and get user ID
            $userId = $user->register($data);
            
            if($userId) {
                // Generate 6-digit OTP code
                $otpCode = sprintf("%06d", mt_rand(1, 999999));
                $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                // Save OTP to database using the User class method
                $saveResult = $user->set2FACode($userId, $otpCode, $expiry);
                
                if($saveResult) {
                    // Send 2FA email
                    $emailService = new EmailService();
                    $emailSent = $emailService->send2FACode($data['email'], $data['username'], $otpCode);
                    
                    if($emailSent) {
                        // Store user ID in session for verification
                        $_SESSION['user_id_2fa'] = $userId;
                        $_SESSION['2fa_required'] = true;
                        $_SESSION['user_email'] = $data['email'];
                        $_SESSION['username'] = $data['username'];
                        
                        // Redirect to 2FA verification page
                        header("Location: verify_2fa.php");
                        exit();
                    } else {
                        // Email failed - show OTP for manual entry
                        $error = "Registration successful! However, we couldn't send the email. Your verification code: <strong>$otpCode</strong> (valid for 10 minutes) - <a href='verify_2fa.php' class='alert-link'>Click here to verify</a>";
                        $_SESSION['user_id_2fa'] = $userId;
                        $_SESSION['2fa_required'] = true;
                        $_SESSION['user_email'] = $data['email'];
                    }
                } else {
                    $error = "Registration successful but failed to setup 2FA. Please contact support.";
                }
            } else {
                $error = "Registration failed. Username or email may already exist.";
            }
        }
    } catch(Exception $e) {
        $error = "System error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Business Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .debug-otp {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
            font-family: monospace;
        }
        .alert-link {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Business Manager</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="login.php">Login</a>
                <a class="nav-link" href="index.php">Home</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center mb-0">Create Your Account</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if($error): ?>
                            <div class="alert alert-dismissible fade show <?php echo strpos($error, 'successful') !== false ? 'alert-warning' : 'alert-danger'; ?>" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                
                                <?php if(strpos($error, 'verification code') !== false || strpos($error, 'couldn\'t send') !== false): ?>
                                    <div class="debug-otp mt-2">
                                        <strong>Manual Verification Required:</strong><br>
                                        Go to <a href="verify_2fa.php" class="btn btn-sm btn-warning mt-2">Verify 2FA Code</a> and enter the code provided above.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                       required maxlength="50" pattern="[a-zA-Z0-9_]{3,50}" title="3-50 characters, letters, numbers, and underscores only">
                                <div class="form-text">3-50 characters, letters, numbers, and underscores only</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required maxlength="100">
                                <div class="form-text">We'll send a verification code to this email</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                       maxlength="20">
                                <div class="form-text">Optional - include country code</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$">
                                <div class="form-text">Min 8 characters with uppercase, lowercase, and number</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       required minlength="8">
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 py-2">Create Account</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
                        </div>
                    </div>
                </div>
                
                <!-- 2FA Information -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6>🔒 Two-Factor Authentication</h6>
                        <p class="mb-2">After registration, you'll receive a 6-digit verification code via email to secure your account.</p>
                        <ul class="list-unstyled mb-0 small">
                            <li>✅ Code valid for 10 minutes</li>
                            <li>✅ Required for first login</li>
                            <li>✅ Enhanced security</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-time password validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        password.addEventListener('input', function() {
            const requirements = {
                length: this.value.length >= 8,
                uppercase: /[A-Z]/.test(this.value),
                lowercase: /[a-z]/.test(this.value),
                number: /[0-9]/.test(this.value)
            };
            
            // Visual feedback can be added here
            if (this.value.length > 0) {
                if (!requirements.length) this.style.borderColor = '#dc3545';
                else if (!requirements.uppercase || !requirements.lowercase || !requirements.number) {
                    this.style.borderColor = '#ffc107';
                } else {
                    this.style.borderColor = '#198754';
                }
            } else {
                this.style.borderColor = '';
            }
        });
        
        // Confirm password match
        confirmPassword.addEventListener('input', function() {
            if (this.value && password.value !== this.value) {
                this.setCustomValidity('Passwords do not match');
                this.style.borderColor = '#dc3545';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = password.value ? '#198754' : '';
            }
        });
    </script>
</body>
</html>