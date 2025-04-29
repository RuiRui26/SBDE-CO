<?php
session_start();
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
require_once "../../PHP_Files/CRUD_Functions/insurance_queries.php";

$insurance = new InsuranceTransactions();

// RENEW ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renew_id'])) {
    $insurance_id = (int) $_POST['renew_id'];
    $insurance->renewInsurance($insurance_id); // You must create this method
    header("Location: renewal_customer.php");
    exit;
}

// GET RENEWAL LIST
$renewalList = $insurance->getExpiringInsurances(); // You must create this method
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Renewal Insurance</title>
    <link rel="stylesheet" href="css/dashboard.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
            display: flex;
            margin-left: 250px; /* Adjust this if needed for your sidebar width */
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #fff;
            margin-left: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
        }

        table.dataTable {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .renew-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .renew-btn:hover {
            background-color: #218838;
        }

        .sidebar {
            position: fixed;
            width: 250px;
            height: 100%;
            top: 0;
            left: 0;
            background: #023451;
            color: white;
            padding-top: 20px;
            z-index: 1000;
        }

        .sidebar .logo {
            display: block;
            width: 80%;
            margin: 0 auto 30px;
        }

        .sidebar .menu {
            list-style: none;
            padding: 0;
        }

        .sidebar .menu li {
            padding: 15px;
            cursor: pointer;
        }

        .sidebar .menu li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar .menu li img {
            width: 20px;
            margin-right: 15px;
        }
    </style>

    <script>
        $(document).ready(function () {
            $('#renewalTable').DataTable();
        });
    </script>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h1>Renewal Insurance</h1>

    <table id="renewalTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Insurance Type</th>
                <th>Renewal Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($renewalList)): ?>
                <?php foreach ($renewalList as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['full_name']); ?></td>
                        <td><?= htmlspecialchars($item['type_of_insurance']); ?></td>
                        <td><?= htmlspecialchars($item['expiry_date']); ?></td>
                        <td><?= htmlspecialchars($item['status']); ?></td>
                        <td>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="renew_id" value="<?= $item['insurance_id']; ?>">
                                <button type="submit" class="renew-btn">Renew</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">No insurances found for renewal.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
