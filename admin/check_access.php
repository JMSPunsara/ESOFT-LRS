<?php
function checkStudentAccess($student_id, $conn) {
    $student_id = mysqli_real_escape_string($conn, $student_id);
    
    $query = "SELECT * FROM student_access_control 
              WHERE student_id = '$student_id' 
              AND status = 'blocked' 
              AND block_end > NOW()";
    
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return [
            'allowed' => false,
            'reason' => $row['block_reason'],
            'end_time' => $row['block_end']
        ];
    }
    
    return ['allowed' => true];
}
?>