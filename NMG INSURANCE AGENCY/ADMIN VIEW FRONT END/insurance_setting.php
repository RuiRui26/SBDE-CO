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
   <?php include 'sidebar.php'; ?>

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