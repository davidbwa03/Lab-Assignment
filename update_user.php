<?php
session_start();
require_once 'config/db.php';
require_once 'classes/User.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: users.php");
    exit;
}

$database = new DatabaseConfig();
$db = $database->getConnection();
$user = new User($db);

$id = intval($_POST['id']);
$row = $user->getUserById($id);

if (!$row) {
    header("Location: users.php?error=User not found");
    exit;
}

// Access control: only admin or self
$is_admin = ($_SESSION['username'] === 'admin');
if (!$is_admin && $_SESSION['user_id'] != $id) {
    die("Unauthorized access.");
}

// Sanitize inputs
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = $_POST['password'];

// Validation (simple)
if (empty($username) || empty($email)) {
    header("Location: edit_user.php?id=$id&error=Username and Email required");
    exit;
}

// Update fields
try {
    $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$username, $email, $phone, $id]);

    // Update password if provided
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt2 = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt2->execute([$password_hash, $id]);
    }

    header("Location: edit_user.php?id=$id&success=1");
    exit;

} catch (PDOException $e) {
    header("Location: edit_user.php?id=$id&error=" . urlencode($e->getMessage()));
    exit;
}
