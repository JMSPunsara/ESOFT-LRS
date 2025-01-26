<?php
session_start();
require_once dirname(__FILE__) . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';
    $batch_year = isset($_POST['batch_year']) ? mysqli_real_escape_string($conn, $_POST['batch_year']) : '';
    $batch_name = isset($_POST['batch_name']) ? mysqli_real_escape_string($conn, $_POST['batch_name']) : '';
    $start_date = isset($_POST['start_date']) ? mysqli_real_escape_string($conn, $_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? mysqli_real_escape_string($conn, $_POST['end_date']) : '';

    // Store in session
    $_SESSION['reservation_search'] = [
        'search' => $search,
        'batch_year' => $batch_year,
        'batch_name' => $batch_name,
        'start_date' => $start_date,
        'end_date' => $end_date
    ];

    // Redirect back to the reservation history page
    header("Location: reservation_history.php");
    exit;
}

// If someone tries to access this file directly without POST data
header("Location: reservation_history.php");
exit;
?>
