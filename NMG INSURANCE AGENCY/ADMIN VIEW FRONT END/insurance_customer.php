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
</head>

<body>
    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="activity-title">Insurance Customers</h1>

        <!-- Search Section -->
        <div class="search-container">
            <form method="GET">
                <input type="text" name="search" placeholder="Search by name, plate number, or MV/File number..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </form>
            <a href="add_client.php" class="add-client-btn">+ Add Client</a>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <table>
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
                <td><span class="status <?= strtolower($transaction['status']); ?>"><?= htmlspecialchars($transaction['status']); ?></span></td>
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
</body>
</html>
