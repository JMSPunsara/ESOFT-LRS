<?php
session_start();

function check_login() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: index.html');
        exit();
    }
}

function get_student_id() {
    return $_SESSION['student_id'] ?? null;
}

// Function to check if student has active session
function has_active_session($conn, $student_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM pc_reservations 
        WHERE student_id = ? AND status = 'active'
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

// Function to get active session details
function get_active_session($conn, $student_id) {
    $stmt = $conn->prepare("
        SELECT *, 
        TIMESTAMPDIFF(MINUTE, start_time, CURRENT_TIMESTAMP) as current_duration 
        FROM pc_reservations 
        WHERE student_id = ? AND status = 'active'
        LIMIT 1
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}


?>