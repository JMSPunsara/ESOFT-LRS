<?php
session_start();
require_once 'config.php'; // Make sure this has mysqli connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if(empty($username) || empty($password) || empty($confirm_password)) {
        header("Location: admin_login.php?error=empty&form=signup");
        exit;
    }

    if($password !== $confirm_password) {
        header("Location: admin_login.php?error=password_mismatch&form=signup");
        exit;
    }

    // Check if admin already exists
    $check_query = "SELECT COUNT(*) as count FROM admin_users WHERE username = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $username);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    $row = mysqli_fetch_assoc($result);
    
    if($row['count'] > 0) {
        mysqli_stmt_close($check_stmt);
        header("Location: admin_login.php?error=exists&form=signup");
        exit;
    }
    mysqli_stmt_close($check_stmt);

    // Insert new admin
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_query = "INSERT INTO admin_users (username, password) VALUES (?, ?)";
    $insert_stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "ss", $username, $hashed_password);

    if(mysqli_stmt_execute($insert_stmt)) {
        mysqli_stmt_close($insert_stmt);
        header("Location: admin_login.php?success=registered");
        exit;
    } else {
        mysqli_stmt_close($insert_stmt);
        header("Location: admin_login.php?error=database&form=signup");
        exit;
    }
}

// If not POST request
header("Location: admin_login.php");
exit;
?>
