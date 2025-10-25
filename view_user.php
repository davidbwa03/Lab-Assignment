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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View User - Business Manager</title>
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
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-eye"></i> View User</h4>
        </div>
        <div class="card-body">
            <p><strong>ID:</strong> <?= $row['id'] ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($row['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
            <p><strong>Phone:</strong> <?= !empty($row['phone']) ? htmlspecialchars($row['phone']) : '<span class="text-muted">Not provided</span>' ?></p>
            <p><strong>2FA Status:</strong> <?= !empty($row['two_factor_secret']) ? 'Enabled' : 'Disabled' ?></p>
            <p><strong>Registered:</strong> <?= date('M j, Y g:i A', strtotime($row['created_at'])) ?></p>
            <p><strong>Last Updated:</strong> <?= date('M j, Y g:i A', strtotime($row['updated_at'])) ?></p>
            <a href="users.php" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
