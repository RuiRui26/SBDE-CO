<?php
session_start(); 
$allowed_roles = ['Admin'];


// Include the Database class
require_once '../../DB_connection/db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/search.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
   
    <!-- Main Content -->
    <div class="main-content">
        <!-- Title in the middle -->
        <h1 class="client-title">Search Policy</h1>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="search" placeholder="Search client information...">
            <button type="button">Search</button>
        </div>

        <!-- Table for DataTables -->
        <table id="policyTable" class="display">
            <thead>
                <tr>
                    <th>Client ID</th>
                    <th>Client Name</th>
                    <th>Policy Type</th>
                    <th>Policy Start Date</th>
                    <th>Policy End Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Create a Database object and get the connection
                $database = new Database();
                $conn = $database->getConnection();

                // Fetch data from the database
                $sql = "SELECT * FROM vehicles"; // Replace with your actual table name
                $stmt = $conn->prepare($sql);
                $stmt->execute();

                // Fetch results and display them in the table
                $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($vehicles) {
                    foreach ($vehicles as $row) {
                        $client_name = isset($_POST['client_name']) ? $_POST['client_name'] : '';
                        $policy_type = isset($_POST['policy_type']) ? $_POST['policy_type'] : '';
                        $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
                        $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
                        $status = isset($_POST['status']) ? $_POST['status'] : '';

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No results found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables on the policy table
            $('#policyTable').DataTable();
        });

        // Toggle Submenu for Settings (Hover + Click Support)
        function toggleSubmenu(event) {
            event.stopPropagation(); // Prevent event from bubbling up
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
        }
    </script>
</body>
</html>