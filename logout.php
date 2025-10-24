<?php
// logout.php
session_start();

// Store session data for feedback (optional)
$username = $_SESSION['username'] ?? 'User';

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page with logout message
header("Location: login.php?logout=1&user=" . urlencode($username));
exit;
?>