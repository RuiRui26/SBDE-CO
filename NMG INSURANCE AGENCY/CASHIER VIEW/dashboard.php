<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DASHBOARD</title>
    <link rel="icon" type="image/png" href="img3/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="sidebar">
        <img src="img3/logo.png" alt="Logo" class="logo">
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img3/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.php"><img src="img3/adminprofile.png" alt="Admin Icon"> Admin Profile</a></li>
            <li><a href="cashier.php"><img src="img3/customers.png" alt="Cashier Icon"> Cashier</a></li>
            <li><a href="search.php"><img src="img3/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="../../Logout_Login/Logout.php"><img src="img3/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <div class="profile-dropdown">
        <img src="img3/samplepic.png" alt="Admin Avatar" class="avatar" onclick="toggleProfileMenu()">
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
            <h1>Welcome, Rosvina Galadlas</h1>
        </div>

        <div class="stats-container" id="stats-container">
            <?php
            $stats = [
                "Total Paid Payments" => 275,
                "Total Unpaid Payments" => 185,
                "Total Sales" => 15000
            ];
            foreach ($stats as $title => $value) {
                echo "
                <div class='stat-card' onclick='showChart(this)'>
                    <div class='dropdown-container'>
                        <select class='filter' onchange='updateChart(this)'>
                            <option value='15days'>Last 15 Days</option>
                            <option value='monthly'>Monthly</option>
                            <option value='yearly'>Yearly</option>
                        </select>
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

        function updateChart(select) {
            const card = select.closest('.stat-card');
            const chartContainer = card.querySelector('.chart-container');
            const canvas = chartContainer.querySelector('canvas');
            const ctx = canvas.getContext('2d');
            const filterValue = select.value;

            chartContainer.style.display = 'block';
            card.querySelector('.transaction-number').style.display = 'none';

            if (canvas.chart) canvas.chart.destroy();
            canvas.chart = createBarChart(ctx, sampleData[filterValue]);
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