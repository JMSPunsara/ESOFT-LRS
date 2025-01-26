<!-- admin/dashboard_home.php -->
<?php
if(!isset($_SESSION['admin_loggedin'])) {
    header("Location: ../admin_login.php");
    exit;
}

// Get selected date (default to today if not set)
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Add DatePicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body class="bg-gray-100">

<div class="container mx-auto px-4 py-8">
    <!-- Date Filter Section -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow-md">
        <form method="GET" class="flex items-center space-x-4">
            <label class="font-semibold">Select Date:</label>
            <input type="date" name="date" id="datePicker" value="<?php echo $selected_date; ?>" 
                   class="border rounded px-3 py-2">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                Filter
            </button>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Students -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold mb-2">Total Students</h3>
            <?php
            $query = "SELECT COUNT(*) as total FROM students";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            ?>
            <p class="text-3xl font-bold text-blue-500"><?php echo $row['total']; ?></p>
        </div>

        <!-- Active Users -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold mb-2">Active Users</h3>
            <?php
            $query = "SELECT COUNT(*) as total FROM login_logs 
                      WHERE DATE(login_time) = '$selected_date' 
                      AND logout_time IS NULL";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            ?>
            <p class="text-3xl font-bold text-green-500"><?php echo $row['total']; ?></p>
        </div>

        <!-- Today's Reservations -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold mb-2">Today's Reservations</h3>
            <?php
            $query = "SELECT COUNT(*) as total FROM pc_reservations 
                      WHERE DATE(start_time) = '$selected_date'";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            ?>
            <p class="text-3xl font-bold text-yellow-500"><?php echo $row['total']; ?></p>
        </div>

        <!-- Total Usage Today -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold mb-2">Total Usage Today</h3>
            <?php
            $query = "SELECT COUNT(*) as total FROM login_logs 
                      WHERE DATE(login_time) = '$selected_date'";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            ?>
            <p class="text-3xl font-bold text-purple-500"><?php echo $row['total']; ?></p>
        </div>
    </div>

    <!-- Recent System Usage Table -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h3 class="text-xl font-bold mb-4">System Usage for <?php echo date('F d, Y', strtotime($selected_date)); ?></h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left">Student Name</th>
                        <th class="px-4 py-2 text-left">Registration No.</th>
                        <th class="px-4 py-2 text-left">Batch</th>
                        <th class="px-4 py-2 text-left">PC Number</th>
                        <th class="px-4 py-2 text-left">Login Time</th>
                        <th class="px-4 py-2 text-left">Logout Time</th>
                        <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT 
                                CONCAT(s.first_name, ' ', s.last_name) as full_name,
                                s.registration_number,
                                s.batch_year,
                                s.batch_name,
                                l.login_time,
                                l.logout_time
                             FROM login_logs l
                             JOIN students s ON l.student_id = s.id
                             WHERE DATE(l.login_time) = '$selected_date'
                             ORDER BY l.login_time DESC";
                    
                    $result = mysqli_query($conn, $query);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $status = is_null($row['logout_time']) ? 'Active' : 'Completed';
                            $statusColor = is_null($row['logout_time']) ? 'text-green-600' : 'text-gray-600';
                            
                            echo "<tr class='border-t'>";
                            echo "<td class='px-4 py-2'>" . htmlspecialchars($row['full_name']) . "</td>";
                            echo "<td class='px-4 py-2'>" . htmlspecialchars($row['registration_number']) . "</td>";
                            echo "<td class='px-4 py-2'>" . htmlspecialchars($row['batch_year'] . ' - ' . $row['batch_name']) . "</td>";
                            echo "<td class='px-4 py-2'>PC" . rand(1, 30) . "</td>";
                            echo "<td class='px-4 py-2'>" . date('H:i:s', strtotime($row['login_time'])) . "</td>";
                            echo "<td class='px-4 py-2'>" . ($row['logout_time'] ? date('H:i:s', strtotime($row['logout_time'])) : '-') . "</td>";
                            echo "<td class='px-4 py-2 {$statusColor} font-semibold'>" . $status . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='px-4 py-2 text-center'>No system usage records found for this date</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- Reservations Table -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-bold mb-4">PC Reservations for <?php echo date('F d, Y', strtotime($selected_date)); ?></h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-2 text-left">Student Name</th>
                    <th class="px-4 py-2 text-left">Registration No.</th>
                    <th class="px-4 py-2 text-left">Batch</th>
                    <th class="px-4 py-2 text-left">PC Number</th>
                    <th class="px-4 py-2 text-left">Start Time</th>
                    <th class="px-4 py-2 text-left">End Time</th>
                    <th class="px-4 py-2 text-left">Duration (mins)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT 
                            CONCAT(s.first_name, ' ', s.last_name) as full_name,
                            s.registration_number,
                            s.batch_year,
                            s.batch_name,
                            r.pc_number,
                            r.start_time,
                            r.end_time,
                            TIMESTAMPDIFF(MINUTE, r.start_time, r.end_time) as duration
                         FROM pc_reservations r
                         JOIN students s ON r.student_id = s.id
                         WHERE DATE(r.start_time) = '$selected_date'
                         ORDER BY r.start_time DESC";
                
                $result = mysqli_query($conn, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<tr class='border-t'>";
                        echo "<td class='px-4 py-2'>" . htmlspecialchars($row['full_name']) . "</td>";
                        echo "<td class='px-4 py-2'>" . htmlspecialchars($row['registration_number']) . "</td>";
                        echo "<td class='px-4 py-2'>" . htmlspecialchars($row['batch_year'] . ' - ' . $row['batch_name']) . "</td>";
                        echo "<td class='px-4 py-2'>" . htmlspecialchars($row['pc_number']) . "</td>";
                        echo "<td class='px-4 py-2'>" . date('h:i A', strtotime($row['start_time'])) . "</td>";
                        echo "<td class='px-4 py-2'>" . date('h:i A', strtotime($row['end_time'])) . "</td>";
                        echo "<td class='px-4 py-2'>" . $row['duration'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='px-4 py-2 text-center'>No reservations found for this date</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add DatePicker JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#datePicker", {
        dateFormat: "Y-m-d",
        defaultDate: "<?php echo $selected_date; ?>",
    });
</script>

</body>
</html>
