<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/../config.php';

// Check admin login
if(!isset($_SESSION['admin_loggedin'])) {
    header("Location: ../admin_login.php");
    exit;
}

// Initialize search parameters
$search = '';
$batch_year = '';
$batch_name = '';
$start_date = date('Y-m-d', strtotime('-30 days'));
$end_date = date('Y-m-d');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        // Handle reset
        unset($_SESSION['reservation_search']);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        // Handle search
        $search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';
        $batch_year = isset($_POST['batch_year']) ? mysqli_real_escape_string($conn, $_POST['batch_year']) : '';
        $batch_name = isset($_POST['batch_name']) ? mysqli_real_escape_string($conn, $_POST['batch_name']) : '';
        $start_date = isset($_POST['start_date']) ? mysqli_real_escape_string($conn, $_POST['start_date']) : date('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_POST['end_date']) ? mysqli_real_escape_string($conn, $_POST['end_date']) : date('Y-m-d');

        // Store in session
        $_SESSION['reservation_search'] = [
            'search' => $search,
            'batch_year' => $batch_year,
            'batch_name' => $batch_name,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
    }
}

// Retrieve from session if exists
if (isset($_SESSION['reservation_search'])) {
    $search = $_SESSION['reservation_search']['search'];
    $batch_year = $_SESSION['reservation_search']['batch_year'];
    $batch_name = $_SESSION['reservation_search']['batch_name'];
    $start_date = $_SESSION['reservation_search']['start_date'];
    $end_date = $_SESSION['reservation_search']['end_date'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation History - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">

        <!-- Results Table -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <table id="reservationTable" class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left">Student Name</th>
                        <th class="px-4 py-2 text-left">Reg. Number</th>
                        <th class="px-4 py-2 text-left">Batch</th>
                        <th class="px-4 py-2 text-left">PC Number</th>
                        <th class="px-4 py-2 text-left">Start Time</th>
                        <th class="px-4 py-2 text-left">End Time</th>
                        <th class="px-4 py-2 text-left">Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Build the query with prepared statements
                    $query = "SELECT 
                                r.*, 
                                s.first_name,
                                s.last_name,
                                s.registration_number,
                                s.batch_year,
                                s.batch_name,
                                TIMESTAMPDIFF(MINUTE, r.start_time, COALESCE(r.end_time, NOW())) as duration
                             FROM pc_reservations r
                             JOIN students s ON r.student_id = s.id
                             WHERE 1=1";
                    
                    $params = [];
                    $types = "";

                    // Add search conditions
                    if ($search) {
                        $query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.registration_number LIKE ?)";
                        $search_param = "%$search%";
                        $params = array_merge($params, [$search_param, $search_param, $search_param]);
                        $types .= "sss";
                    }

                    if ($batch_year) {
                        $query .= " AND s.batch_year = ?";
                        $params[] = $batch_year;
                        $types .= "s";
                    }

                    if ($batch_name) {
                        $query .= " AND s.batch_name = ?";
                        $params[] = $batch_name;
                        $types .= "s";
                    }

                    if ($start_date && $end_date) {
                        $query .= " AND DATE(r.start_time) BETWEEN ? AND ?";
                        $params[] = $start_date;
                        $params[] = $end_date;
                        $types .= "ss";
                    }

                    $query .= " ORDER BY r.start_time DESC";

                    // Prepare and execute the query
                    $stmt = mysqli_prepare($conn, $query);
                    if ($types && $params) {
                        mysqli_stmt_bind_param($stmt, $types, ...$params);
                    }
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $duration_hours = floor($row['duration'] / 60);
                        $duration_minutes = $row['duration'] % 60;
                        
                        echo "<tr class='border-t hover:bg-gray-50'>";
                        echo "<td class='px-4 py-2'>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                        echo "<td class='px-4 py-2'>" . htmlspecialchars($row['registration_number']) . "</td>";
                        echo "<td class='px-4 py-2'>" . htmlspecialchars($row['batch_year'] . ' - ' . $row['batch_name']) . "</td>";
                        echo "<td class='px-4 py-2'>PC " . htmlspecialchars($row['pc_number']) . "</td>";
                        echo "<td class='px-4 py-2'>" . date('Y-m-d H:i', strtotime($row['start_time'])) . "</td>";
                        echo "<td class='px-4 py-2'>" . ($row['end_time'] ? date('Y-m-d H:i', strtotime($row['end_time'])) : 'Active') . "</td>";
                        echo "<td class='px-4 py-2'>{$duration_hours}h {$duration_minutes}m</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#reservationTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 
                'csv', 
                'excel',
                {
                    extend: 'pdf',
                    title: 'PC Reservation History',
                    customize: function(doc) {
                        doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                    }
                },
                'print'
            ],
            pageLength: 25,
            order: [[4, 'desc']]
        });

        // Initialize date pickers
        flatpickr("#start_date", {
            dateFormat: "Y-m-d"
        });
        flatpickr("#end_date", {
            dateFormat: "Y-m-d"
        });

        // Handle form submission
        $('#searchForm').on('submit', function(e) {
            // Date validation
            if($('#start_date').val() && $('#end_date').val()) {
                if($('#start_date').val() > $('#end_date').val()) {
                    e.preventDefault();
                    alert('Start date cannot be later than end date');
                    return false;
                }
            }
            
            // Show loading state on search button if not reset
            if(!e.submitter || e.submitter.name !== 'reset') {
                $('#searchBtn').prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span> Searching...');
            }
            return true;
        });
    });
    </script>
</body>
</html>