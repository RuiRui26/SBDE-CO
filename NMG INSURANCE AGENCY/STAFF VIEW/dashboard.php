<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="icon" type="image/png" href="img5/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="img5/logo.png" alt="Logo" class="logo">
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img5/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="staff_info.php"><img src="img5/adminprofile.png" alt="Admin Icon"> Staff Information</a></li>
            <li><a href="customer.php"><img src="img5/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="search.php"><img src="img5/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="../../Logout_Login/Logout.php"><img src="img5/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <!-- Admin Profile Dropdown -->
    <div class="profile-dropdown">
        <img src="img5/samplepic.png" alt="Admin Avatar" class="avatar" onclick="toggleProfileMenu()">
        <div class="profile-menu" id="profileMenu">
            <p>Admin</p>
            <a href="admin.php">Manage Account</a>
            <a href="#">Change Account</a>
            <a href="../../Logout_Login/Logout.php">Logout</a>
        </div>
    </div>

    <!-- Real-time Date and Time -->
    <div class="datetime-display" id="datetimeDisplay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="welcome-container">
            <h1>Welcome, Staff</h1>
        </div>

        <!-- Stats Container -->
        <div class="stats-container" id="stats-container">
            <?php
            $stats = [
                "Total Sales" => ["default" => 50, "day" => 50, "15days" => 700, "monthly" => 3000, "yearly" => 36000],
                "Total Insurance Applied" => ["default" => 120, "TPPD" => 120, "TPL" => 80, "UPA" => 40, "TPBI" => 35],
                "Total Pending" => ["default" => 30, "TPPD" => 30, "TPL" => 20, "UPA" => 10, "TPBI" => 5]
            ];

            foreach ($stats as $title => $data) {
                echo "
                <div class='stat-card'>
                    <h3>$title</h3>
                    <div class='transaction-number' data-default='{$data['default']}'>{$data['default']}</div>
                    <div class='dropdown-container'>
                        <select class='filter' onchange='updateStats(this)'>
                            <option value='default' selected>Total Number</option>
                ";

                foreach ($data as $key => $value) {
                    if ($key !== "default") {
                        echo "<option value='$value'>$key</option>";
                    }
                }

                echo "
                        </select>
                    </div>
                </div>";
            }
            ?>
        </div>
    </div>

    <script>
        // Update Date and Time
        function updateDateTime() {
            const now = new Date();
            document.getElementById('datetimeDisplay').textContent = now.toLocaleString();
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();

        // Update Stats Based on Dropdown
        function updateStats(select) {
            const card = select.closest('.stat-card');
            const transactionNumber = card.querySelector('.transaction-number');

            if (select.value === 'default') {
                transactionNumber.textContent = transactionNumber.dataset.default; // Reset to default value
            } else {
                transactionNumber.textContent = select.value; // Set selected value
            }
        }

        // Toggle Profile Menu
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }

        // Close dropdown if clicked outside
        document.addEventListener('click', (e) => {
            const menu = document.getElementById('profileMenu');
            if (!e.target.closest('.profile-dropdown')) {
                menu.style.display = 'none';
            }
        });
    </script>

</body>

</html>
