<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DASHBOARD</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="img2/logo.png" alt="Logo" class="logo">
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img2/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Admin Profile</a></li>
            <li><a href="customer.php"><img src="img2/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="search.php"><img src="img2/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="activitylog.php"><img src="img2/activitylog.png" alt="Activity Icon"> Activity Log</a></li>
            <li><a href="../../Logout_Login/Logout.php"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
            <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log </title>
    <!-- Favicon -->
    <link rel="icon" type="imag2/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/activitylog.css">
</head>
<body>
    <div class="sidebar">
        <img src="img2/logo.png" alt="Logo" class="logo"> 
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img2/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Admin Profile</a></li>
            <li><a href="customer.php"><img src="img2/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="search.php"><img src="img2/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="activitylog.php"><img src="img2/activitylog.png" alt="Activity Icon"> Activity Log</a></li>
            <li><a href="../../Logout_Login/Logout.php"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Title -->
        <h1 class="activity-title">Activity Log</h1>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="search" placeholder="Search activity...">
            <button type="submit">Search</button>
        </div>

        <!-- Activity Log Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date and Time</th>
                        <th>Account</th>
                        <th>Transaction Type</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Sample Rows lang te andy -->
                    <tr>
                        <td>2024-02-20 10:30 AM</td>
                        <td>Admin123</td>
                        <td>Apply Insurance</td>
                    </tr>
                    <tr>
                        <td>2024-02-20 10:30 AM</td>
                        <td>Admin456</td>
                        <td>LTO Transaction</td>
                    </tr>
                    
                </tbody>
            </table>
        </div>
    </div>


    
</body>
</html>
            <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log </title>
    <!-- Favicon -->
    <link rel="icon" type="imag2/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/activitylog.css">
</head>
<body>
    <div class="sidebar">
        <img src="img2/logo.png" alt="Logo" class="logo"> 
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img2/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Admin Profile</a></li>
            <li><a href="customer.php"><img src="img2/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="search.php"><img src="img2/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="activitylog.php"><img src="img2/activitylog.png" alt="Activity Icon"> Activity Log</a></li>
            <li><a href="../../Logout_Login/Logout.php"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Title -->
        <h1 class="activity-title">Activity Log</h1>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="search" placeholder="Search activity...">
            <button type="submit">Search</button>
        </div>

        <!-- Activity Log Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date and Time</th>
                        <th>Account</th>
                        <th>Transaction Type</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Sample Rows lang te andy -->
                    <tr>
                        <td>2024-02-20 10:30 AM</td>
                        <td>Admin123</td>
                        <td>Apply Insurance</td>
                    </tr>
                    <tr>
                        <td>2024-02-20 10:30 AM</td>
                        <td>Admin456</td>
                        <td>LTO Transaction</td>
                    </tr>
                    
                </tbody>
            </table>
        </div>
    </div>


    
</body>
</html>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="welcome-container">
            <h1>Welcome, Admin</h1>
        </div>

        <!-- Stats Container -->
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Insurance Applied</h3>
                <div class="progress-container" data-percent="75">
                    <svg>
                        <circle cx="50%" cy="50%" r="45%"></circle>
                        <circle cx="50%" cy="50%" r="45%" class="progress-circle"></circle>
                    </svg>
                    <div class="progress-value">75%</div>
                </div>
                <p class="stat-number">150</p>
            </div>

            <div class="stat-card">
                <h3>Total LTO Transactions</h3>
                <div class="progress-container" data-percent="60">
                    <svg>
                        <circle cx="50%" cy="50%" r="45%"></circle>
                        <circle cx="50%" cy="50%" r="45%" class="progress-circle"></circle>
                    </svg>
                    <div class="progress-value">60%</div>
                </div>
                <p class="stat-number">120</p>
            </div>

            <div class="stat-card">
                <h3>Pending Insurance</h3>
                <div class="progress-container" data-percent="40">
                    <svg>
                        <circle cx="50%" cy="50%" r="45%"></circle>
                        <circle cx="50%" cy="50%" r="45%" class="progress-circle"></circle>
                    </svg>
                    <div class="progress-value">40%</div>
                </div>
                <p class="stat-number">80</p>
            </div>

            <div class="stat-card">
                <h3>Approved Insurance</h3>
                <div class="progress-container" data-percent="90">
                    <svg>
                        <circle cx="50%" cy="50%" r="45%"></circle>
                        <circle cx="50%" cy="50%" r="45%" class="progress-circle"></circle>
                    </svg>
                    <div class="progress-value">90%</div>
                </div>
                <p class="stat-number">200</p>
            </div>
        </div>
    </div>

    <script src="js/progress.js"></script>

</body>

</html>
