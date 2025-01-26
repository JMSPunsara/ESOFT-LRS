<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Control</title>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
        }
        .btn-unblock {
            background-color: #4CAF50;
            color: white;
        }
        .btn-cancel {
            background-color: #f44336;
            color: white;
        }
        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 5px 10px;
            border-radius: 15px;
        }
        .status-blocked {
            background-color: #ffebee;
            color: #c62828;
            padding: 5px 10px;
            border-radius: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Registration Number</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Simple query without joins initially
                $query = "SELECT * FROM students";
                $result = mysqli_query($conn, $query);
                
                if (!$result) {
                    die("Query failed: " . mysqli_error($conn));
                }

                while($row = mysqli_fetch_assoc($result)) {
                    // Check if student is blocked
                    $block_check = mysqli_query($conn, 
                        "SELECT 1 FROM student_blocks WHERE student_id = '" . 
                        mysqli_real_escape_string($conn, $row['registration_number']) . "' LIMIT 1");
                    $status = mysqli_num_rows($block_check) > 0 ? 'Blocked' : 'Active';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['registration_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td>
                        <span class="status-<?php echo strtolower($status); ?>">
                            <?php echo htmlspecialchars($status); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($status == 'Blocked') { ?>
                            <button class="btn btn-unblock" 
                                    onclick="showUnblockConfirmation('<?php echo $row['registration_number']; ?>')">
                                Unblock
                            </button>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Confirmation Modal -->
    <div id="unblockModal" class="modal">
        <div class="modal-content">
            <h2>Confirm Unblock</h2>
            <p>Are you sure you want to unblock this student?</p>
            <p id="studentRegNo" style="font-size: 14px; color: #666;"></p>
            <div style="margin-top: 20px; text-align: right;">
                <button class="btn btn-cancel" onclick="hideUnblockConfirmation()">Cancel</button>
                <button class="btn btn-unblock" onclick="confirmUnblock()">Yes, Unblock</button>
            </div>
        </div>
    </div>

    <script>
    let selectedRegNo = '';

    function showUnblockConfirmation(regNo) {
        selectedRegNo = regNo;
        document.getElementById('studentRegNo').textContent = 'Registration Number: ' + regNo;
        document.getElementById('unblockModal').style.display = 'block';
    }

    function hideUnblockConfirmation() {
        document.getElementById('unblockModal').style.display = 'none';
    }

    function confirmUnblock() {
        fetch('unblock_student.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'registration_number=' + encodeURIComponent(selectedRegNo)
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Student successfully unblocked');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to unblock student'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred while processing request');
        })
        .finally(() => {
            hideUnblockConfirmation();
        });
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == document.getElementById('unblockModal')) {
            hideUnblockConfirmation();
        }
    }
    </script>
</body>
</html>