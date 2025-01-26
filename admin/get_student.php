<?php
require_once dirname(__DIR__) . '/config.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Student ID is required']);
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM students WHERE id = '$id'";
$result = mysqli_query($conn, $query);

if ($result && $student = mysqli_fetch_assoc($result)) {
    echo json_encode($student);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Student not found']);
}
?>
