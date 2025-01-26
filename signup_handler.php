<?php
// signup_handler.php
require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$hostname = "localhost";
$username = "root";
$password = "";
$database = "computer_lab_system";

// Create connection with error handling
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to check if registration number exists
function checkRegistrationNumber($conn, $regNumber) {
    $query = "SELECT COUNT(*) as count FROM students WHERE registration_number = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $regNumber);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get and validate form data
        $firstName = $conn->real_escape_string($_POST['first_name']);
        $lastName = $conn->real_escape_string($_POST['last_name']);
        $gender = $conn->real_escape_string($_POST['gender']);
        $contactNumber = $conn->real_escape_string($_POST['contact_number']);
        $batchYear = $conn->real_escape_string($_POST['batch_year']);
        $batchName = $conn->real_escape_string($_POST['batch_name']);
        $regNumber = $conn->real_escape_string($_POST['registration_number']);
        $email = $conn->real_escape_string($_POST['email']);
        $address = $conn->real_escape_string($_POST['address']);

        // Check if registration number already exists
        $checkQuery = "SELECT COUNT(*) as count FROM students WHERE registration_number = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $regNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            // Registration number already exists
            echo "<div style='color: red; padding: 10px; margin: 10px; border: 1px solid red; background-color: #ffe6e6;'>
                    Error: Registration number '$regNumber' is already registered. Please use a different registration number.
                    <br><br>
                    <a href='javascript:history.back()' style='color: blue; text-decoration: underline;'>Go Back</a>
                  </div>";
        } else {
            // Prepare the INSERT statement
            $insertQuery = "INSERT INTO students (first_name, last_name, gender, contact_number, 
                          batch_year, batch_name, registration_number, email, address) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sssssssss", 
                $firstName, 
                $lastName, 
                $gender, 
                $contactNumber, 
                $batchYear, 
                $batchName, 
                $regNumber, 
                $email, 
                $address
            );

            if ($stmt->execute()) {
                echo "<div style='color: green; padding: 10px; margin: 10px; border: 1px solid green; background-color: #e6ffe6;'>
                        Registration successful! Redirecting to login page...
                      </div>";
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'index.html';
                        }, 3000);
                      </script>";
            } else {
                throw new Exception("Error inserting data: " . $stmt->error);
            }
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo "<div style='color: red; padding: 10px; margin: 10px; border: 1px solid red; background-color: #ffe6e6;'>
                Error: " . $e->getMessage() . "
                <br><br>
                <a href='javascript:history.back()' style='color: blue; text-decoration: underline;'>Go Back</a>
              </div>";
    }
    
    $conn->close();
}
?>
