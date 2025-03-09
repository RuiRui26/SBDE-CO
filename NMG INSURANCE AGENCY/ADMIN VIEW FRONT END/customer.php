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
    <title>Customer</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/customer.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="img2/logo.png" alt="Logo" class="logo">
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img2/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Admin Profile</a></li>
            <li><a href="customer.php"><img src="img2/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="staff_info.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Staff Information</a></li>
            <li><a href="search.php"><img src="img2/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="activitylog.php"><img src="img2/activitylog.png" alt="Activity Icon"> Activity Log</a></li>

            <!-- Settings with Hover & Click Dropdown -->
            <li class="has-submenu" onclick="toggleSubmenu(event)">
                <a href="#"><img src="img2/setting.png" alt="Setting Icon"> Settings</a>
                <ul class="submenu">
                    <li><a href="page_management.php">Page Management</a></li>
                </ul>
            </li>

            <li><a href="../../Logout_Login/Logout.php"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">

        <div class="title-container">
            <h1 class="client-title">Client Applied Information</h1>
            <button type="button" class="add-client-btn" onclick="addNewClient()">Add New Client</button>
        </div>

        <!-- Info Boxes Container -->
        <div class="info-container">
            <!-- Insurance Container -->
            <div class="info-box">
                <img src="img2/logo.png" alt="Insurance" class="box-logo">
                <h2>Insurance Transaction</h2>
                <button class="view-btn" onclick="viewAllInsurance()">View</button>
            </div>

            <!-- Archive Container -->
            <div class="info-box">
                <img src="img2/logo.png" alt="LTO" class="box-logo">
                <h2>Lost Documents</h2>
                <button class="view-btn" onclick="viewAllLto()">View</button>
            </div>
        </div>

    </div>

    <script>
        function viewAllInsurance() {
            window.location.href = 'insurance_customer.php';
        }

        function viewAllLto() {
            window.location.href = 'lost_customer.php';
        }

        function handleSearch() {
            alert("Searching for client information...");
        }

        function addNewClient() {
            window.location.href = 'add_client.php';
        }
        // Toggle Submenu for Settings (Hover + Click Support)
        function toggleSubmenu(event) {
            event.stopPropagation(); // Prevent event from bubbling up
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
        }
    </script>

</body>

</html>