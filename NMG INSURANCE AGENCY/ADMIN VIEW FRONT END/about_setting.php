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
    <title>About Page Settings</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/about_setting.css">
</head>

<body>
    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>


    <!-- Main Content -->
    <div class="main-content">
        <h1>About Page Settings</h1>

        <form action="save_about.php" method="POST" enctype="multipart/form-data">

            <!-- Image Upload Section -->
            <div class="image-preview-container">
                <label for="images">Upload Images (4 or More):</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple onchange="previewImages()">
                <div id="imageGrid" class="image-grid"></div>
            </div>

            <!-- About Page Text -->
            <label for="aboutText">About Page Text:</label>
            <textarea id="aboutText" name="aboutText" rows="5" placeholder="Enter about page content..."></textarea>

            <!-- Form Buttons -->
            <div class="form-buttons">
                <button type="submit">Save</button>
                <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
            </div>

        </form>
    </div>

    <script>
        // Toggle Profile Menu
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }

        // Toggle Settings Dropdown
        function toggleSubmenu(event) {
            event.stopPropagation();
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.classList.toggle('active');
        }

        // Preview Multiple Images
        function previewImages() {
            const input = document.getElementById('images');
            const imageGrid = document.getElementById('imageGrid');
            imageGrid.innerHTML = '';

            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        imageGrid.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }

        // Close profile menu when clicking outside
        document.addEventListener('click', (event) => {
            const profileMenu = document.getElementById('profileMenu');
            if (!event.target.closest('.profile-dropdown')) {
                profileMenu.style.display = 'none';
            }
        });
    </script>

</body>

</html>
