<?php
session_start(); 
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
require_once "../../PHP_Files/CRUD_Functions/insurance_queries.php";

$insuranceTransactions = new InsuranceTransactions();
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$transactions = $insuranceTransactions->getTransactions($search, $limit, $offset);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Customers</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/customer_table.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
    /* Smooth out table area */
    .table-container {
        padding: 20px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        overflow-x: auto;
    }

    #transactionsTable {
        width: 100% !important;
        border-collapse: collapse;
        font-size: 14px;
    }

    #transactionsTable th,
    #transactionsTable td {
        padding: 12px 16px;
        text-align: left;
        vertical-align: middle;
    }

    #transactionsTable th {
        background-color:black;
        font-weight: 600;
    }

    .dataTables_wrapper .dataTables_filter {
        float: right;
        margin-bottom: 10px;
    }

    .dataTables_wrapper .dataTables_paginate {
        margin-top: 10px;
        float: right;
    }

    .status {
        padding: 6px 10px;
        border-radius: 5px;
        font-weight: bold;
        color: white;
        display: inline-block;
    }

    .status.approved {
        background-color: #28a745;
    }

    .status.pending {
        background-color: #ffc107;
    }

    .status.rejected {
        background-color: #dc3545;
    }

    .view-btn {
        background-color: #007bff;
        color: white;
        padding: 6px 10px;
        text-decoration: none;
        border-radius: 4px;
    }

    .view-btn:hover {
        background-color: #0056b3;
    }

    .add-client-btn {
        margin-bottom: 15px;
        display: inline-block;
        padding: 8px 16px;
        background-color: #17a2b8;
        color: white;
        border-radius: 6px;
        text-decoration: none;
    }

    .add-client-btn:hover {
        background-color: #138496;
    }
</style>

</head>

<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="activity-title">Insurance Customers</h1>

        <!-- Add Client Button -->
        <div class="search-container">
            <a href="add_client.php" class="add-client-btn">+ Add Client</a>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <table id="transactionsTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Plate Number / MV/File Number</th>
                        <th>Transaction</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)) : ?>
                        <?php foreach ($transactions as $transaction) : ?>
                            <tr>
                                <td><?= htmlspecialchars($transaction['full_name']); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($transaction['plate_number'])) {
                                        echo htmlspecialchars($transaction['plate_number']);
                                    } elseif (!empty($transaction['mv_file_number'])) {
                                        echo "MV/File: " . htmlspecialchars($transaction['mv_file_number']);
                                    } else {
                                        echo "N/A";
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($transaction['type_of_insurance']); ?></td>
                                <td><?= htmlspecialchars($transaction['created_at']); ?></td>
                                <td>
                                    <span class="status <?= strtolower($transaction['status']); ?>">
                                        <?= htmlspecialchars($transaction['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="insurance_details.php?id=<?= $transaction['insurance_id']; ?>" class="view-btn">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="6">No transactions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- DataTables Initialization -->
    <script>
        $(document).ready(function () {
            $('#transactionsTable').DataTable({
                "pageLength": 10,
                "lengthChange": false, // Hide "Show X entries"
                "ordering": true,
                "info": false,
                "searching": true // Set to true if you want client-side search
            });
        });
    </script>
</body>
</html>
