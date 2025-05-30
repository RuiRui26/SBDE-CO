<?php
session_start(); 
$allowed_roles = ['Secretary'];
require '../../Logout_Login/Restricted.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretary | Dashboard</title>
    <link rel="icon" type="image/png" href="img6/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    
    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>

    <!-- Admin Profile Dropdown -->
    <div class="profile-dropdown">
        <img src="img6/samplepic.png" alt="Admin Avatar" class="avatar" onclick="toggleProfileMenu()">
        <div class="profile-menu" id="profileMenu">
            <p>Admin</p>
            <a href="admin.php">Manage Account</a>
            <a href="#">Change Account</a>
            <a href="../../Logout_Login/Logout.php">Logout</a>
        </div>
    </div>

    <div class="datetime-display" id="datetimeDisplay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="welcome-container">
            <h1>Welcome, Secretary</h1>
        </div>

        <!-- Stats Container -->
        <div class="stats-container">
            <?php
            $stats = [
                "Total Insurance Applied" => 275,
                "Total LTO Transactions" => 185,
                "Pending Insurance" => 60,
                "Approved Insurance" => 215
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

            <!-- Enhanced Total Sales Table -->
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

        function updateStats(cardId, range) {
            const card = document.getElementById(cardId);
            const transactionNumber = card.querySelector('.transaction-number');

            const statsData = {
                "TotalInsuranceApplied": {
                    "15days": 45,
                    "monthly": 120,
                    "yearly": 350
                },
                "TotalLTOTransactions": {
                    "15days": 30,
                    "monthly": 90,
                    "yearly": 250
                }
            };

            if (statsData[cardId] && statsData[cardId][range]) {
                transactionNumber.textContent = statsData[cardId][range];
            } else {
                transactionNumber.textContent = range === "default" ? (cardId === "TotalInsuranceApplied" ? 275 : 185) : "N/A";
            }
        }
    </script>

</body>

</html>
