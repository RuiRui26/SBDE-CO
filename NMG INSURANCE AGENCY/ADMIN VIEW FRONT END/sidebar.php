<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
<div class="sidebar">
    <img src="img2/logo.png" alt="Logo" class="logo">
    <ul class="menu">
        <li><a href="dashboard.php"><img src="img2/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
        <li><a href="admin.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Admin Profile</a></li>
        <li><a href="customer.php"><img src="img2/customers.png" alt="Customers Icon"> Customers</a></li>
        <li><a href="staff_info.php"><img src="img2/adminprofile.png" alt="Staff Icon"> Staff Information</a></li>
        
        
        <li class="has-submenu" onclick="toggleSubmenu(event)">
            <a href="#"><img src="img2/setting.png" alt="Setting Icon"> Settings</a>
            <ul class="submenu">
                <li><a href="page_management.php">Page Management</a></li>
            </ul>
        </li>
        
        <li><a href="../../Logout_Login/Logout.php"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
    </ul>
</div>

<script>
    function toggleSubmenu(event) {
        event.stopPropagation();
        const submenu = event.currentTarget.querySelector('.submenu');
        submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
    }

    document.addEventListener('click', () => {
        document.querySelectorAll('.submenu').forEach(submenu => {
            submenu.style.display = 'none';
        });
    });
</script>

</body>
</html>