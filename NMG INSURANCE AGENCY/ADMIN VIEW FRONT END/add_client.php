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
    <title>Add Client</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_client.css">
</head>

<body>
    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="page-title">Add Client</h1>

        <div class="client-options">
            <div class="client-card">
                <img src="img2/logo.png" alt="Lost Document" class="client-icon">
                <h2>Lost Document</h2>
                <p>Manage lost document appointments efficiently.</p>
                <a href="appoint_lost.php" class="action-btn">Proceed</a>
            </div>

            <div class="client-card">
                <img src="img2/logo.png" alt="Apply Insurance" class="client-icon">
                <h2>Insurance</h2>
                <p>Register a new insurance application quickly.</p>
                <a href="register_insurance.php" class="action-btn">Proceed</a>
            </div>
        </div>
    </div>

    <script>
        // Toggle Submenu for Settings (Hover + Click Support)
        function toggleSubmenu(event) {
            event.stopPropagation();
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
        }
    </script>

</body>

</html>
