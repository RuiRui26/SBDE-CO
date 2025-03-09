<?php
session_start(); 

require('../../Logout_Login/Restricted.php');
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <!-- Favicon -->
    <link rel="icon" type="imag2/png" href="img5/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/search.css">
</head>
<body>
    <div class="sidebar">
        <img src="img5/logo.png" alt="Logo" class="logo">
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img5/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="staff.php"><img src="img5/adminprofile.png" alt="Admin Icon"> Staff Information</a></li>
            <li><a href="customer.php"><img src="img5/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="search.php"><img src="img5/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="../../Logout_Login/Logout.php"><img src="img5/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Title in the middle -->
        <h1 class="client-title">Search Policy</h1>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="search" placeholder="Search client information...">
            <button type="button">Search</button>
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
