<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_loggedin'])) {
    header("Location: admin_login.php");
    exit;
}

// Create required tables if they don't exist
$tables_sql = "
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    course VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pc_usage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    pc_number INT,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    activity_type VARCHAR(50),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

CREATE TABLE IF NOT EXISTS computers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pc_number INT UNIQUE,
    status ENUM('available', 'in-use', 'maintenance') DEFAULT 'available'
);
";

mysqli_multi_query($conn, $tables_sql);
while(mysqli_next_result($conn)){} // Clear all results
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>/* Animated Refresh Button Styles */
.refresh-btn {
    position: fixed;
    right: 30px;
    bottom: 30px;
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    z-index: 1000;
}

.refresh-icon {
    width: 24px;
    height: 24px;
    fill: white;
    transition: transform 0.5s ease;
}

.refresh-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.refresh-btn:hover .refresh-icon {
    animation: spin 1s ease infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.refresh-btn.clicked {
    animation: pulse 0.5s ease;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(0.9); }
    100% { transform: scale(1); }
}

.refresh-tooltip {
    position: absolute;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    right: 70px;
    opacity: 0;
    transition: opacity 0.3s ease;
    white-space: nowrap;
}

.refresh-btn:hover .refresh-tooltip {
    opacity: 1;
}
</style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <h1 class="text-xl font-bold text-gray-800">Admin Dashboard</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="admin_logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md h-screen">
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="?page=dashboard" class="flex items-center p-2 hover:bg-gray-100 rounded">
                            <i class="fas fa-dashboard mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="?page=students" class="flex items-center p-2 hover:bg-gray-100 rounded">
                            <i class="fas fa-users mr-2"></i> Student Details
                        </a>
                    </li>
                  <!--   <li>
                        <a href="?page=access_control" class="flex items-center p-2 hover:bg-gray-100 rounded">
                            <i class="fas fa-key mr-2"></i> Access Control
                        </a>
                    </li>  -->
                    <li>
                        <a href="?page=pc_usage" class="flex items-center p-2 hover:bg-gray-100 rounded">
                            <i class="fas fa-desktop mr-2"></i> PC Usage
                        </a>
                    </li>
                    <li>
                        <a href="?page=reservations" class="flex items-center p-2 hover:bg-gray-100 rounded">
                            <i class="fas fa-calendar mr-2"></i> Reservations
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Content Area -->
        <main class="flex-1 p-8">
            <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
            $file_path = "";
            
            switch($page) {
                case 'dashboard':
                    $file_path = "./admin/dashboard_home.php";
                    break;
                case 'students':
                    $file_path = "./admin/student_management.php";
                    break;
                case 'access_control':
                    $file_path = "./admin/access_control.php";
                    break;
                case 'pc_usage':
                    $file_path = "./admin/pc_usage.php";
                    break;
                case 'reservations':
                    $file_path = "./admin/reservations.php";
                    break;
                default:
                    $file_path = "./admin/dashboard_home.php";
            }

            if(file_exists($file_path)) {
                include $file_path;
            } else {
                echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>";
                echo "<p>Error: Page not found. Please make sure all required files are in place.</p>";
                echo "</div>";
            }
            ?>
        </main>
    </div>

    <!-- Animated Refresh Button -->
<div class="refresh-btn" onclick="refreshPage()" id="refreshButton">
    <span class="refresh-tooltip">Refresh Page</span>
    <svg class="refresh-icon" viewBox="0 0 24 24">
        <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
    </svg>
</div>

<!-- Last Refresh Time Display -->
<div id="lastRefresh" class="fixed bottom-4 left-4 text-sm text-gray-600">
    Last refreshed: <span id="refreshTime"></span>
</div>

<script>
// Refresh functionality
function refreshPage() {
    const refreshBtn = document.getElementById('refreshButton');
    const refreshIcon = refreshBtn.querySelector('.refresh-icon');
    
    // Add clicked animation
    refreshBtn.classList.add('clicked');
    
    // Add spinning animation to icon
    refreshIcon.style.animation = 'spin 1s linear infinite';
    
    // Show loading state
    showToast('Refreshing page...', 'info');
    
    // Refresh the page after animation
    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Update last refresh time
function updateLastRefreshTime() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
    document.getElementById('refreshTime').textContent = timeStr;
}

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 p-4 rounded-lg text-white ${
        type === 'info' ? 'bg-blue-500' : 
        type === 'success' ? 'bg-green-500' : 
        'bg-red-500'
    } transition-opacity duration-300`;
    toast.style.zIndex = '1001';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Fade in
    setTimeout(() => {
        toast.style.opacity = '1';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Initialize last refresh time on page load
document.addEventListener('DOMContentLoaded', () => {
    updateLastRefreshTime();
});

// Add auto-refresh every 5 minutes (300000 milliseconds)
setInterval(() => {
    refreshPage();
}, 300000);

</script>
</body>
</html>
