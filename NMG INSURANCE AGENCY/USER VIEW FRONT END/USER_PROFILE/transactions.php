<?php 
include 'sidebar.php';
require '../../../Logout_Login_USER/Restricted.php';
require_once '../../../DB_connection/db.php';

$database = new Database();
$pdo = $database->getConnection();

$user_id = $_SESSION['user_id'] ?? null;

$client = [];
if ($user_id) {
    $stmt = $pdo->prepare("SELECT client_id, full_name, contact_number, street_address, barangay FROM clients WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
}

$client_id = $client['client_id'] ?? null;
$full_name = $client['full_name'] ?? 'User';
$contact_number = $client['contact_number'] ?? '';
$street_address = $client['street_address'] ?? 'N/A';
$barangay = $client['barangay'] ?? 'N/A';

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
    <title>Transaction Records</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="../css/sidebar.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
            display: flex;
        }

        .main-content {
            margin-left: 100px;
            padding: 30px;
            flex-grow: 1;
            width: calc(100% - 250px);
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .client-info {
            background: #fff;
            padding: 20px;
            border-left: 5px solid #007bff;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .client-info p {
            margin: 6px 0;
            font-size: 15px;
        }

        .date-filter {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
        }

        table.dataTable thead {
            background-color: #007bff;
            color: white;
        }

        .status-badge {
            padding: 5px 10px;
            font-weight: bold;
            border-radius: 20px;
            font-size: 13px;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-expired {
            background: #f8d7da;
            color: #721c24;
        }

        @media print {
            body {
                background: white;
            }

            .main-content {
                margin: 0;
                padding: 0;
                width: 100%;
            }

            .sidebar,
            .date-filter,
            .dataTables_length,
            .dataTables_filter,
            .dataTables_info,
            .dataTables_paginate,
            .dt-buttons {
                display: none !important;
            }

            .client-info {
                margin-top: 20px;
                margin-bottom: 30px;
                border: none;
                page-break-inside: avoid;
                padding: 10px;
                border: 1px solid #ccc;
            }

            h2 {
                text-align: center;
                margin-bottom: 30px;
                font-size: 24px;
            }

            table {
                font-size: 14px;
                width: 100%;
                border-collapse: collapse;
            }

            table, th, td {
                border: 1px solid #333;
            }

            th, td {
                padding: 8px;
                text-align: center;
            }

            thead {
                background: #f0f0f0;
            }
        }
    </style>
</head>
<body>

<div class="main-content">
    <h2>Transaction Records</h2>

    <div class="client-info">
        <p><strong>Name:</strong> <?= htmlspecialchars($full_name) ?></p>
        <p><strong>Contact Number:</strong> <?= htmlspecialchars($contact_number) ?></p>
        <p><strong>Street:</strong> <?= htmlspecialchars($street_address) ?></p>
        <p><strong>Barangay:</strong> <?= htmlspecialchars($barangay) ?></p>
    </div>

    <div class="date-filter">
        <label>From:</label>
        <input type="date" id="start-date">
        <label>To:</label>
        <input type="date" id="end-date">
        <button id="filter-btn">Filter</button>
    </div>

    <table id="transactionsTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Date</th>
                <th>Plate No.</th>
                <th>Vehicle Type</th>
                <th>Insurance Type</th>
                <th>Registered Date</th>
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
                    <td><?= $register_date ?></td>
                    <td><?= htmlspecialchars($row['plate_number']) ?></td>
                    <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
                    <td><?= htmlspecialchars($row['type_of_insurance']) ?></td>
                    <td><?= $register_date ?></td>
                    <td><?= $expiration_date ?></td>
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

<script>
    $(document).ready(function() {
        const table = $('#transactionsTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'print',
                    title: 'Transaction Records',
                    customize: function (win) {
                        $(win.document.body).css('font-size', '14px');
                        $(win.document.body).prepend(
                            $('.client-info').clone().css({
                                marginBottom: '20px',
                                padding: '10px',
                                border: '1px solid #ccc'
                            }).wrap('<div>').parent().html()
                        );
                    }
                },
                'copy', 'csv', 'excel', 'pdf'
            ],
            order: [[0, 'desc']]
        });

        $('#filter-btn').on('click', function () {
            let start = $('#start-date').val();
            let end = $('#end-date').val();

            if (start && end) {
                const startDate = new Date(start).getTime();
                const endDate = new Date(end).getTime();

                $.fn.dataTable.ext.search.push(function(settings, data) {
                    const rowDate = new Date(data[0]).getTime();
                    return rowDate >= startDate && rowDate <= endDate;
                });

                table.draw();
                $.fn.dataTable.ext.search.pop();
            } else {
                table.search('').draw();
            }
        });
    });
</script>

</body>
</html>
