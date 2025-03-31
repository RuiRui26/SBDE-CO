<?php
session_start(); 
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Documents</title>
    <link rel="icon" type="image/png" href="img6/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/customer_table.css">
</head>

<body>

    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Page Title -->
        <h1 class="activity-title">Lost Documents</h1>

        <!-- Search and Add Client Section -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search by name or status..." onkeyup="searchTable()">
            <button class="add-client-btn" onclick="addNewClient()">Add Client</button>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <table id="insuranceTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Transaction</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>John Doe</td>
                        <td>Lost</td>
                        <td>2024-02-15</td>
                        <td><span class="status pending">Pending</span></td>
                        <td>
                            <button class="view-btn" onclick="viewCustomer('John Doe')">View</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Jane Smith</td>
                        <td>Lost</td>
                        <td>2024-01-25</td>
                        <td><span class="status approved">Approved</span></td>
                        <td>
                            <button class="view-btn" onclick="viewCustomer('Jane Smith')">View</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Scripts -->
    <script>
       
        function viewCustomer(name) {
            alert("Viewing details for " + name);
            window.location.href = 'lost_details.php';
        }

        
        function addNewClient() {
            alert("Redirecting to add new client...");
            window.location.href = 'add_client.php';
        }
        // Toggle Submenu for Settings (Hover + Click Support)
        function toggleSubmenu(event) {
            event.stopPropagation(); // Prevent event from bubbling up
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
        }

        function searchTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll("#insuranceTable tbody tr");

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? "" : "none";
            });
        }
    </script>

</body>

</html>
