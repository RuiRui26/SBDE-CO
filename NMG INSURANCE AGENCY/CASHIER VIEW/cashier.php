<?php
session_start(); 
require('../../Logout_Login/Restricted.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Transaction List</title>
    <link rel="icon" type="image/png" href="img3/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/customer_table.css">
</head>

<body>

    <!-- Sidebar -->
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

    <!-- Main Content -->
    <div class="main-content">

        <!-- Page Title -->
        <h1 class="activity-title">Insurance Invoice Sales</h1>

        <!-- Search and Date Filter -->
        <div class="search-container">
            <!-- Search Section -->
            <div class="search-section">
                <label for="searchInput">Search</label>
                <input type="text" id="searchInput" placeholder="Search by name or status...">
                <button class="go-btn" onclick="searchTable()">Go</button>
            </div>

            <!-- Date Filter Section -->
            <div class="date-section">
                <label for="dateFilter">Select Date</label>
                <input type="date" id="dateFilter" class="date-picker" onchange="filterByDate()">
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <table id="insuranceTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Transaction</th>
                        <th>Scheduled Date</th>
                        <th>Re-Scheduled Date</th>
                        <th>Status</th>
                        <th>View Information</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Sample Data -->
                    <tr>
                        <td>John Doe</td>
                        <td>Insurance</td>
                        <td>2024-02-15</td>
                        <td></td>
                        <td><span class="status pending">Pending</span></td>
                        <td><button class="view-btn" onclick="viewCustomer('John Doe')">View</button></td>
                    </tr>
                    <tr>
                        <td>Jane Smith</td>
                        <td>Life Insurance</td>
                        <td>2024-03-01</td>
                        <td>2024-03-05</td>
                        <td><span class="status completed">Completed</span></td>
                        <td><button class="view-btn" onclick="viewCustomer('Jane Smith')">View</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Scripts -->
    <script>
        // Navigate to view details page
        function viewCustomer(name) {
            alert("Viewing details for " + name);
            window.location.href = 'cashier_details.php';
        }

        // Search Table Functionality
        function searchTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll("#insuranceTable tbody tr");

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? "" : "none";
            });
        }

        // Filter by Date Functionality
        function filterByDate() {
            const selectedDate = document.getElementById('dateFilter').value;
            const rows = document.querySelectorAll("#insuranceTable tbody tr");

            rows.forEach(row => {
                const scheduledDate = row.cells[2].textContent.trim();
                row.style.display = !selectedDate || scheduledDate === selectedDate ? "" : "none";
            });
        }
    </script>

</body>

</html>
