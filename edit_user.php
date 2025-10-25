<?php
session_start();
require_once 'config/db.php';
require_once 'classes/User.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new DatabaseConfig();
$db = $database->getConnection();
$user = new User($db);

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$id = intval($_GET['id']);
$row = $user->getUserById($id);

if (!$row) {
    die("User not found.");
}

// Access control: only admin or self
$is_admin = ($_SESSION['username'] === 'admin');
if (!$is_admin && $_SESSION['user_id'] != $row['id']) {
    die("Unauthorized access.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit User - Business Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
<div class="container">
    <a class="navbar-brand" href="users.php">Business Manager</a>
    <div class="navbar-nav ms-auto">
        <span class="navbar-text me-3">
            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
        </span>
        <a class="nav-link" href="users.php"><i class="bi bi-people-fill"></i> Users</a>
        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>
</nav>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="bi bi-pencil"></i> Edit User</h4>
        </div>
        <div class="card-body">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success">✅ User updated successfully!</div>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger">❌ <?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <form action="update_user.php" method="POST">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($row['username']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($row['email']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($row['phone']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                    <input type="password" name="password" class="form-control">
                </div>

                <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Update</button>
                <a href="users.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
