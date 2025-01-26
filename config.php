<?php
// config.php - Save this in your project root folder
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'computer_lab_system');

// Create database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to ensure proper handling of Unicode characters
mysqli_set_charset($conn, "utf8mb4");

// Define base path constant
define('BASE_PATH', __DIR__);
?>