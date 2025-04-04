<?php 
include 'sidebar.php';
require '../../../Logout_Login_USER/Restricted.php';

// Connect to database and fetch user information
require_once '../../../DB_connection/db.php';
$database = new Database();
$pdo = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get client information
$stmt = $pdo->prepare("SELECT full_name FROM clients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

$full_name = $client['full_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="../css/sidebar.css">

    <!-- jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <!-- Custom Styles -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            display: flex;
        }
        .main-content {
            margin-left: 100px;
            padding: 20px;
            flex-grow: 1;
            width: calc(100% - 250px);
            transition: margin-left 0.3s;
        }

        .collapsed + .main-content {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        h2 {
            text-align: left;
            color: #333;
        }

        .date-filter {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
        }

        .date-filter label {
            font-weight: bold;
        }

        input[type="date"] {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* DataTable Styling */
        table.dataTable thead {
            background: #007bff;
            color: white;
        }

        table.dataTable tbody tr:hover {
            background: rgba(0, 123, 255, 0.2);
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ccc;
            padding: 5px;
            border-radius: 5px;
        }
    </style>

</head>
<body>

<!-- Main Content -->
<div class="main-content">
    <h2>Transaction Records</h2>

    <!-- Date Filter -->
    <div class="date-filter">
        <label for="start-date">From:</label>
        <input type="date" id="start-date">
        <label for="end-date">To:</label>
        <input type="date" id="end-date">
        <button id="filter-btn">Filter</button>
    </div>

    <!-- Transactions Table -->
    <table id="transactionsTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Date</th>
                <th>Vehicle Plate No.</th>
                <th>Vehicle Type</th>
                <th>Insurance Type</th>
                <th>Register Insurance Date</th>
                <th>Expiration Date</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>
</div>

<!-- Initialize DataTables -->
<script>
    $(document).ready(function() {
        var table = $('#transactionsTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });

        // Date Filter Function
        $('#filter-btn').on('click', function() {
            let startDate = $('#start-date').val();
            let endDate = $('#end-date').val();

            if (startDate && endDate) {
                table.rows().every(function() {
                    let rowDate = new Date(this.data()[0]); // Get the date from first column
                    let start = new Date(startDate);
                    let end = new Date(endDate);

                    if (rowDate >= start && rowDate <= end) {
                        $(this.node()).show();
                    } else {
                        $(this.node()).hide();
                    }
                });
            } else {
                table.rows().every(function() {
                    $(this.node()).show();
                });
            }
        });
    });

    // Sidebar Toggle Functionality
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.querySelector(".sidebar");
        const mainContent = document.querySelector(".main-content");

        if (sidebar) {
            sidebar.addEventListener("click", function() {
                sidebar.classList.toggle("collapsed");
                mainContent.classList.toggle("collapsed");
            });
        }
    });
</script>

</body>
</html>
