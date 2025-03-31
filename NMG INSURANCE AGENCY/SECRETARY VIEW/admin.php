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
    <title>Admin</title>
    <!-- Favicon -->
    <link rel="icon" type="imag6/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    
    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>

    <div class="admin-container">
        <div class="cover-photo"></div>

        <div class="admin-profile">
            <img src="img6/samplepic.png" alt="Admin Picture" class="admin-picture">
            <div class="admin-info">
                <h1 id="adminName">John Doe</h1>
                <p id="adminPosition">System Administrator</p>
                <p id="adminEmail">admin@example.com</p>
                <p id="adminPhone">+123 456 7890</p>
                <div class="admin-actions">
                    <button onclick="openModal()">Edit Profile</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <h2>Edit Profile</h2>
            <label for="name">Name:</label>
            <input type="text" id="name" value="John Doe">

            <label for="position">Position:</label>
            <input type="text" id="position" value="System Administrator">

            <label for="email">Email:</label>
            <input type="email" id="email" value="admin@example.com">

            <label for="phone">Phone:</label>
            <input type="text" id="phone" value="+123 456 7890">

            <div class="modal-buttons">
                <button onclick="saveProfile()">Save</button>
                <button onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('editProfileModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }

        function saveProfile() {
            document.getElementById('adminName').textContent = document.getElementById('name').value;
            document.getElementById('adminPosition').textContent = document.getElementById('position').value;
            document.getElementById('adminEmail').textContent = document.getElementById('email').value;
            document.getElementById('adminPhone').textContent = document.getElementById('phone').value;
            closeModal();
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