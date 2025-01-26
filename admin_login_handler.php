<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if(empty($username) || empty($password)) {
        header("Location: admin_login.php?error=empty");
        exit;
    }

    // Prepare SQL statement
    $sql = "SELECT * FROM admin_users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if($row = mysqli_fetch_assoc($result)) {
        if(password_verify($password, $row['password'])) {
            // Password is correct
            $_SESSION['admin_loggedin'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_id'] = $row['id'];
            
            mysqli_stmt_close($stmt);
            header("Location: admin_dashboard.php");
            exit;
        }
    }

    // If we get here, authentication failed
    mysqli_stmt_close($stmt);
    header("Location: admin_login.php?error=invalid");
    exit;
}

// If not POST request
header("Location: admin_login.php");
exit;
?>
