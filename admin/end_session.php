<?php
require_once dirname(__FILE__) . '/../config.php';

$reservation_id = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : 'end';

if (!$reservation_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid reservation ID']);
    exit;
}

try {
    if ($action === 'end') {
        // End session
        $query = "UPDATE pc_reservations SET end_time = NOW() WHERE id = ?";
    } else if ($action === 'delete') {
        // Delete reservation
        $query = "DELETE FROM pc_reservations WHERE id = ?";
    }

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Query preparation failed");
    }

    mysqli_stmt_bind_param($stmt, "i", $reservation_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => ($action === 'end' ? 'Session ended successfully' : 'Reservation deleted successfully')]);
    } else {
        throw new Exception("Failed to " . ($action === 'end' ? 'end session' : 'delete reservation'));
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
