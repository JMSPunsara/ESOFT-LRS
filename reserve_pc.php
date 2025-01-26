<?php
// reserve_pc.php
require_once 'check_session.php';
check_login();

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'computer_lab_system';

$student_id = get_student_id();

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if student already has an active session
    $check_stmt = $conn->prepare("
        SELECT id FROM pc_reservations 
        WHERE student_id = :student_id 
        AND status = 'active'
    ");
    $check_stmt->bindParam(':student_id', $student_id);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You already have an active PC session']);
        exit();
    }

    // Check if PC is available
    $pc_number = $_POST['pc_number'];
    $check_pc_stmt = $conn->prepare("
        SELECT id FROM pc_reservations 
        WHERE pc_number = :pc_number 
        AND status = 'active'
    ");
    $check_pc_stmt->bindParam(':pc_number', $pc_number);
    $check_pc_stmt->execute();

    if ($check_pc_stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This PC is currently in use']);
        exit();
    }

    // Create new reservation
    $reserve_stmt = $conn->prepare("
        INSERT INTO pc_reservations (student_id, pc_number, start_time, status)
        VALUES (:student_id, :pc_number, NOW(), 'active')
    ");
    $reserve_stmt->bindParam(':student_id', $student_id);
    $reserve_stmt->bindParam(':pc_number', $pc_number);
    $reserve_stmt->execute();

    echo json_encode(['status' => 'success']);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>