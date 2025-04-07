<?php 
include 'sidebar.php';
require '../../../Logout_Login_USER/Restricted.php';

// Connect to database and fetch user information
require_once '../../../DB_connection/db.php';
$database = new Database();
$pdo = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get client information
$stmt = $pdo->prepare("SELECT client_id, full_name FROM clients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

$client_id = $client['client_id'] ?? null;
$full_name = $client['full_name'] ?? 'User';

// Get insurance registrations for this client
$insurance_data = [];
if ($client_id) {
    $stmt = $pdo->prepare("
        SELECT 
            ir.created_at AS register_date,
            v.plate_number,
            v.vehicle_type,
            v.type_of_insurance,
            ir.created_at AS insurance_date,
            DATE_ADD(ir.created_at, INTERVAL 1 YEAR) AS expiration_date
        FROM insurance_registration ir
        JOIN vehicles v ON ir.vehicle_id = v.vehicle_id
        WHERE ir.client_id = :client_id
        ORDER BY ir.created_at DESC
    ");
    $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
    $stmt->execute();
    $insurance_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
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
        
        /* Status badges */
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-expired {
            background-color: #f8d7da;
            color: #721c24;
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
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($insurance_data as $row): ?>
                <?php 
                    $register_date = date('Y-m-d', strtotime($row['register_date']));
                    $expiration_date = date('Y-m-d', strtotime($row['expiration_date']));
                    $is_expired = strtotime($expiration_date) < time();
                ?>
                <tr>
                    <td><?= htmlspecialchars($register_date) ?></td>
                    <td><?= htmlspecialchars($row['plate_number']) ?></td>
                    <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
                    <td><?= htmlspecialchars($row['type_of_insurance']) ?></td>
                    <td><?= htmlspecialchars($register_date) ?></td>
                    <td><?= htmlspecialchars($expiration_date) ?></td>
                    <td>
                        <span class="status-badge <?= $is_expired ? 'status-expired' : 'status-active' ?>">
                            <?= $is_expired ? 'Expired' : 'Active' ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
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
            ],
            order: [[0, 'desc']] // Sort by date descending by default
        });

        // Date Filter Function
        $('#filter-btn').on('click', function() {
            let startDate = $('#start-date').val();
            let endDate = $('#end-date').val();

            if (startDate && endDate) {
                // Convert to comparable format
                startDate = new Date(startDate).getTime();
                endDate = new Date(endDate).getTime();
                
                // Filter the table
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        var rowDate = new Date(data[0]).getTime();
                        return (rowDate >= startDate && rowDate <= endDate);
                    }
                );
                
                table.draw();
                $.fn.dataTable.ext.search.pop(); // Remove the filter
            } else {
                table.search('').draw(); // Clear any filtering
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