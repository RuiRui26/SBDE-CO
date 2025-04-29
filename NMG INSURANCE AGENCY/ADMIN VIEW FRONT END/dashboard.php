<?php
session_start();
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
require_once '../../DB_connection/db.php';

// Fetching updated stats and logged-in user's name
try {
    $database = new Database();
    $conn = $database->getConnection();

    // Fetch insurance stats
    $totalInsuranceApplied = $conn->query("SELECT COUNT(*) FROM insurance_registration")->fetchColumn();
    $pendingInsurance = $conn->query("SELECT COUNT(*) FROM insurance_registration WHERE status = 'Pending'")->fetchColumn();
    $approvedInsurance = $conn->query("SELECT COUNT(*) FROM insurance_registration WHERE status = 'Approved'")->fetchColumn();

    // Fetch logged-in user's name
    $userId = $_SESSION['user_id']; // Assuming user_id is stored in session after login
    $stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $fullName = htmlspecialchars($user['first_name'] . ' ' . 
                    (!empty($user['middle_name']) ? $user['middle_name'] . ' ' : '') . 
                    $user['last_name']);
    } else {
        $fullName = "Admin"; // fallback
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Dashboard</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Admin Profile Dropdown -->
    <div class="profile-dropdown">
        <img src="img2/samplepic.png" alt="Admin Avatar" class="avatar" onclick="toggleProfileMenu()">
        <div class="profile-menu" id="profileMenu">
            <p><?= $fullName ?></p> <!-- Display dynamic full name -->
            <a href="admin.php">Manage Account</a>
            <a href="#">Change Account</a>
            <a href="../../Logout_Login/Logout.php">Logout</a>
        </div>
    </div>

    <div class="datetime-display" id="datetimeDisplay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="welcome-container">
            <h1>Welcome, <?= $fullName ?></h1> <!-- Dynamic Welcome -->
        </div>

        <!-- Stats Container -->
        <div class="stats-container">
            <?php
            $stats = [
                "Total Insurance Applied" => $totalInsuranceApplied,
                "Pending Insurance" => $pendingInsurance,
                "Approved Insurance" => $approvedInsurance,
            ];

            foreach ($stats as $title => $value) {
                echo "<div class='stat-card' id='".str_replace(' ', '', $title)."'>
                        <h3>$title</h3>
                        <div class='transaction-number'>$value</div>";

                if ($title == "Total Insurance Applied" || $title == "Total LTO Transactions") {
                    echo "<div class='dropdown-container'>
                            <select class='filter' onchange='updateStats(\"".str_replace(' ', '', $title)."\", this.value)'>
                                <option value='default'>See All</option>
                                <option value='15days'>Last 15 Days</option>
                                <option value='monthly'>Monthly</option>
                                <option value='yearly'>Yearly</option>
                            </select>
                        </div>";
                }
                echo "</div>";
            }
            ?>

            <!-- Total Sales Table -->
            <div class="sales-table-container">
                <h3>Total Sales</h3>
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th>Insurance Type</th>
                            <th>Number of Policies</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $salesData = [
                            ["TPPD", 120, "₱1,200,000"],
                            ["TPL", 80, "₱800,000"],
                            ["UPA", 40, "₱400,000"],
                            ["TPBI", 35, "₱350,000"]
                        ];

                        foreach ($salesData as $sale) {
                            echo "<tr>
                                    <td>{$sale[0]}</td>
                                    <td>{$sale[1]}</td>
                                    <td>{$sale[2]}</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        function updateDateTime() {
            const now = new Date();
            document.getElementById('datetimeDisplay').textContent = now.toLocaleString();
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);

        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }

        function toggleSubmenu(event) {
            event.stopPropagation();
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
        }

        document.addEventListener('click', () => {
            document.querySelectorAll('.submenu').forEach(submenu => {
                submenu.style.display = 'none';
            });
        });
    </script>
</body>
</html>
