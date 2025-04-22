<?php
session_start(); 
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';

// Include database connection
include '../../DB_connection/db.php';

$db = new Database();
$conn = $db->getConnection(); // Ensure that $conn is a PDO instance
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/activitylog.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <!-- jQuery (necessary for DataTables plugin) -->
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="activity-title">Activity Log</h1>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="search" placeholder="Search activity...">
            <button type="submit">Search</button>
        </div>

        <!-- Activity Log Table -->
        <div class="table-container">
            <table id="activityLogTable">
                <thead>
                    <tr>
                        <th>Date and Time</th>
                        <th>Account</th>
                        <th>Transaction Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query to fetch data
                    $query = "SELECT u.name, ul.action, ul.created_at 
          FROM user_logs ul
          JOIN users u ON ul.user_id = u.user_id
          ORDER BY ul.created_at DESC";

                    
                    // Prepare the query with PDO
                    $stmt = $conn->prepare($query);
                    $stmt->execute(); // Execute the query

                    // Check if there are results and display them
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>
                                    <td>" . $row['created_at'] . "</td>
                                    <td>" . $row['username'] . "</td>
                                    <td>" . $row['action'] . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No activity logs found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#activityLogTable').DataTable(); // Initialize DataTable on the table
        });
    </script>
</body>
</html>
