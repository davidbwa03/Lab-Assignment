<?php
session_start();
require_once 'config/db.php';
require_once 'classes/Product.php';
require_once 'classes/User.php';
require_once 'includes/validation.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new DatabaseConfig();
$db = $database->getConnection();
$product = new Product($db);
$user = new User($db);

$error = '';
$success = '';
$search_results = null;

// Handle form submission for new product
if($_POST && isset($_POST['name'])) {
    try {
        $data = Validation::sanitizeInput($_POST);
        $data['created_by'] = $_SESSION['user_id'];
        
        $priceValidation = Validation::validatePrice($data['price']);
        
        if($priceValidation === true) {
            if($product->create($data)) {
                $success = "Product/Service added successfully!";
                $_POST = array(); // Clear form
            } else {
                $error = "Failed to add product/service. Please try again.";
            }
        } else {
            $error = $priceValidation;
        }
    } catch(PDOException $e) {
        if($e->getCode() == '23000') {
            $error = "Database error: Please make sure the category exists.";
        } else {
            $error = "Error: " . $e->getMessage();
        }
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle search
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = Validation::sanitizeInput($_GET['search']);
    $search_results = $product->search($search_term);
}

// Get all products and categories
$products = $product->read();
$categories = $product->getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products & Services - Business Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 20px;
        }
        .badge-good {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        .badge-service {
            background: linear-gradient(135deg, #007bff, #6f42c1);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
        .search-box {
            max-width: 400px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="products.php">Business Manager</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <a class="nav-link" href="users.php"><i class="bi bi-people"></i> View Users</a>
                <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar for Add Product Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Add Product/Service</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Name *</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                       required maxlength="255">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3" 
                                          maxlength="500"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                <div class="form-text">Maximum 500 characters</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Price ($) *</label>
                                <input type="number" step="0.01" min="0.01" max="9999999.99" 
                                       class="form-control" name="price" 
                                       value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php while($category = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Type *</label>
                                <select class="form-select" name="type" required>
                                    <option value="good" <?php echo (isset($_POST['type']) && $_POST['type'] == 'good') ? 'selected' : ''; ?>>Product (Good)</option>
                                    <option value="service" <?php echo (isset($_POST['type']) && $_POST['type'] == 'service') ? 'selected' : ''; ?>>Service</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg"></i> Add Product/Service
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="col-md-8">
                <!-- Search Box -->
                <div class="card">
                    <div class="card-body">
                        <form method="get" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" 
                                   placeholder="Search products and services..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                            <?php if(isset($_GET['search'])): ?>
                                <a href="products.php" class="btn btn-outline-secondary ms-2">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-grid"></i> 
                            <?php echo isset($_GET['search']) ? 'Search Results' : 'All Products & Services'; ?>
                        </h4>
                        <span class="badge bg-light text-dark">
                            <?php 
                            $count = isset($search_results) ? $search_results->rowCount() : $products->rowCount();
                            echo $count . ' item' . ($count != 1 ? 's' : '');
                            ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Category</th>
                                        <th>Type</th>
                                        <th>Added By</th>
                                        <th>Date Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $data = isset($search_results) ? $search_results : $products;
                                    if($data->rowCount() > 0): 
                                        while($row = $data->fetch(PDO::FETCH_ASSOC)): 
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td>
                                            <span class="fw-bold text-success">$<?php echo number_format($row['price'], 2); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($row['category_name']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $row['type'] == 'good' ? 'badge-good' : 'badge-service'; ?>">
                                                <?php echo ucfirst($row['type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['created_by_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                    </tr>
                                    <?php 
                                        endwhile; 
                                    else: 
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="bi bi-inbox display-4 text-muted"></i>
                                            <p class="mt-3 text-muted">No products or services found.</p>
                                            <?php if(isset($_GET['search'])): ?>
                                                <p class="text-muted">Try a different search term or <a href="products.php">view all items</a>.</p>
                                            <?php else: ?>
                                                <p class="text-muted">Start by adding your first product or service using the form on the left.</p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Price input validation
        document.querySelector('input[name="price"]').addEventListener('input', function(e) {
            if (this.value < 0) {
                this.value = 0.01;
            }
            if (this.value > 9999999.99) {
                this.value = 9999999.99;
            }
        });
    </script>
</body>
</html>