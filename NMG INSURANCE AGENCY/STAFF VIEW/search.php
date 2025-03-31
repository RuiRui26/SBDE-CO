<?php
session_start(); 
$allowed_roles = ['Staff'];
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
    
     <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>

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
