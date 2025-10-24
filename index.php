<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .card-hover {
            transition: transform 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-building"></i> Business Manager
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="login.php">Login</a>
                <a class="nav-link" href="register.php">Register</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Business Management System</h1>
            <p class="lead mb-5">A comprehensive OOP PHP application for managing users, products, and services</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <a href="setup.php" class="btn btn-light btn-lg w-100 py-3">
                                🚀 Quick Setup
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="login.php" class="btn btn-outline-light btn-lg w-100 py-3">
                                🔐 User Login
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="register.php" class="btn btn-outline-light btn-lg w-100 py-3">
                                👥 Register
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col">
                    <h2 class="display-5">System Features</h2>
                    <p class="lead">Built with Object-Oriented PHP and Modern Practices</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-hover h-100 text-center">
                        <div class="card-body">
                            <div class="feature-icon">👥</div>
                            <h5 class="card-title">User Management</h5>
                            <p class="card-text">Complete user registration, login, and profile management system with secure authentication.</p>
                            <ul class="list-unstyled text-start">
                                <li>✅ User Registration</li>
                                <li>✅ Secure Login</li>
                                <li>✅ 2FA Support</li>
                                <li>✅ Profile Management</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-hover h-100 text-center">
                        <div class="card-body">
                            <div class="feature-icon">📦</div>
                            <h5 class="card-title">Product Management</h5>
                            <p class="card-text">Manage both physical goods and services with categories, pricing, and detailed descriptions.</p>
                            <ul class="list-unstyled text-start">
                                <li>✅ Add Products/Services</li>
                                <li>✅ Category Management</li>
                                <li>✅ Price Tracking</li>
                                <li>✅ Search & Filter</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-hover h-100 text-center">
                        <div class="card-body">
                            <div class="feature-icon">🛡️</div>
                            <h5 class="card-title">Security & Validation</h5>
                            <p class="card-text">Advanced security features including input validation and two-factor authentication.</p>
                            <ul class="list-unstyled text-start">
                                <li>✅ Input Validation</li>
                                <li>✅ 2FA Ready</li>
                                <li>✅ SQL Injection Protection</li>
                                <li>✅ XSS Prevention</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Technical Features -->
    

    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">Business Management System &copy; <?php echo date('Y'); ?> - OOP PHP Lab Project</p>
            <p class="mb-0">Demonstrating Object-Oriented PHP, Database Integration, and Security Practices</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>