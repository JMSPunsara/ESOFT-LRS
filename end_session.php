<?php
require_once 'check_session.php';
require_once 'config.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$student_id = get_student_id();

try {
    // Create connection
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Find the active session
        $stmt = $conn->prepare("
            SELECT id, pc_number 
            FROM pc_reservations 
            WHERE student_id = ? AND status = 'active'
            LIMIT 1
        ");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("No active session found");
        }

        $session = $result->fetch_assoc();
        
        // Update the session to completed
        $update_stmt = $conn->prepare("
            UPDATE pc_reservations 
            SET status = 'completed',
                end_time = CURRENT_TIMESTAMP,
                duration_minutes = TIMESTAMPDIFF(MINUTE, start_time, CURRENT_TIMESTAMP)
            WHERE id = ? AND status = 'active'
        ");
        
        $update_stmt->bind_param("i", $session['id']);
        $update_stmt->execute();

        if ($update_stmt->affected_rows === 0) {
            throw new Exception("Failed to update session");
        }

        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Session ended successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while ending the session: ' . $e->getMessage()
    ]);
}

$conn->close();
?>