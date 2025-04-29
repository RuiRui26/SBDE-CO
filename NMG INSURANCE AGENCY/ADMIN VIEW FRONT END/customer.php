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
   <?php include 'sidebar.php'; ?>

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

            <!-- Renewal Container -->
            <div class="info-box">
                 <img src="img2/logo.png" alt="Renewal" class="box-logo">
                <h2>Renewal</h2>
                <button class="view-btn" onclick="viewAllRenewal()">View</button>
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
        function viewAllRenewal() {
            window.location.href = 'renewal_customer.php';
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