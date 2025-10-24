<?php
// setup.php - Complete database and admin user setup
session_start();

// Check if database configuration exists
if (!file_exists('config/db.php')) {
    die("Database configuration file not found. Please create config/database.php first.");
}

require_once 'config/db.php';

$database = new DatabaseConfig();
$db = null;

try {
    $db = $database->getConnection();
    echo "<h3>Database connection successful!</h3>";
} catch (Exception $e) {
    echo "<h3>Database connection failed!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in config/database.php</p>";
    exit;
}

$messages = [];
$success = false;

try {
    echo "<h3>Setting up Business Manager System...</h3>";
    
    // Step 1: Create database if it doesn't exist
    try {
        $db->exec("CREATE DATABASE IF NOT EXISTS business_app");
        $db->exec("USE business_app");
        $messages[] = "Database 'business_app' is ready";
    } catch (Exception $e) {
        $messages[] = "Using existing database";
    }

    // Step 2: Create tables
    $tables_sql = [
        "users" => "CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            two_factor_secret VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "categories" => "CREATE TABLE IF NOT EXISTS categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT
        )",
        
        "goods_services" => "CREATE TABLE IF NOT EXISTS goods_services (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category_id INT,
            type ENUM('good', 'service') NOT NULL,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        )"
    ];

    foreach ($tables_sql as $table_name => $sql) {
        try {
            $db->exec($sql);
            $messages[] = "able '$table_name' created/verified";
        } catch (Exception $e) {
            $messages[] = "Failed to create table '$table_name': " . $e->getMessage();
        }
    }

    // Step 3: Insert categories
    $categories = [
        ['Electronics', 'Electronic devices and accessories'],
        ['Clothing', 'Apparel and fashion items'],
        ['Food & Beverages', 'Food and beverage products'],
        ['Services', 'Professional services'],
        ['Home & Garden', 'Home and garden products']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
    $categories_count = 0;
    foreach($categories as $category) {
        try {
            $stmt->execute($category);
            $categories_count++;
        } catch (Exception $e) {
            // Category might already exist
        }
    }
    $messages[] = "Categories inserted/verified ($categories_count categories)";

    // Step 4: Create admin user with proper password
    $check_admin = $db->prepare("SELECT id FROM users WHERE username = 'admin'");
    $check_admin->execute();
    
    if ($check_admin->rowCount() == 0) {
        // Create admin user
        $admin_password = 'admin123';
        $admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (username, email, password, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@example.com', $admin_hash, '+1234567890']);
        $messages[] = "Admin user created: <strong>admin</strong> / <strong>admin123</strong>";
    } else {
        // Update admin password to ensure it's correct
        $admin_password = 'password';
        $admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->execute([$admin_hash]);
        $messages[] = "Admin user verified and password reset: <strong>admin</strong> / <strong>admin123</strong>";
    }

    // Step 5: Create test user
    $check_test = $db->prepare("SELECT id FROM users WHERE username = 'testuser'");
    $check_test->execute();
    
    if ($check_test->rowCount() == 0) {
        $test_password = 'test123';
        $test_hash = password_hash($test_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute(['testuser', 'test@business.com', $test_hash, '+1234567891']);
        $messages[] = "Test user created: <strong>testuser</strong> / <strong>test123</strong>";
    } else {
        $messages[] = "Test user already exists";
    }

    // Step 6: Insert sample products
    $admin_id = 1; // Assuming admin is ID 1
    $products = [
        ['Laptop', 'High-performance laptop with 16GB RAM', 999.99, 1, 'good', $admin_id],
        ['Smartphone', 'Latest smartphone model', 699.99, 1, 'good', $admin_id],
        ['T-Shirt', '100% Cotton t-shirt', 19.99, 2, 'good', $admin_id],
        ['Coffee', 'Premium coffee beans', 12.99, 3, 'good', $admin_id],
        ['Web Design', 'Professional website design service', 500.00, 4, 'service', $admin_id],
        ['Consulting', 'Business consulting hourly rate', 75.00, 4, 'service', $admin_id]
    ];
    
    $products_count = 0;
    $stmt = $db->prepare("INSERT IGNORE INTO goods_services (name, description, price, category_id, type, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    foreach($products as $product) {
        try {
            $stmt->execute($product);
            $products_count++;
        } catch (Exception $e) {
            // Product might already exist
        }
    }
    $messages[] = "Sample products inserted ($products_count products)";

    $success = true;
    $messages[] = "<strong>Setup completed successfully!</strong>";

} catch(PDOException $e) {
    $messages[] = "Setup error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Manager - Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .message-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .message-success {
            background-color: #d4edda;
            border-left-color: #28a745;
        }
        .message-error {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Business Manager</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">System Setup</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach($messages as $message): ?>
                            <div class="message-item <?php echo strpos($message, '❌') !== false ? 'message-error' : 'message-success'; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endforeach; ?>

                        <?php if($success): ?>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">🔑 Login Credentials</h6>
                                    </div>
                                    <div class="card-body">
                                        <h6>Admin Account:</h6>
                                        <p><strong>Username:</strong> admin<br>
                                        <strong>Password:</strong> admin123<br>
                                        <strong>Email:</strong> admin@business.com</p>
                                        
                                        <h6>Test Account:</h6>
                                        <p><strong>Username:</strong> testuser<br>
                                        <strong>Password:</strong> test123<br>
                                        <strong>Email:</strong> test@business.com</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">📊 Database Status</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        try {
                                            $tables = ['users', 'categories', 'goods_services'];
                                            foreach($tables as $table) {
                                                $count = $db->query("SELECT COUNT(*) as count FROM $table")->fetch();
                                                echo "<p><strong>" . ucfirst($table) . ":</strong> " . $count['count'] . " records</p>";
                                            }
                                        } catch(PDOException $e) {
                                            echo "<p>Error retrieving database stats</p>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="login.php" class="btn btn-success btn-lg me-3">🚀 Go to Login</a>
                            <a href="index.php" class="btn btn-outline-primary btn-lg">🏠 Back to Home</a>
                        </div>
                        <?php else: ?>
                        <div class="text-center mt-4">
                            <a href="setup.php" class="btn btn-warning btn-lg me-3">🔄 Retry Setup</a>
                            <a href="index.php" class="btn btn-outline-secondary btn-lg">🏠 Back to Home</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>