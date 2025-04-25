<?php
session_start();
$allowed_roles = ['Cashier'];
require('../../Logout_Login/Restricted.php');
require '../../DB_connection/db.php';

$database = new Database();
$pdo = $database->getConnection();

// Fetch cashier info from `users` table only
$cashier = null;
if (isset($_SESSION['user_id'])) {
    $stmtCashier = $pdo->prepare("SELECT u.name, u.email
                                   FROM users u
                                   WHERE u.user_id = ?");
    $stmtCashier->execute([$_SESSION['user_id']]);
    $cashier = $stmtCashier->fetch(PDO::FETCH_ASSOC);
}

// Fetch approved insurance with client and vehicle info
$sql = "SELECT ir.created_at, ir.start_date, ir.expired_at, ir.type_of_insurance, ir.status, ir.is_paid, ir.is_claimed,
        CONCAT(c.first_name, ' ', c.last_name) AS client_name,
        v.brand AS vehicle_brand, v.model AS vehicle_model
        FROM insurance_registration ir
        JOIN clients c ON ir.client_id = c.client_id
        JOIN vehicles v ON ir.vehicle_id = v.vehicle_id
        WHERE ir.status = 'Approved'
        ORDER BY ir.expired_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cashier | Dashboard</title>
    <link rel="icon" type="image/png" href="img3/logo.png" />
    <link rel="stylesheet" href="css/dashboard.css" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="profile-dropdown">
        <img src="img3/samplepic.png" alt="Cashier Avatar" class="avatar" onclick="toggleProfileMenu()" />
        <div class="profile-menu" id="profileMenu" style="display:none;">
            <?php if ($cashier): ?>
                <p><?php echo htmlspecialchars($cashier['name']); ?></p>
                <p><?php echo htmlspecialchars($cashier['email']); ?></p>
            <?php else: ?>
                <p>Cashier</p>
            <?php endif; ?>
            <a href="cashier.php">Manage Account</a>
            <a href="#">Change Account</a>
            <a href="../../Logout_Login/Logout.php">Logout</a>
        </div>
    </div>

    <div class="datetime-display" id="datetimeDisplay"></div>

    <div class="main-content">
        <div class="welcome-container">
            <h1>Welcome, <?php echo $cashier ? htmlspecialchars($cashier['name']) : 'Cashier'; ?></h1>
        </div>

        <div class="payment-history">
            <h2>Approved Insurance Payment History</h2>
            <table id="insuranceTable" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Registered At</th>
                        <th>Client Name</th>
                        <th>Insurance Type</th>
                        <th>Vehicle</th>
                        <th>Start Date</th>
                        <th>Expiration Date</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Claim Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $today = new DateTime();

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $createdAt = date("F d, Y", strtotime($row['created_at']));
                        $clientName = htmlspecialchars($row['client_name']);
                        $insuranceType = htmlspecialchars($row['type_of_insurance']);
                        $vehicle = htmlspecialchars($row['vehicle_brand'] . ' ' . $row['vehicle_model']);
                        $startDate = !empty($row['start_date']) ? date("F d, Y", strtotime($row['start_date'])) : 'N/A';
                        $expiredAt = !empty($row['expired_at']) ? date("F d, Y", strtotime($row['expired_at'])) : 'N/A';
                        $status = htmlspecialchars($row['status']);
                        $paymentStatus = htmlspecialchars($row['is_paid']);
                        $claimStatus = htmlspecialchars($row['is_claimed']);

                        // Expiration status
                        $expirationStatus = "N/A";
                        $expirationStyle = "";

                        if (!empty($row['expired_at'])) {
                            $expirationDate = new DateTime($row['expired_at']);
                            $interval = $today->diff($expirationDate);
                            $daysLeft = (int)$interval->format('%r%a');

                            if ($daysLeft < 0) {
                                $expirationStatus = "Expired";
                                $expirationStyle = "color: red; font-weight: bold;";
                            } elseif ($daysLeft <= 30) {
                                $expirationStatus = "Expiring soon ({$daysLeft} day(s) left)";
                                $expirationStyle = "color: orange; font-weight: bold;";
                            } else {
                                $expirationStatus = "{$daysLeft} day(s) left";
                            }
                        }

                        echo "<tr>
                                <td>$createdAt</td>
                                <td>$clientName</td>
                                <td>$insuranceType</td>
                                <td>$vehicle</td>
                                <td>$startDate</td>
                                <td style='$expirationStyle'>$expiredAt<br>$expirationStatus</td>
                                <td>$status</td>
                                <td>$paymentStatus</td>
                                <td>$claimStatus</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        // DataTables initialization
        $(document).ready(function () {
            $('#insuranceTable').DataTable({
                responsive: true
            });
        });

        // Real-time date/time display
        function updateDateTime() {
            const now = new Date();
            document.getElementById('datetimeDisplay').textContent = now.toLocaleString();
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Toggle profile menu
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        document.addEventListener('click', (e) => {
            if (!document.getElementById('profileMenu').contains(e.target) && !e.target.classList.contains('avatar')) {
                document.getElementById('profileMenu').style.display = 'none';
            }
        });
    </script>
</body>

</html>
