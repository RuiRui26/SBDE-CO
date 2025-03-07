<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Setting</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/contact_setting.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="img2/logo.png" alt="Logo" class="logo">
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img2/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Admin Profile</a></li>
            <li><a href="customer.php"><img src="img2/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="staff_info.php"><img src="img2/adminprofile.png" alt="Staff Icon"> Staff Information</a></li>
            <li><a href="search.php"><img src="img2/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="activitylog.php"><img src="img2/activitylog.png" alt="Activity Icon"> Activity Log</a></li>

            <!-- Settings with Hover & Click Dropdown -->
            <li class="has-submenu" onclick="toggleSubmenu(event)">
                <a href="#"><img src="img2/setting.png" alt="Setting Icon"> Settings</a>
                <ul class="submenu">
                    <li><a href="page_management.php">Page Management</a></li>
                    <li><a href="contact_setting.php">Contact Setting</a></li>
                </ul>
            </li>

            <li><a href="../../Logout_Login/Logout.php"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Contact Setting</h1>

        <!-- Contact Info Form -->
        <form action="save_contact.php" method="POST" class="contact-form">
            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" placeholder="Enter phone number" required>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" placeholder="Enter email address" required>

            <label for="address">Physical Address:</label>
            <textarea id="address" name="address" rows="3" placeholder="Enter address" required></textarea>

            <div class="button-group">
                <button type="submit" class="save-btn">Save Changes</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='dashboard.php'">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        // Toggle the submenu on click
        function toggleSubmenu(event) {
            event.stopPropagation();
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
        }

        // Close submenu if clicked outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.submenu').forEach(submenu => {
                submenu.style.display = 'none';
            });
        });
    </script>

</body>

</html>
