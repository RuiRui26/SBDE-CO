<?php
session_start(); 
$allowed_roles = ['Secretary'];
require '../../Logout_Login/Restricted.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Information</title>
    <link rel="icon" type="image/png" href="img6/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/staff_info.css">
</head>

<body>
    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>


    <!-- Main Content -->
    <div class="main-content">

        <!-- Staff Header -->
        <div class="staff-header">
            <h2>Staff Information</h2>
        </div>

        <!-- Search Bar -->
        <div class="search-section">
            <input type="text" class="search-bar" placeholder="Search staff...">
        </div>

        <!-- Add Staff Button -->
        <div class="add-button-container">
            <button class="add-staff-btn">Add Staff</button>
        </div>

        <!-- Staff Container -->
        <div class="staff-container">
            <?php
            $staff = [
                ["John Doe", "Manager", "john.doe@example.com", "123-456-7890", "img6/samplepic.png"],
                ["Jane Smith", "Cashier", "jane.smith@example.com", "987-654-3210", "img6/samplepic.png"],
                ["Alice Johnson", "Agent", "alice.johnson@example.com", "111-222-3333", "img6/samplepic.png"],
                ["Bob Williams", "Agent", "bob.williams@example.com", "444-555-6666", "img6/samplepic.png"],
                ["Eva Brown", "Cashier", "eva.brown@example.com", "777-888-9999", "img6/samplepic.png"],
                ["David Lee", "Manager", "david.lee@example.com", "101-202-3030", "img6/samplepic.png"]
            ];

            foreach ($staff as $staffMember) {
                echo "<div class='staff-card'>";
                echo "<img src='" . $staffMember[4] . "' alt='" . $staffMember[0] . "' class='staff-photo'>";
                echo "<div class='staff-details'>";
                echo "<p><strong>Name:</strong> " . $staffMember[0] . "</p>";
                echo "<p><strong>Role:</strong> " . $staffMember[1] . "</p>";
                echo "<p><strong>Email:</strong> " . $staffMember[2] . "</p>";
                echo "<p><strong>Contact:</strong> " . $staffMember[3] . "</p>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>

    </div>

    <!-- JavaScript -->
    <script>
        // Profile Menu Toggle
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }
        // Toggle Submenu for Settings (Hover + Click Support)
        function toggleSubmenu(event) {
            event.stopPropagation(); // Prevent event from bubbling up
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
        }

        // Close Profile Menu on Outside Click
        document.addEventListener('click', (e) => {
            const menu = document.getElementById('profileMenu');
            if (!e.target.closest('.profile-dropdown')) {
                menu.style.display = 'none';
            }
        });
    </script>

</body>

</html>
