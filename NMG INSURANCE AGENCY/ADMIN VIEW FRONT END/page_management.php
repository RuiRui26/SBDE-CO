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
    <title>Setting Page Management</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/page_management.css">
</head>

<body>
    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>

    <!-- Admin Profile Dropdown -->
    <div class="profile-dropdown">
        <img src="img2/samplepic.png" alt="Admin Avatar" class="avatar" onclick="toggleProfileMenu()">
        <div class="profile-menu" id="profileMenu">
            <p>Admin</p>
            <a href="admin.php">Manage Account</a>
            <a href="#">Change Account</a>
            <a href="../../Logout_Login/Logout.php">Logout</a>
        </div>
    </div>

    <div class="datetime-display" id="datetimeDisplay"></div>

    <!-- Page Management Table -->
    <div class="main-content">
        <h1>Page Management</h1>

        <table class="page-management-table">
            <thead>
                <tr>
                    <th>Page Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Array of pages with corresponding edit pages
                $pages = [
                    ["Homepage", "Active", "homepage_setting.php"],
                    ["About Page", "Active", "about_setting.php"],
                    ["Insurance", "Active", "insurance_setting.php"],
                    ["Contacts", "Active", "contact_setting.php"],
                    ["Requirements", "Active", "requirements_setting.php"],
                ];

                // Loop through each page and display rows
                foreach ($pages as $page) {
                    $pageName = $page[0];
                    $status = $page[1];
                    $editPage = $page[2];
                    $buttonText = ($status === "Active") ? "Disable" : "Enable";

                    echo "
                    <tr>
                        <td>$pageName</td>
                        <td class='status $status'>" . ucfirst($status) . "</td>
                        <td>
                            <button class='action-btn toggle-btn' onclick='toggleStatus(this)'>$buttonText</button>
                            <a href='$editPage' class='action-btn edit-btn'>Edit</a>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        // Real-time date and time display
        function updateDateTime() {
            const now = new Date();
            const dateTimeString = now.toLocaleString();
            document.getElementById('datetimeDisplay').textContent = dateTimeString;
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Toggle Profile Menu
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }

        // Toggle Page Status
        function toggleStatus(button) {
            const row = button.closest('tr');
            const statusCell = row.querySelector('.status');
            if (statusCell.textContent === 'Active') {
                statusCell.textContent = 'Inactive';
                statusCell.classList.remove('Active');
                statusCell.classList.add('Inactive');
                button.textContent = 'Enable';
            } else {
                statusCell.textContent = 'Active';
                statusCell.classList.remove('Inactive');
                statusCell.classList.add('Active');
                button.textContent = 'Disable';
            }
        }

        // Toggle Submenu for Settings (Hover + Click Support)
        function toggleSubmenu(event) {
            event.stopPropagation();
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
        }

        // Close submenu when clicking outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.submenu').forEach(submenu => {
                submenu.style.display = 'none';
            });
        });
    </script>

</body>

</html>
