<?php
// logout.php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Clear any other cookies if used
setcookie('admin_logged_in', '', time()-3600, '/');
setcookie('user_type', '', time()-3600, '/');

// Redirect to login page
header("Location: index.html");
exit();
?>
