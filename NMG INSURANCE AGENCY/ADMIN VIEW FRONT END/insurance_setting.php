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
    <title>Insurance Setting</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/insurance_setting.css">
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
                    <li><a href="insurance_setting.php">Insurance Setting</a></li>
                </ul>
            </li>

            <li><a href="../../Logout_Login/Logout.php"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Insurance Setting</h1>

        <!-- Insurance Management Form -->
        <form id="insuranceForm" method="POST" action="save_insurance.php" class="insurance-form">
            <h2>Add New Insurance Type</h2>

            <label for="insuranceType">Insurance Type:</label>
            <input type="text" id="insuranceType" name="insuranceType" placeholder="Enter insurance type" required>

            <div class="form-buttons">
                <button type="submit" class="save-btn">Save</button>
                <button type="button" class="cancel-btn" onclick="resetForm()">Cancel</button>
            </div>
        </form>

        <!-- Existing Insurance Types -->
        <div class="insurance-table">
            <h2>Existing Insurance Types</h2>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Insurance Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $insuranceTypes = ['TPPD', 'TPL', 'UPA', 'TPBI'];
                    foreach ($insuranceTypes as $index => $type) {
                        echo "<tr>
                                <td>" . ($index + 1) . "</td>
                                <td>$type</td>
                                <td>
                                    <a href='edit_insurance.php?type=$type' class='edit-btn'>Edit</a>
                                    <a href='delete_insurance.php?type=$type' class='delete-btn'>Delete</a>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Toggle submenu visibility
        function toggleSubmenu(event) {
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.classList.toggle('show');
        }

        // Reset form on cancel
        function resetForm() {
            document.getElementById('insuranceForm').reset();
        }
    </script>

</body>

</html>