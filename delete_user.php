<?php
session_start();
require_once 'config/db.php';
require_once 'classes/User.php';

// Must be admin to delete someone else
if (!isset($_SESSION['user_id']) || $_SESSION['username'] != 'admin') {
    header("Location: users.php?error=unauthorized");
    exit;
}

if (!isset($_POST['id'])) {
    header("Location: users.php?error=missing_id");
    exit;
}

$userId = $_POST['id'];

// Prevent admin from deleting themselves
if ($userId == $_SESSION['user_id']) {
    header("Location: users.php?error=cannot_delete_self");
    exit;
}

$database = new DatabaseConfig();
$db = $database->getConnection();
$user = new User($db);

if ($user->delete($userId)) {
    header("Location: users.php?success=user_deleted");
} else {
    header("Location: users.php?error=delete_failed");
}
exit;
