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
    <title>Homepage Settings</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/homepage_setting.css">
</head>

<body>
    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Homepage Settings</h1>

        <form action="#" method="POST" enctype="multipart/form-data">
            <!-- Website Name -->
            <div class="form-group">
                <label for="website_name">Website Name</label>
                <input type="text" id="website_name" name="website_name" placeholder="Enter website name" required>
            </div>

            <!-- Logo Upload -->
            <div class="form-group">
                <label for="logo">Upload New Logo</label>
                <input type="file" id="logo" name="logo" accept="image/*" onchange="previewImage('logoPreview', this)">
                <div class="image-preview" id="logoPreviewContainer">
                    <img id="logoPreview" src="img2/logo.png" alt="Logo Preview">
                </div>
            </div>

            <!-- Background Upload -->
            <div class="form-group">
                <label for="background">Upload Background Image</label>
                <input type="file" id="background" name="background" accept="image/*" onchange="previewImage('bgPreview', this)">
                <div class="image-preview" id="bgPreviewContainer">
                    <img id="bgPreview" src="img2/default-bg.jpg" alt="Background Preview">
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="form-buttons">
                <button type="submit">Save Changes</button>
                <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
            </div>
        </form>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle Settings Submenu
        function toggleSubmenu(event) {
            event.stopPropagation();
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.classList.toggle('active');
        }

        // Image Preview
        function previewImage(previewId, input) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.submenu').forEach(submenu => submenu.classList.remove('active'));
        });
    </script>

</body>

</html>
