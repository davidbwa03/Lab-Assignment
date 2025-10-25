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

// Current user
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];
$is_admin = ($current_username === 'admin');
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
        .current-user {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }
        .user-table img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="products.php">Business Manager</a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3">
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($current_username) ?>
            </span>
            <a class="nav-link" href="products.php"><i class="bi bi-grid"></i> Products</a>
            <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✅ User deleted successfully!</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">❌ Failed to delete the user.</div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-people-fill"></i> Registered Users</h4>
            <span class="badge bg-light text-dark">
                <?= $users->rowCount() ?> total
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
                                <th>2FA</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php while($row = $users->fetch(PDO::FETCH_ASSOC)): 
                            $is_current_user = ($row['id'] == $current_user_id);
                        ?>
                            <tr class="<?= $is_current_user ? 'current-user' : '' ?>">
                                <td>
                                    <strong><?= $row['id'] ?></strong>
                                    <?php if($is_current_user): ?>
                                        <span class="badge bg-success ms-1">You</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <strong><?= htmlspecialchars($row['username']) ?></strong>
                                    <?php if($row['username'] == 'admin'): ?>
                                        <span class="badge bg-danger ms-1">Admin</span>
                                    <?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($row['email']) ?></td>

                                <td>
                                    <?= !empty($row['phone']) 
                                        ? htmlspecialchars($row['phone'])
                                        : '<span class="text-muted">Not provided</span>'; ?>
                                </td>

                                <td><span class="badge bg-secondary">Disabled</span></td>

                                <td>
                                    <small>
                                        <?= date('M j, Y', strtotime($row['created_at'])) ?><br>
                                        <span class="text-muted"><?= date('g:i A', strtotime($row['created_at'])) ?></span>
                                    </small>
                                </td>

                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- View -->
                                        <a href="view_user.php?id=<?= $row['id'] ?>" 
                                           class="btn btn-outline-primary" 
                                           title="View User">
                                           <i class="bi bi-eye"></i>
                                        </a>

                                        <!-- Edit -->
                                        <?php if($is_current_user || $is_admin): ?>
                                            <a href="edit_user.php?id=<?= $row['id'] ?>" 
                                               class="btn btn-outline-success" 
                                               title="Edit User">
                                               <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Delete -->
                                        <?php if(!$is_current_user && $is_admin): ?>
                                            <form action="delete_user.php" method="POST" 
                                                  onsubmit="return confirm('Delete this user?');">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" 
                                                        class="btn btn-outline-danger" 
                                                        title="Delete User">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>

                            </tr>
                        <?php endwhile; ?>

                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">No registered users found.</p>
            <?php endif; ?>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
