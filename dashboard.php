<?php

require_once 'check_session.php';
require_once 'config.php';
check_login();

$student_id = get_student_id();

// Initialize variables
$student = null;
$active_session = null;
$reservation_history = [];

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch student details with error checking
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = :student_id");
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        // Handle case where student is not found
        header('Location: logout.php');
        exit();
    }

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


// Calculate total usage hours
$total_usage_query = "
    SELECT 
        SUM(TIMESTAMPDIFF(MINUTE, start_time, COALESCE(end_time, CURRENT_TIMESTAMP))) as total_minutes,
        COUNT(DISTINCT pc_number) as computers_used,
        COUNT(*) as total_sessions
    FROM pc_reservations 
    WHERE student_id = :student_id";

try {
    $stmt = $conn->prepare($total_usage_query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
    $usage_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Convert minutes to hours and remaining minutes
    $total_hours = floor($usage_stats['total_minutes'] / 60);
    $remaining_minutes = $usage_stats['total_minutes'] % 60;
} catch(PDOException $e) {
    $usage_stats = ['total_minutes' => 0, 'computers_used' => 0, 'total_sessions' => 0];
    $total_hours = 0;
    $remaining_minutes = 0;
}





try {
    $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check current time
    $currentTime = new DateTime(); // Current server time
    $endTime = new DateTime('17:30:00'); // 5:30 PM

    // If current time is past 5:30 PM, end active sessions
    if ($currentTime > $endTime) {
        $stmt = $conn->prepare("
            UPDATE pc_reservations
            SET end_time = CURRENT_TIMESTAMP, status = 'inactive'
            WHERE status = 'active'
        ");
        $stmt->execute();
    }
} catch (PDOException $e) {
    die("Error ending sessions: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link href="tailwind.min.css" rel="stylesheet">
    <style>
        /* Add this to your CSS */
:root {
  --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  --hover-gradient: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
  --card-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.bg-white {
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.bg-white:hover {
  transform: translateY(-5px);
  box-shadow: var(--card-shadow);
}

/* Animated PC buttons */
button {
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

button:after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: rgba(255,255,255,0.2);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

button:hover:after {
  width: 200%;
  height: 200%;
}

/* Add animated background */
body {
    background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
    background-size: 400% 400%;
    animation: gradient 15s ease infinite;
}

@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Add these styles to your CSS */
#currentDate, #currentTime {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#timeZone {
    background: #f3f4f6;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

/* Add animation */
@keyframes timeUpdate {
    0% { opacity: 1; }
    50% { opacity: 0.8; }
    100% { opacity: 1; }
}

#currentTime {
    animation: timeUpdate 1s infinite;
}

/* Animated Refresh Button Styles */
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


/* Modern Notification Styles */
.notification-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.student-notification {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: translateX(120%);
    animation: slideIn 0.5s forwards;
    max-width: 400px;
    border-left: 4px solid #4f46e5;
}

.notification-header {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.notification-icon {
    width: 24px;
    height: 24px;
    margin-right: 12px;
}

.notification-title {
    font-weight: 600;
    color: #1f2937;
}

.notification-message {
    color: #4b5563;
    font-size: 0.95rem;
    line-height: 1.5;
}

@keyframes slideIn {
    to {
        transform: translateX(0);
    }
}

@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

/* Modal Styles */
.student-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    z-index: 1000;
    max-width: 500px;
    width: 90%;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    z-index: 999;
}

    /* Button Styles */
    .action-button {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .action-button::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.3s ease, height 0.3s ease;
    }

    .action-button:hover::after {
        width: 200%;
        height: 200%;
    }

    /* End Session Button */
    .end-session-btn {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
    }

    .end-session-btn:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
    }

    /* Logout Button */
    .logout-btn {
        background: linear-gradient(135deg,rgb(220, 18, 62) 0%,rgb(220, 77, 11) 100%);
        color: white;
        box-shadow: 0 4px 6px -1px rgba(75, 85, 99, 0.2);
    }

    .logout-btn:hover {
        background: linear-gradient(135deg,rgb(223, 4, 41) 0%,rgb(234, 192, 25) 100%);
        transform: translateY(-2px);
    }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        
        <!-- Header with Logout -->
        <div class="flex justify-between items-center mb-6">
        <img src="src/img/esoft-logo.png" alt="ESOFT Logo" class="h-10 mr-3">
            <h1 class="text-2xl font-bold">Student Dashboard</h1>
            <button onclick="logout()" class="action-button logout-btn" data-action="logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
            <i class="fas fa-sign-out-alt mr-2">   Logout </i>
            </button>
        </div>
        <div class="flex justify-between items-center">
        <div class="text-lg">
            <span id="currentDate" class="font-semibold mr-4"></span>
            <span id="currentTime" class="font-semibold"></span>
        </div>
        <div class="text-sm text-gray-600" id="timeZone"></div>
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

        <!-- Profile Information -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Profile Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="font-semibold">Name:</p>
                    <p><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                </div>
                <div>
                    <p class="font-semibold">Registration Number:</p>
                    <p><?php echo htmlspecialchars($student['registration_number']); ?></p>
                </div>
                <div>
                    <p class="font-semibold">Batch:</p>
                    <p><?php echo htmlspecialchars($student['batch_year'] . ' (' . $student['batch_name'] . ')'); ?></p>
                </div>
                <div>
                    <p class="font-semibold">Email:</p>
                    <p><?php echo htmlspecialchars($student['email']); ?></p>
                </div>
                <div>
                    <p class="font-semibold">Contact:</p>
                    <p><?php echo htmlspecialchars($student['contact_number'] ?? 'Not provided'); ?></p>
                </div>
                <div>
                    <p class="font-semibold">Gender:</p>
                    <p><?php echo htmlspecialchars($student['gender']); ?></p>
                </div>
            </div>
        </div>


        
        <!-- Active Session -->
        <?php
        try {
            $active_stmt = $conn->prepare("
                SELECT *, 
                TIMESTAMPDIFF(MINUTE, start_time, CURRENT_TIMESTAMP) as current_duration 
                FROM pc_reservations 
                WHERE student_id = :student_id 
                AND status = 'active'
                LIMIT 1
            ");
            $active_stmt->bindParam(':student_id', $student_id);
            $active_stmt->execute();
            $active_session = $active_stmt->fetch(PDO::FETCH_ASSOC);

            if ($active_session): ?>
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Active Session</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="font-semibold">PC Number:</p>
                        <p>PC <?php echo htmlspecialchars($active_session['pc_number']); ?></p>
                    </div>
                    <div>
                        <p class="font-semibold">Start Time:</p>
                        <p><?php echo date('Y-m-d H:i:s', strtotime($active_session['start_time'])); ?></p>
                    </div>
                    <div>
                        <p class="font-semibold">Duration:</p>
                        <p><?php echo floor($active_session['current_duration'] / 60) . 'h ' . 
                            ($active_session['current_duration'] % 60) . 'm'; ?></p>
                    </div>
                    <div>
                    
                        <button onclick="endSession()" class="action-button end-session-btn" data-action="end-session"  class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-stop-circle mr-2"> End Session </i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endif;

            // Fetch reservation history
            $history_stmt = $conn->prepare("
                SELECT *,
                CASE 
                    WHEN end_time IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, start_time, end_time)
                    ELSE TIMESTAMPDIFF(MINUTE, start_time, CURRENT_TIMESTAMP)
                END as duration_mins
                FROM pc_reservations 
                WHERE student_id = :student_id 
                ORDER BY start_time DESC
            ");
            $history_stmt->bindParam(':student_id', $student_id);
            $history_stmt->execute();
            $reservation_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "<div class='text-red-500'>Error loading session data: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>

        <!-- Available PCs -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Available PCs</h2>
            <div class="grid grid-cols-4 md:grid-cols-8 gap-4">
                <?php
                // Get occupied PCs
                $occupied_stmt = $conn->prepare("
                    SELECT pc_number FROM pc_reservations WHERE status = 'active'
                ");
                $occupied_stmt->execute();
                $occupied_pcs = $occupied_stmt->fetchAll(PDO::FETCH_COLUMN);

                // Display PC buttons
                for($i = 1; $i <= 30; $i++) {
                    $is_occupied = in_array($i, $occupied_pcs);
                    $is_my_pc = $active_session && $active_session['pc_number'] == $i;
                    $button_class = $is_occupied ? 'bg-red-500' : ($is_my_pc ? 'bg-blue-500' : 'bg-green-500 hover:bg-green-600');
                    $disabled = $is_occupied || $active_session ? 'disabled' : '';
                ?>
                    <button 
                        onclick="reservePC(<?php echo $i; ?>)"
                        class="p-2 text-white rounded <?php echo $button_class; ?>"
                        <?php echo $disabled; ?>
                    >
                        PC <?php echo $i; ?>
                    </button>
                <?php } ?>
            </div>
        </div>


        <!-- Usage Statistics -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Lab Usage Statistics</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-700">Total Usage Time</h3>
            <p class="text-2xl font-bold text-indigo-600">
                <?php echo $total_hours . 'h ' . $remaining_minutes . 'm'; ?>
            </p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-700">Different PCs Used</h3>
            <p class="text-2xl font-bold text-indigo-600">
                <?php echo $usage_stats['computers_used']; ?>
            </p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-700">Total Sessions</h3>
            <p class="text-2xl font-bold text-indigo-600">
                <?php echo $usage_stats['total_sessions']; ?>
            </p>
        </div>
    </div>

    <!-- Monthly Usage Chart -->
    <?php
    // Get monthly usage statistics
    $monthly_usage_query = "
        SELECT 
            DATE_FORMAT(start_time, '%Y-%m') as month,
            SUM(TIMESTAMPDIFF(MINUTE, start_time, COALESCE(end_time, CURRENT_TIMESTAMP))) as minutes
        FROM pc_reservations 
        WHERE student_id = :student_id
        AND start_time >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(start_time, '%Y-%m')
        ORDER BY month ASC";

    try {
        $stmt = $conn->prepare($monthly_usage_query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $monthly_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $monthly_stats = [];
    }
    ?>

    <div class="mt-6">
        <h3 class="text-lg font-semibold mb-4">Monthly Usage Trend</h3>
        <div class="h-64 bg-gray-50 p-4 rounded-lg">
            <?php foreach ($monthly_stats as $stat): ?>
                <div class="flex items-center mb-2">
                    <div class="w-24">
                        <?php echo date('M Y', strtotime($stat['month'])); ?>
                    </div>
                    <div class="flex-1">
                        <div class="bg-indigo-200 h-6 rounded-full">
                            <div 
                                class="bg-indigo-600 h-6 rounded-full" 
                                style="width: <?php echo min(($stat['minutes'] / ($usage_stats['total_minutes'] ?: 1)) * 100, 100); ?>%">
                            </div>
                        </div>
                    </div>
                    <div class="w-24 text-right">
                        <?php echo floor($stat['minutes'] / 60) . 'h ' . ($stat['minutes'] % 60) . 'm'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


        <!-- Reservation History -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold mb-4">Reservation History</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">PC Number</th>
                            <th class="px-4 py-2 text-left">Start Time</th>
                            <th class="px-4 py-2 text-left">End Time</th>
                            <th class="px-4 py-2 text-left">Duration</th>
                            <th class="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservation_history as $reservation): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?php echo date('Y-m-d', strtotime($reservation['start_time'])); ?></td>
                            <td class="px-4 py-2">PC <?php echo htmlspecialchars($reservation['pc_number']); ?></td>
                            <td class="px-4 py-2"><?php echo date('H:i:s', strtotime($reservation['start_time'])); ?></td>
                            <td class="px-4 py-2">
                                <?php echo $reservation['end_time'] ? date('H:i:s', strtotime($reservation['end_time'])) : 'Active'; ?>
                            </td>
                            <td class="px-4 py-2">
                                <?php 
                                $duration = $reservation['duration_mins'];
                                echo floor($duration / 60) . 'h ' . ($duration % 60) . 'm';
                                ?>
                            </td>
                            <td class="px-4 py-2"><?php echo ucfirst($reservation['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>

class StudentNotificationSystem {
    constructor() {
        this.container = this.createContainer();
        this.notifications = [];
    }

    createContainer() {
        const container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
        return container;
    }

    show(options) {
        const {
            title,
            message,
            type = 'info',
            duration = 5000,
            icon = this.getIconForType(type)
        } = options;

        const notification = document.createElement('div');
        notification.className = 'student-notification';
        notification.innerHTML = `
            <div class="notification-header">
                <img src="${icon}" class="notification-icon" alt="${type}">
                <div class="notification-title">${title}</div>
            </div>
            <div class="notification-message">${message}</div>
        `;

        this.container.appendChild(notification);
        this.notifications.push(notification);

        setTimeout(() => {
            notification.style.animation = 'fadeOut 0.5s forwards';
            setTimeout(() => {
                notification.remove();
                this.notifications = this.notifications.filter(n => n !== notification);
            }, 500);
        }, duration);

        return notification;
    }

    getIconForType(type) {
        const icons = {
            success: 'admin/ico/success.png',
            error: 'admin/ico/error.png',
            info: 'admin/ico/info.png',
            warning: 'admin/ico/warn.png'
        };
        return icons[type] || icons.info;
    }

    showConfirm(options) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'student-modal';
            modal.innerHTML = `
                <h3 class="text-xl font-bold mb-4">${options.title}</h3>
                <p class="text-gray-600 mb-6">${options.message}</p>
                <div class="flex justify-end space-x-4">
                    <button class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors" 
                            onclick="this.closest('.student-modal').remove();
                                    document.querySelector('.modal-overlay').remove();">
                        Cancel
                    </button>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                            onclick="this.closest('.student-modal').remove();
                                    document.querySelector('.modal-overlay').remove();">
                        Confirm
                    </button>
                </div>
            `;

            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';

            document.body.appendChild(overlay);
            document.body.appendChild(modal);

            const confirmBtn = modal.querySelector('.bg-indigo-600');
            const cancelBtn = modal.querySelector('.bg-gray-200');

            confirmBtn.onclick = () => {
                modal.remove();
                overlay.remove();
                resolve(true);
            };

            cancelBtn.onclick = () => {
                modal.remove();
                overlay.remove();
                resolve(false);
            };
        });
    }
}

// Initialize the notification system
const studentNotify = new StudentNotificationSystem();

// Replace the existing reservePC function
async function reservePC(pcNumber) {
    const confirmed = await studentNotify.showConfirm({
        title: 'Reserve PC',
        message: `Would you like to reserve PC ${pcNumber}? This PC will be reserved for your use for the next 2 hours.`
    });

    if (!confirmed) return;

    studentNotify.show({
        title: 'Processing',
        message: 'Reserving your PC...',
        type: 'info',
        duration: 2000
    });

    try {
        const response = await fetch('reserve_pc.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `pc_number=${pcNumber}`
        });

        const data = await response.json();

        if (data.status === 'success') {
            studentNotify.show({
                title: 'Success!',
                message: `PC ${pcNumber} has been reserved for you. Your session starts now!`,
                type: 'success',
                duration: 5000
            });
            
            // Start session timer
            startSessionTimer(pcNumber);
            
            // Refresh the UI
            setTimeout(() => location.reload(), 2000);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        studentNotify.show({
            title: 'Reservation Failed',
            message: error.message || 'An error occurred while reserving the PC.',
            type: 'error',
            duration: 5000
        });
    }
}

// End Session Button Functionality
async function endSession() {
    // Show confirmation dialog
    const confirmed = await studentNotify.showConfirm({
        title: 'End Session',
        message: 'Are you sure you want to end your current session? This action cannot be undone.',
        type: 'warning'
    });

    if (!confirmed) return;

    // Show loading notification
    const loadingNotification = studentNotify.show({
        title: 'Processing',
        message: 'Ending your session...',
        type: 'info',
        duration: 2000
    });

    try {
        const response = await fetch('end_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        const data = await response.json();

        if (data.status === 'success') {
            // Show success message
            studentNotify.show({
                title: 'Session Ended',
                message: 'Your session has been successfully ended. Thank you for using our facility!',
                type: 'success',
                duration: 3000
            });

            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            throw new Error(data.message || 'Failed to end session');
        }
    } catch (error) {
        // Show error message
        studentNotify.show({
            title: 'Error',
            message: 'Failed to end session: ' + error.message,
            type: 'error',
            duration: 5000
        });
    }
}

// Logout Button Functionality
async function logout() {
    // Show confirmation dialog
    const confirmed = await studentNotify.showConfirm({
        title: 'Logout Confirmation',
        message: 'Are you sure you want to logout? Any active session will be ended.',
        type: 'warning'
    });

    if (!confirmed) return;

    // Check if there's an active session
    if (document.querySelector('[data-active-session="true"]')) {
        // Show warning about active session
        studentNotify.show({
            title: 'Active Session',
            message: 'Ending your active session before logout...',
            type: 'warning',
            duration: 3000
        });

        // End active session first
        try {
            await endSession();
        } catch (error) {
            console.error('Error ending session during logout:', error);
        }
    }

    // Show logging out message
    studentNotify.show({
        title: 'Logging Out',
        message: 'Please wait while we log you out...',
        type: 'info',
        duration: 2000
    });

    // Perform logout after short delay
    setTimeout(() => {
        window.location.href = 'logout.php';
    }, 1500);
}

// Add event listeners to buttons
document.addEventListener('DOMContentLoaded', () => {
    // End Session Button
    const endSessionBtn = document.querySelector('[data-action="end-session"]');
    if (endSessionBtn) {
        endSessionBtn.addEventListener('click', endSession);
    }

    // Logout Button
    const logoutBtn = document.querySelector('[data-action="logout"]');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }

    // Add keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Alt + E for End Session
        if (e.altKey && e.key === 'e' && endSessionBtn) {
            e.preventDefault();
            endSession();
        }
        // Alt + L for Logout
        if (e.altKey && e.key === 'l' && logoutBtn) {
            e.preventDefault();
            logout();
        }
    });
});

// Add smooth animations for status changes
function updatePCStatus(pcNumber, status) {
    const pcButton = document.querySelector(`button[onclick="reservePC(${pcNumber})"]`);
    pcButton.style.animation = 'statusChange 0.5s ease';
}


// Add WebSocket connection for real-time updates
const ws = new WebSocket('ws://your-server/websocket');

ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    if (data.type === 'pc_status_update') {
        updatePCStatus(data.pcNumber, data.status);
    }
};
// Add live timer for active sessions
function updateSessionTimer() {
    const durationElement = document.querySelector('.session-duration');
    if (durationElement) {
        let minutes = parseInt(durationElement.dataset.minutes);
        setInterval(() => {
            minutes++;
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            durationElement.textContent = `${hours}h ${mins}m`;
        }, 60000);
    }
}

// Replace simple alerts with toast notifications
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Add hover effects for PC status information
function initializePCGrid() {
    const pcButtons = document.querySelectorAll('.grid button');
    pcButtons.forEach(button => {
        button.addEventListener('mouseover', (e) => {
            const pcStats = getPCStats(button.dataset.pcNumber);
            showPCTooltip(e, pcStats);
        });
    });
}

function showPCTooltip(event, stats) {
    const tooltip = document.createElement('div');
    tooltip.className = 'absolute bg-black text-white p-2 rounded text-sm';
    tooltip.style.left = `${event.pageX + 10}px`;
    tooltip.style.top = `${event.pageY + 10}px`;
    tooltip.innerHTML = `
        <div>Usage Today: ${stats.usageToday}</div>
        <div>Last User: ${stats.lastUser}</div>
        <div>Status: ${stats.status}</div>
    `;
    document.body.appendChild(tooltip);
}

function startSessionTimer(pcNumber) {
    let timeLeft = 120; // 2 hours in minutes
    
    const interval = setInterval(() => {
        timeLeft--;
        
        if (timeLeft === 30) {
            studentNotify.show({
                title: 'Session Ending Soon',
                message: '30 minutes remaining in your session. Please save your work.',
                type: 'warning',
                duration: 10000
            });
        } else if (timeLeft === 10) {
            studentNotify.show({
                title: 'Warning',
                message: '10 minutes remaining! Make sure to save all your work.',
                type: 'warning',
                duration: 10000
            });
        } else if (timeLeft === 5) {
            studentNotify.show({
                title: 'Final Warning',
                message: 'Session ends in 5 minutes! Please prepare to finish.',
                type: 'error',
                duration: 10000
            });
        } else if (timeLeft === 0) {
            clearInterval(interval);
            endSession();
        }
    }, 60000);
}


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

// Function to update date and time
function updateDateTime() {
    const now = new Date();
    
    // Format date
    const dateOptions = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    const dateStr = now.toLocaleDateString('en-US', dateOptions);
    
    // Format time
    const timeOptions = { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit', 
        hour12: true 
    };
    const timeStr = now.toLocaleTimeString('en-US', timeOptions);
    
    // Get timezone
    const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    
    // Update DOM elements
    document.getElementById('currentDate').textContent = dateStr;
    document.getElementById('currentTime').textContent = timeStr;
    document.getElementById('timeZone').textContent = timeZone;
}

// Update immediately and then every second
updateDateTime();
setInterval(updateDateTime, 1000);


    </script>
</body>
</html>
