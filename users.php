<?php
session_start();
require_once 'config/db.php';
require_once 'classes/User.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new DatabaseConfig();
$db = $database->getConnection();
$user = new User($db);

// Get all users
$users = $user->readAll();

// Get current user info
$current_user = $user->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Business Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .user-table img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        .current-user {
            background-color: rgba(25, 135, 84, 0.1) !important;
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
                <a class="nav-link" href="products.php"><i class="bi bi-grid"></i> Products & Services</a>
                <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-people-fill"></i> Registered Users</h4>
                        <span class="badge bg-light text-dark">
                            <?php echo $users->rowCount() . ' user' . ($users->rowCount() != 1 ? 's' : ''); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if($users->rowCount() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover user-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>2FA Status</th>
                                        <th>Registration Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $users->fetch(PDO::FETCH_ASSOC)): 
                                        $is_current_user = ($row['id'] == $_SESSION['user_id']);
                                    ?>
                                    <tr class="<?php echo $is_current_user ? 'current-user' : ''; ?>">
                                        <td>
                                            <strong><?php echo $row['id']; ?></strong>
                                            <?php if($is_current_user): ?>
                                                <span class="badge bg-success ms-1">You</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                                                    <?php if($row['username'] == 'admin'): ?>
                                                        <span class="badge bg-danger ms-1">Admin</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($row['email']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if(!empty($row['phone'])): ?>
                                                <a href="tel:<?php echo htmlspecialchars($row['phone']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($row['phone']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Not provided</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            // In a real implementation, you'd check if 2FA is enabled
                                            $has_2fa = false; // This would come from the database
                                            ?>
                                            <span class="badge bg-<?php echo $has_2fa ? 'success' : 'secondary'; ?>">
                                                <?php echo $has_2fa ? 'Enabled' : 'Disabled'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo date('M j, Y', strtotime($row['created_at'])); ?><br>
                                                <span class="text-muted"><?php echo date('g:i A', strtotime($row['created_at'])); ?></span>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" title="View Profile">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <?php if($is_current_user): ?>
                                                    <button class="btn btn-outline-success" title="Edit Profile">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if(!$is_current_user && $_SESSION['username'] == 'admin'): ?>
                                                    <button class="btn btn-outline-danger" title="Delete User">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">No Users Found</h4>
                            <p class="text-muted">There are no registered users in the system.</p>
                            <a href="register.php" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Register First User
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $users->rowCount(); ?></h4>
                                        <p class="mb-0">Total Users</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-people display-6"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $users->rowCount() - 1; ?></h4>
                                        <p class="mb-0">Regular Users</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-person display-6"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4>1</h4>
                                        <p class="mb-0">Admin Users</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-shield-check display-6"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>