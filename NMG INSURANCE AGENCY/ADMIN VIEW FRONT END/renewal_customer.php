<?php
session_start();
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
require_once "../../PHP_Files/CRUD_Functions/insurance_queries.php";

$insurance = new InsuranceTransactions();

// Handle renewal action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renew_id'])) {
    $insurance_id = (int) $_POST['renew_id'];
    $insurance->renewInsurance($insurance_id);
    header("Location: renewal_customer.php");
    exit;
}

$expiringInsurances = $insurance->getExpiringInsurances();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insurance Renewal</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
        }
        .main-content {
            margin-left: 270px;
            padding: 40px;
            background-color: #fff;
            min-height: 100vh;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 36px;
            color: #023451f3;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 18px;
        }
        th, td {
            padding: 12px 18px;
            border: 1px solid #ddd;
            text-align: left;
        }
        thead {
            background-color: #023451f3;
            color: white;
        }
        tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .renew-btn {
            padding: 8px 16px;
            font-size: 15px;
            color: white;
            background-color: #28a745;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .renew-btn:hover {
            background-color: #218838;
        }
        .table-container {
            overflow-x: auto;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h1>Renewal Insurance</h1>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Insurance Type</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($expiringInsurances)): ?>
    <?php foreach ($expiringInsurances as $item): ?>
        <?php
            $expiryDate = strtotime($item['expiry_date']);
            $today = strtotime(date('Y-m-d'));
            $daysLeft = ($expiryDate - $today) / (60 * 60 * 24);
        ?>
        <tr>
            <td><?= htmlspecialchars($item['full_name']) ?></td>
            <td><?= htmlspecialchars($item['type_of_insurance']) ?></td>
            <td><?= htmlspecialchars($item['expiry_date']) ?>
                <span style="color: red;">(<?= ceil($daysLeft) ?> days left)</span>
            </td>
            <td><?= htmlspecialchars($item['status']) ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="renew_id" value="<?= $item['insurance_id'] ?>">
                    <button type="submit" class="renew-btn">Renew</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="5">No expiring insurances within 30 days.</td></tr>
<?php endif; ?>

