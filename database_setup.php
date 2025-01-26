<?php
require_once 'config.php';

// Create tables
$sql = "
-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    student_id VARCHAR(50) UNIQUE NOT NULL,
    course VARCHAR(100),
    year_level VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Computers table
CREATE TABLE IF NOT EXISTS computers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pc_number VARCHAR(20) UNIQUE NOT NULL,
    lab_room VARCHAR(50),
    status ENUM('available', 'in-use', 'maintenance') DEFAULT 'available',
    last_maintenance TIMESTAMP NULL
);

-- PC Usage table
CREATE TABLE IF NOT EXISTS pc_usage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    pc_id INT,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (pc_id) REFERENCES computers(id)
);

-- Activity Logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    activity_type VARCHAR(50),
    details TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

-- Reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    pc_id INT,
    reservation_date DATE,
    time_slot TIME,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (pc_id) REFERENCES computers(id)
);
";

// Execute the SQL to create tables
if (mysqli_multi_query($conn, $sql)) {
    do {
        // Consume the result to allow execution of the next query
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
    echo "Database tables created successfully!";
} else {
    echo "Error creating tables: " . mysqli_error($conn);
}

// Insert sample data for testing
$sample_data = "
-- Insert sample students
INSERT INTO students (name, email, password, student_id, course, year_level) VALUES
('John Doe', 'john@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'STU001', 'Computer Science', '3rd Year'),
('Jane Smith', 'jane@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'STU002', 'Information Technology', '2nd Year')
ON DUPLICATE KEY UPDATE email=email;

-- Insert sample computers
INSERT INTO computers (pc_number, lab_room) VALUES
('PC001', 'Lab 1'),
('PC002', 'Lab 1'),
('PC003', 'Lab 2')
ON DUPLICATE KEY UPDATE pc_number=pc_number;

-- Insert sample activity logs
INSERT INTO activity_logs (student_id, activity_type, details) 
SELECT id, 'Login', 'First time login' 
FROM students 
WHERE email = 'john@example.com'
LIMIT 1;
";

// Execute sample data insertion
if (mysqli_multi_query($conn, $sample_data)) {
    do {
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
    echo "\nSample data inserted successfully!";
} else {
    echo "\nError inserting sample data: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
