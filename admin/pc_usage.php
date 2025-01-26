<!-- admin/pc_usage.php -->
<?php

// Fix the path to config.php
require_once dirname(__FILE__) . '/../config.php';

// Check admin login
if(!isset($_SESSION['admin_loggedin'])) {
    header("Location: ../admin_login.php");
    exit;
}


if(!isset($_SESSION['admin_loggedin'])) {
    header("Location: ../admin_login.php");
    exit;
}

// Get selected date (default to today if not set)
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use correct path to config file
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config.php';

// Verify admin login
if(!isset($_SESSION['admin_loggedin'])) {
    header("Location: ../admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC Usage Management - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Add DatePicker and Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Date Filter Section -->
        <div class="mb-6 bg-white p-4 rounded-lg shadow-md">
            <form method="GET" class="flex items-center space-x-4">
                <label class="font-semibold">Select Date:</label>
                <input type="date" name="date" id="datePicker" value="<?php echo $selected_date; ?>" 
                       class="border rounded px-3 py-2">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Filter
                </button>
            </form>
        </div>

        <!-- PC Usage Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <?php
            // Get usage statistics for selected date
            $stats_query = "SELECT 
                COUNT(DISTINCT r.student_id) as active_users,
                COUNT(DISTINCT r.pc_number) as pcs_in_use,
                COUNT(*) as total_sessions
                FROM pc_reservations r
                WHERE DATE(r.start_time) = ? AND (r.end_time IS NULL OR DATE(r.end_time) = ?)";
            
            $stmt = mysqli_prepare($conn, $stats_query);
            mysqli_stmt_bind_param($stmt, "ss", $selected_date, $selected_date);
            mysqli_stmt_execute($stmt);
            $stats_result = mysqli_stmt_get_result($stmt);
            $stats = mysqli_fetch_assoc($stats_result);
            ?>
            
            <!-- Statistics Cards -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-2">Active Users</h3>
                <p class="text-3xl font-bold text-blue-500"><?php echo $stats['active_users']; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-2">PCs in Use</h3>
                <p class="text-3xl font-bold text-green-500"><?php echo $stats['pcs_in_use']; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-2">Total Sessions</h3>
                <p class="text-3xl font-bold text-purple-500"><?php echo $stats['total_sessions']; ?></p>
            </div>
        </div>

        <!-- PC Status Grid -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">PC Status Overview</h2>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-4">
                <?php
                // Get current PC usage status
                $pc_query = "SELECT 
                    r.pc_number,
                    r.id as reservation_id,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    r.start_time,
                    TIMESTAMPDIFF(MINUTE, r.start_time, COALESCE(r.end_time, NOW())) as duration
                    FROM pc_reservations r
                    LEFT JOIN students s ON r.student_id = s.id
                    WHERE DATE(r.start_time) = ? AND r.end_time IS NULL
                    ORDER BY r.pc_number";
                
                $stmt = mysqli_prepare($conn, $pc_query);
                mysqli_stmt_bind_param($stmt, "s", $selected_date);
                mysqli_stmt_execute($stmt);
                $pc_result = mysqli_stmt_get_result($stmt);
                
                $active_pcs = array();
                while ($row = mysqli_fetch_assoc($pc_result)) {
                    $active_pcs[$row['pc_number']] = $row;
                }

                // Display PC grid
                for ($i = 1; $i <= 30; $i++) {
                    $is_active = isset($active_pcs[$i]);
                    $pc_data = $is_active ? $active_pcs[$i] : null;
                    ?>
                    <div class="relative group">
                        <button 
                            class="w-full p-4 rounded-lg text-white text-center <?php echo $is_active ? 'bg-red-500' : 'bg-green-500'; ?>"
                            onclick="<?php echo $is_active ? 'showPCDetails('.$i.')' : 'startSession('.$i.')'; ?>"
                        >
                            PC <?php echo $i; ?>
                        </button>
                        <?php if ($is_active): ?>
                        <div class="hidden group-hover:block absolute z-10 w-48 bg-white rounded-md shadow-lg p-4 top-full left-0 mt-2">
                            <p class="font-semibold"><?php echo htmlspecialchars($pc_data['student_name']); ?></p>
                            <p class="text-sm text-gray-600">Started: <?php echo date('H:i', strtotime($pc_data['start_time'])); ?></p>
                            <p class="text-sm text-gray-600">Duration: <?php echo floor($pc_data['duration']/60).'h '.($pc_data['duration']%60).'m'; ?></p>
                            <button 
                                onclick="endSession(<?php echo $pc_data['reservation_id']; ?>)"
                                class="mt-2 w-full bg-red-500 text-white px-2 py-1 rounded text-sm"
                            >
                                End Session
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Active Sessions Table -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Active Sessions</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2 text-left">PC Number</th>
                            <th class="px-4 py-2 text-left">Student Name</th>
                            <th class="px-4 py-2 text-left">Registration No.</th>
                            <th class="px-4 py-2 text-left">Start Time</th>
                            <th class="px-4 py-2 text-left">Duration</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $active_sessions_query = "SELECT 
                            r.id as reservation_id,
                            r.pc_number,
                            CONCAT(s.first_name, ' ', s.last_name) as student_name,
                            s.registration_number,
                            r.start_time,
                            TIMESTAMPDIFF(MINUTE, r.start_time, NOW()) as duration
                            FROM pc_reservations r
                            JOIN students s ON r.student_id = s.id
                            WHERE DATE(r.start_time) = ? AND r.end_time IS NULL
                            ORDER BY r.start_time DESC";
                        
                        $stmt = mysqli_prepare($conn, $active_sessions_query);
                        mysqli_stmt_bind_param($stmt, "s", $selected_date);
                        mysqli_stmt_execute($stmt);
                        $active_sessions = mysqli_stmt_get_result($stmt);

                        while ($session = mysqli_fetch_assoc($active_sessions)) {
                            echo "<tr class='border-t'>";
                            echo "<td class='px-4 py-2'>PC " . htmlspecialchars($session['pc_number']) . "</td>";
                            echo "<td class='px-4 py-2'>" . htmlspecialchars($session['student_name']) . "</td>";
                            echo "<td class='px-4 py-2'>" . htmlspecialchars($session['registration_number']) . "</td>";
                            echo "<td class='px-4 py-2'>" . date('H:i', strtotime($session['start_time'])) . "</td>";
                            echo "<td class='px-4 py-2'>" . floor($session['duration']/60).'h '.($session['duration']%60).'m' . "</td>";
                            echo "<td class='px-4 py-2'>
                                    <button onclick='endSession(".$session['reservation_id'].")' 
                                            class='bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600'>
                                        End Session
                                    </button>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Initialize date picker
        flatpickr("#datePicker", {
            defaultDate: "<?php echo $selected_date; ?>",
            dateFormat: "Y-m-d"
        });

        // Function to start a new session
        function startSession(pcNumber) {
            const studentId = prompt("Enter student registration number:");
            if (!studentId) return;

            fetch('start_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `pc_number=${pcNumber}&student_id=${studentId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while starting the session.');
            });
        }

        // Function to end a session
        function endSession(reservationId) {
            if (!confirm('Are you sure you want to end this session?')) return;

            fetch('end_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `reservation_id=${reservationId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while ending the session.');
            });
        }

        // Function to show PC details
        function showPCDetails(pcNumber) {
            const detailsEl = document.querySelector(`#pc-details-${pcNumber}`);
            if (detailsEl) {
                detailsEl.classList.toggle('hidden');
            }
        }
    </script>
</body>
</html>
