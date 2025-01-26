<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('Student ID is required');
    }

    $student_id = mysqli_real_escape_string($conn, $_POST['id']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $registration_number = mysqli_real_escape_string($conn, $_POST['registration_number']);
    $batch_name = mysqli_real_escape_string($conn, $_POST['batch_name']);
    $batch_year = mysqli_real_escape_string($conn, $_POST['batch_year']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');

    // Check if registration number exists for other students
    $check_query = "SELECT id FROM students WHERE registration_number = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "si", $registration_number, $student_id);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_fetch($stmt)) {
        throw new Exception('Registration number already exists');
    }

    // Update student
    $query = "UPDATE students SET 
        first_name = ?, last_name = ?, email = ?, 
        registration_number = ?, batch_name = ?, batch_year = ?,
        contact_number = ?, address = ?
        WHERE id = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssssssi", 
        $first_name, $last_name, $email,
        $registration_number, $batch_name, $batch_year,
        $contact_number, $address, $student_id
    );

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true,
            'message' => 'Student updated successfully'
        ]);
    } else {
        throw new Exception('Error updating student: ' . mysqli_error($conn));
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

?>
