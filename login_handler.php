<?php
// Initialize session
session_start();

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

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registration_number = sanitize_input($_POST['registration_number']);
    
    // Validate registration number
    if (empty($registration_number)) {
        exit();
    }
    
    try {
        // Prepare SQL statement
        $stmt = $conn->prepare("SELECT * FROM students WHERE registration_number = :reg_num");
        $stmt->bindParam(':reg_num', $registration_number);
        $stmt->execute();
        
        // Check if student exists
        if ($stmt->rowCount() > 0) {
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Set session variables
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['registration_number'] = $student['registration_number'];
            $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
            $_SESSION['logged_in'] = true;
            
            // Log login time
            $log_stmt = $conn->prepare("INSERT INTO login_logs (student_id, login_time) VALUES (:student_id, NOW())");
            $log_stmt->bindParam(':student_id', $student['id']);
            $log_stmt->execute();
            
            // Store the login_log_id for logging logout time later
            $_SESSION['current_login_id'] = $conn->lastInsertId();
            
            echo json_encode(['status' => 'success', 'redirect' => 'dashboard.php']);
        } else {
        }
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>