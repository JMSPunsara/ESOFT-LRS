<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $search_name = mysqli_real_escape_string($conn, $_POST['search_name'] ?? '');
    $search_reg = mysqli_real_escape_string($conn, $_POST['search_reg'] ?? '');
    $search_batch = mysqli_real_escape_string($conn, $_POST['search_batch'] ?? '');

    $where_clauses = [];
    $params = [];
    $types = "";

    if (!empty($search_name)) {
        $where_clauses[] = "(first_name LIKE ? OR last_name LIKE ?)";
        $search_term = "%{$search_name}%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "ss";
    }

    if (!empty($search_reg)) {
        $where_clauses[] = "registration_number LIKE ?";
        $params[] = "%{$search_reg}%";
        $types .= "s";
    }

    if (!empty($search_batch)) {
        $where_clauses[] = "batch_year = ?";
        $params[] = $search_batch;
        $types .= "s";
    }

    $query = "SELECT * FROM students";
    if (!empty($where_clauses)) {
        $query .= " WHERE " . implode(" AND ", $where_clauses);
    }
    $query .= " ORDER BY first_name, last_name";

    $stmt = mysqli_prepare($conn, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $students = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }

    echo json_encode([
        'success' => true,
        'students' => $students
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>