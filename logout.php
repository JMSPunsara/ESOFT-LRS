<?php
// logout.php
require_once 'check_session.php';
check_login();

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'computer_lab_system';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

try {
    // Update logout time
    if (isset($_SESSION['current_login_id'])) {
        $stmt = $conn->prepare("
            UPDATE login_logs 
            SET logout_time = NOW() 
            WHERE id = :login_id
        ");
        $stmt->bindParam(':login_id', $_SESSION['current_login_id']);
        $stmt->execute();
    }
    
    // Clear session
    session_destroy();
    
    echo json_encode(['status' => 'success']);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
// Redirect to login page
header("Location: index.html");
exit();
?>