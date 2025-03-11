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
    <title>Staff Information</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/staff_info.css">
    
    <style>
        /* Centered Modal Styling */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
            box-shadow: 0px 0px 10px 2px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .modal-content input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .modal-content button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .modal-content button:hover {
            background-color: #0056b3;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }
    </style>
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
            <li class="has-submenu" onclick="toggleSubmenu(event)">
                <a href="#"><img src="img2/setting.png" alt="Setting Icon"> Settings</a>
                <ul class="submenu">
                    <li><a href="page_management.php">Page Management</a></li>
                </ul>
            </li>
            <li><a href="../../Logout_Login/Logout.php"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="staff-header">
            <h2>Staff Information</h2>
        </div>

        <!-- Search Bar -->
        <div class="search-section">
            <input type="text" class="search-bar" id="searchInput" placeholder="Search staff..." onkeyup="searchStaff()">
        </div>

        <!-- Add Staff Button -->
        <div class="add-button-container">
            <button class="add-staff-btn" onclick="openAddStaffModal()">Add Staff</button>
        </div>

        <!-- Staff Container -->
        <div class="staff-container" id="staffContainer">
            <?php
            $staff = [
                ["Gaga Morales", "Secretary", "GMorales@gmail.com", "09876234512", "img2/samplepic.png"],
                ["Carl Dinapa", "Cashier", "CarlMokleio@gmail.com", "09341412678", "img2/samplepic.png"],
                ["Richard Gomez", "Agent", "RichyRich@gmail.com", "09123412678", "img2/samplepic.png"],
                ["Bob Williams", "", "bob.williams@example.com", "444-555-6666", "img2/samplepic.png"],
                ["Gemma Dela Merced", "Staff", "Barrios13@gmail.com", "09123412345", "img2/samplepic.png"],
                ["Neneng Barrios", "System Administrator", "Barrios13@gmail.com", "09123412345", "img2/samplepic.png"]
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

    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddStaffModal()">&times;</span>
            <h2>Add Staff</h2>
            <input type="text" id="staffName" placeholder="Full Name">
            <input type="text" id="staffRole" placeholder="Role">
            <input type="email" id="staffEmail" placeholder="Email">
            <input type="text" id="staffContact" placeholder="Contact Number">
            <input type="file" id="staffImage" accept="image/*">
            <button onclick="addStaff()">Submit</button>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("addStaffModal").style.display = "none";
        });

        // Search Staff Function
        function searchStaff() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let staffCards = document.querySelectorAll('.staff-card');

            staffCards.forEach(card => {
                let text = card.innerText.toLowerCase();
                card.style.display = text.includes(input) ? "block" : "none";
            });
        }

        // Open Add Staff Modal
        function openAddStaffModal() {
            document.getElementById("addStaffModal").style.display = "flex";
        }

        // Close Add Staff Modal
        function closeAddStaffModal() {
            document.getElementById("addStaffModal").style.display = "none";
        }

        // Add Staff Function
        function addStaff() {
            let name = document.getElementById("staffName").value;
            let email = document.getElementById("staffEmail").value;
            let contact = document.getElementById("staffContact").value;

            if (!name || !email || !contact) {
                alert("Please fill in all fields!");
                return;
            }

            closeAddStaffModal();
        }
    </script>

</body>
</html>