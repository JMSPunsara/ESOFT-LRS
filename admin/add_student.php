<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email', 'registration_number', 'batch_name', 'batch_year'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // Sanitize inputs
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $registration_number = mysqli_real_escape_string($conn, $_POST['registration_number']);
    $batch_name = mysqli_real_escape_string($conn, $_POST['batch_name']);
    $batch_year = mysqli_real_escape_string($conn, $_POST['batch_year']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');

    // Check if registration number already exists
    $check_query = "SELECT id FROM students WHERE registration_number = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $registration_number);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_fetch($stmt)) {
        throw new Exception('Registration number already exists');
    }

    // Insert student
    $query = "INSERT INTO students (
        first_name, last_name, email, registration_number, 
        batch_name, batch_year, contact_number, address
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssssss", 
        $first_name, $last_name, $email, $registration_number,
        $batch_name, $batch_year, $contact_number, $address
    );

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true,
            'message' => 'Student added successfully'
        ]);
    } else {
        throw new Exception('Error adding student: ' . mysqli_error($conn));
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>