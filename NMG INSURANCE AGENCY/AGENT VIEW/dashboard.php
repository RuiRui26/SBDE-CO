<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DASHBOARD</title>
    <link rel="icon" type="image/png" href="img4/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="sidebar">
        <img src="img4/logo.png" alt="Logo" class="logo">
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img4/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.php"><img src="img4/adminprofile.png" alt="Admin Icon"> Agent Profile</a></li>
            <li><a href="customer.php"><img src="img4/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="../../Logout_Login/Logout.php"><img src="img4/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <div class="profile-dropdown">
        <img src="img4/samplepic.png" alt="Admin Avatar" class="avatar" onclick="toggleProfileMenu()">
        <div class="profile-menu" id="profileMenu">
            <p>Admin</p>
            <a href="admin.php">Manage Account</a>
            <a href="#">Change Account</a>
            <a href="../../Logout_Login/Logout.php">Logout</a>
        </div>
    </div>

    <div class="datetime-display" id="datetimeDisplay"></div>

    <div class="main-content">
        <div class="welcome-container">
            <h1>Welcome, Agent!</h1>
        </div>

        <div class="stats-container" id="stats-container">
            <?php
            $stats = [
                "Total Messages Received" => 275,
                "Total Pending Messages" => 185,
                "New Customers This Month" => 50,
            ];
            foreach ($stats as $title => $value) {
                echo "
                <div class='stat-card' onclick='showChart(this)'>
                    <div class='button-container'>";

                if ($title === "Total Messages Received" || $title === "Total Pending Messages") {
                    echo "<button class='view-messages-btn' onclick='viewMessages()'>View Messages</button>";
                } else if ($title === "New Customers This Month") {
                    echo "<button class='view-customers-btn' onclick='viewCustomers()'>View Customers</button>";
                } else {
                    echo "<div class='chart-filters'>
                            <button class='chart-filter-btn' onclick='updateChart(this, \"15days\")'>Last 15 Days</button>
                            <button class='chart-filter-btn' onclick='updateChart(this, \"monthly\")'>Monthly</button>
                            <button class='chart-filter-btn' onclick='updateChart(this, \"yearly\")'>Yearly</button>
                        </div>";
                }

                echo "
                    </div>
                    <h3>$title</h3>
                    <div class='transaction-number'>$value</div>
                    <div class='chart-container' style='display: none;'>
                        <canvas></canvas>
                    </div>
                </div>";
            }
            ?>
        </div>
    </div>

    <script>
        // Real-time date and time display
        function updateDateTime() {
            const now = new Date();
            const dateTimeString = now.toLocaleString();
            document.getElementById('datetimeDisplay').textContent = dateTimeString;
        }

        updateDateTime(); // Initial update
        setInterval(updateDateTime, 1000); // Update every second

        const sampleData = {
            "15days": [20, 30, 40, 50, 45, 35, 55, 65, 60, 70, 80, 75, 85, 90, 95],
            "monthly": [400, 500, 450, 600, 700, 800],
            "yearly": [4500, 5500, 6500, 7000, 7500]
        };

        function createBarChart(ctx, data) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map((_, i) => `#${i + 1}`),
                    datasets: [{
                        label: 'Transactions',
                        data: data,
                        backgroundColor: '#4CAF50',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            ticks: { color: '#023451' },
                            grid: { color: '#ccc' }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (tooltipItem) => `Value: ${tooltipItem.raw}` } }
                    }
                }
            });
        }

        function updateChart(btn, filterValue) {
            const card = btn.closest('.stat-card');
            const chartContainer = card.querySelector('.chart-container');
            const canvas = chartContainer.querySelector('canvas');
            const ctx = canvas.getContext('2d');

            chartContainer.style.display = 'block';
            card.querySelector('.transaction-number').style.display = 'none';

            if (canvas.chart) canvas.chart.destroy();
            canvas.chart = createBarChart(ctx, sampleData[filterValue]);
        }

        function viewMessages() {
            window.location.href = "messages.php";
        }

        function viewCustomers() {
            window.location.href = "customer.php";
        }

        function showChart(card) {
            document.querySelectorAll('.stat-card').forEach(c => {
                if (c !== card) {
                    c.querySelector('.chart-container').style.display = 'none';
                    c.querySelector('.transaction-number').style.display = 'block';
                }
            });
        }

        document.addEventListener('click', (e) => {
            if (!document.getElementById('stats-container').contains(e.target)) {
                document.querySelectorAll('.stat-card').forEach(card => {
                    card.querySelector('.chart-container').style.display = 'none';
                    card.querySelector('.transaction-number').style.display = 'block';
                });
            }
        });

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