<?php
// Remove any existing connection
if (isset($conn)) {
    mysqli_close($conn);
}

// Undefine constants if they exist
if (defined('DB_HOST')) {
    return;
}

// Define database constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'computer_lab_system');

// Create new connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
