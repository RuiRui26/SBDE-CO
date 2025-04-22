<?php
session_start(); 
$allowed_roles = ['Staff'];
require('../../Logout_Login/Restricted.php');
require_once '../../DB_connection/db.php';

// Fetching updated stats
try {
    $database = new Database();
    $conn = $database->getConnection();

    $totalInsuranceApplied = $conn->query("SELECT COUNT(*) FROM insurance_registration")->fetchColumn();
    $pendingInsurance = $conn->query("SELECT COUNT(*) FROM insurance_registration WHERE status = 'Pending'")->fetchColumn();
    $approvedInsurance = $conn->query("SELECT COUNT(*) FROM insurance_registration WHERE status = 'Approved'")->fetchColumn();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff | Dashboard</title>
    <link rel="icon" type="image/png" href="img5/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

     <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>
    

    <!-- Profile Dropdown -->
    <div class="profile-dropdown">
        <img src="img5/samplepic.png" alt="Admin Avatar" class="avatar" onclick="toggleProfileMenu()">
        <div class="profile-menu" id="profileMenu">
            <p>Admin</p>
            <a href="admin.php">Manage Account</a>
            <a href="#">Change Account</a>
            <a href="../../Logout_Login/Logout.php">Logout</a>
        </div>
    </div>

    <!-- Date & Time Display -->
    <div class="datetime-display" id="datetimeDisplay"></div>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Welcome Message -->
        <div class="welcome-container">
            <h1>Welcome, Staff</h1>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container" id="stats-container">
            <?php
            $stats = [
                "Total Insurance Applied" => $totalInsuranceApplied,
                "Pending Insurance" => $pendingInsurance,
                "Approved Insurance" => $approvedInsurance,
                "Total Sales" => ["day" => 50, "15days" => 700, "monthly" => 3000, "yearly" => 36000]
            ];

            foreach ($stats as $title => $data) {
                $defaultValue = is_array($data) ? $data['day'] : $data;

                echo "
                <div class='stat-card'>
                    <h3>$title</h3>
                    <div class='transaction-number' data-default='$defaultValue'>$defaultValue</div>
                    <div class='dropdown-container'>";

                if (is_array($data)) {
                    echo "<select class='filter' onchange='updateStats(this)'>";
                    foreach ($data as $key => $value) {
                        echo "<option value='$value'>$key</option>";
                    }
                    echo "</select>";
                }

                echo "</div>
                </div>";
            }
            ?>
        </div>

        <!-- Staff Leaderboard -->
        <div class="leaderboard-container">
            <h2>Staff Leaderboard (Top Sales)</h2>
            <div class="leaderboard-table-wrapper">
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Picture</th>
                            <th>Staff Name</th>
                            <th>Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $staffSales = [
                            ["name" => "Robby Patrick Enriquez", "sales" => 350, "picture" => "img5/samplepic.png"],
                            ["name" => "Andy Rilg Dinampo", "sales" => 300, "picture" => "img5/samplepic.png"],
                            ["name" => "Alekxiz Solis", "sales" => 275, "picture" => "img5/samplepic.png"],
                            ["name" => "John Mchales Buenaventura", "sales" => 250, "picture" => "img5/samplepic.png"],
                            ["name" => "Charlie White", "sales" => 220, "picture" => "img5/samplepic.png"]
                        ];

                        usort($staffSales, fn($a, $b) => $b['sales'] - $a['sales']);

                        foreach ($staffSales as $index => $staff) {
                            $rankEmoji = ["🥇", "🥈", "🥉"][$index] ?? ($index + 1);
                            echo "<tr>
                                    <td>$rankEmoji</td>
                                    <td><img src='{$staff['picture']}' alt='{$staff['name']}' class='staff-picture'></td>
                                    <td>{$staff['name']}</td>
                                    <td>{$staff['sales']}</td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        // Date and Time
        function updateDateTime() {
            document.getElementById('datetimeDisplay').textContent = new Date().toLocaleString();
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();

        // Toggle Profile Menu
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.profile-dropdown')) {
                document.getElementById('profileMenu').style.display = 'none';
            }
        });

        // Update Stats
        function updateStats(select) {
            const card = select.closest('.stat-card');
            const number = card.querySelector('.transaction-number');
            number.textContent = select.value;
        }
    </script>

</body>

</html>
