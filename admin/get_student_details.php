<?php
require_once 'config.php';
header('Content-Type: application/json');

if (isset($_GET['registration_number'])) {
    try {
        $reg_number = mysqli_real_escape_string($conn, $_GET['registration_number']);
        
        $query = "SELECT 
                    s.*,
                    COALESCE(pc.total_sessions, 0) as total_sessions,
                    COALESCE(TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(pr.end_time, pr.start_time)))), '%H:%i:%s'), '00:00:00') as avg_session_time,
                    MAX(ll.login_time) as last_login,
                    sac.status as access_status,
                    sac.block_reason,
                    sac.block_end
                  FROM students s
                  LEFT JOIN (
                      SELECT student_id, COUNT(*) as total_sessions 
                      FROM pc_reservations 
                      GROUP BY student_id
                  ) pc ON s.registration_number = pc.student_id
                  LEFT JOIN pc_reservations pr ON s.registration_number = pr.student_id
                  LEFT JOIN login_logs ll ON s.registration_number = ll.student_id
                  LEFT JOIN student_access_control sac ON s.registration_number = sac.registration_number
                  WHERE s.registration_number = ?
                  GROUP BY s.id";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $reg_number);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($student = mysqli_fetch_assoc($result)) {
            echo json_encode([
                'success' => true,
                'student' => $student
            ]);
        } else {
            throw new Exception('Student not found');
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>
