<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    // Get active users
    $active_query = "SELECT COUNT(DISTINCT student_id) as count 
                    FROM login_logs 
                    WHERE logout_time IS NULL";
    $active_result = mysqli_query($conn, $active_query);
    $active_users = mysqli_fetch_assoc($active_result)['count'];

    // Get blocked users
    $blocked_query = "SELECT COUNT(*) as count 
                     FROM student_access_control 
                     WHERE status = 'Blocked' 
                     AND block_end > NOW()";
    $blocked_result = mysqli_query($conn, $blocked_query);
    $blocked_users = mysqli_fetch_assoc($blocked_result)['count'];

    // Get today's logins
    $today_query = "SELECT COUNT(DISTINCT student_id) as count 
                   FROM login_logs 
                   WHERE DATE(login_time) = CURDATE()";
    $today_result = mysqli_query($conn, $today_query);
    $today_logins = mysqli_fetch_assoc($today_result)['count'];

    echo json_encode([
        'success' => true,
        'active_users' => $active_users,
        'blocked_users' => $blocked_users,
        'today_logins' => $today_logins
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
