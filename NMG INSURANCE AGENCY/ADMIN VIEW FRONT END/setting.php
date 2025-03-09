<?php
session_start(); 

//require('../../Logout_Login/Restricted.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | NMG Insurance</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/setting.css">
    <link rel="icon" type="image/png" href="img2/logo.png">
</head>

<body>
   <!-- Sidebar -->
   <div class="sidebar">
        <img src="img2/logo.png" alt="Logo" class="logo">
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img2/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Admin Profile</a></li>
            <li><a href="customer.php"><img src="img2/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="staff_info.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Staff Information</a></li>
            <li><a href="search.php"><img src="img2/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="activitylog.php"><img src="img2/activitylog.png" alt="Activity Icon"> Activity Log</a></li>

            <!-- Settings with Hover & Click Dropdown -->
            <li class="has-submenu" onclick="toggleSubmenu(event)">
                <a href="setting.php"><img src="img2/setting.png" alt="Setting Icon"> Settings</a>
                <ul class="submenu">
                    <li><a href="page_management.php">Page Management</a></li>
                </ul>
            </li>

            <li><a href="../../Logout_Login/Logout.php"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="page-title">Admin Settings</h1>

        <div class="container">
            <form action="save_settings.php" method="POST">

                <!-- Form Settings -->
                <fieldset>
                    <legend>Form Settings</legend>

                    <label>
                        <input type="checkbox" name="show_chassis_number" checked>
                        Show Chassis Number Field
                    </label>

                    <label>
                        <input type="checkbox" name="show_plate_number" checked>
                        Show Plate Number Field
                    </label>

                    <label for="insurance_types">Insurance Types:</label>
                    <select id="insurance_types" name="insurance_types" multiple required onchange="toggleOtherInput()">
                        <option value="tpl" selected>Third Party Liability (TPL)</option>
                        <option value="tppd" selected>Third Party Property Damage (TPPD)</option>
                        <option value="od" selected>Own Damage (OD)</option>
                        <option value="upa">Uninsured/Underinsured Protection (UPA)</option>
                        <option value="other">Other</option>
                    </select>

                    <div id="otherInsuranceType" style="display: none;">
                        <label for="other_insurance">Specify Other Insurance Type:</label>
                        <input type="text" id="other_insurance" name="other_insurance" placeholder="Enter other insurance type">
                    </div>

                </fieldset>

                <!-- Upload Settings -->
                <fieldset>
                    <legend>Upload Settings</legend>

                    <label for="max_file_size">Max Upload Size (MB):</label>
                    <input type="number" id="max_file_size" name="max_file_size" value="5" min="1" required>

                    <label for="allowed_formats">Allowed File Formats (e.g., jpg,png):</label>
                    <input type="text" id="allowed_formats" name="allowed_formats" value="jpg,png" required>

                </fieldset>

                <!-- Security Settings -->
                <fieldset>
                    <legend>Security Settings</legend>

                    <label>
                        <input type="checkbox" name="enable_encryption" checked>
                        Enable Data Encryption
                    </label>

                    <label>
                        <input type="checkbox" name="enable_audit_logs">
                        Enable Audit Logs
                    </label>

                </fieldset>

                <!-- Notification Settings -->
                <fieldset>
                    <legend>Notification Settings</legend>

                    <label for="admin_email">Admin Email for Notifications:</label>
                    <input type="email" id="admin_email" name="admin_email" value="admin@nmginsurance.com" required>

                    <label>
                        <input type="checkbox" name="enable_sms_alerts">
                        Enable SMS Alerts for New Applications
                    </label>

                </fieldset>

                <!-- Branding Settings -->
                <fieldset>
                    <legend>Branding</legend>

                    <label for="site_logo">Upload Site Logo:</label>
                    <input type="file" id="site_logo" name="site_logo" accept="image/*">

                    <label for="footer_text">Footer Text:</label>
                    <input type="text" id="footer_text" name="footer_text" value="Copyright NMG Insurance ©2025">

                </fieldset>

                <button type="submit">Save Settings</button>
            </form>
        </div>
    </div>

    <script>
        function toggleOtherInput() {
            const select = document.getElementById('insurance_types');
            const otherInput = document.getElementById('otherInsuranceType');

            if (Array.from(select.selectedOptions).some(option => option.value === 'other')) {
                otherInput.style.display = 'block';
            } else {
                otherInput.style.display = 'none';
            }
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
