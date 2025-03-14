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
    <title>Activity Log </title>
    <!-- Favicon -->
    <link rel="icon" type="imag2/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/activitylog.css">
</head>
<body>
   <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>

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

    <script>
        // Toggle Submenu for Settings (Hover + Click Support)
        function toggleSubmenu(event) {
            event.stopPropagation(); // Prevent event from bubbling up
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
        }
    </script>


    
</body>
</html>
