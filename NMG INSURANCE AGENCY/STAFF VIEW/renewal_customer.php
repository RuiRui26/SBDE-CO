<?php
session_start();
$allowed_roles = ['Staff'];
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
    background: #f5f5f5;
    margin: 0;
    padding: 0;
}

/* Main Content */
.main-content {
    margin-left: 270px;
    padding: 40px;
    width: calc(100% - 270px);
    background-color: #fff;
    min-height: 100vh;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Page Title */
h1 {
    font-size: 48px;
    color: #023451f3;
    margin-bottom: 40px;
}

/* Table Container */
.table-container {
    overflow-x: auto;
    width: 100%;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Table Header */
thead {
    background-color: #023451f3;
    color: white;
}

th, td {
    padding: 20px 30px;
    text-align: left;
    font-size: 20px;
}

/* Alternating Rows */
tbody tr:nth-child(odd) {
    background-color: #f9f9f9;
}

tbody tr:hover {
    background-color: #e1f0ff;
}

/* Renew Button */
.renew-btn {
    padding: 12px 24px;
    font-size: 16px;
    color: white;
    background-color: #28a745;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
}

.renew-btn:hover {
    background-color: #218838;
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

    <div class="table-container">
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
</div>

</body>
</html>
