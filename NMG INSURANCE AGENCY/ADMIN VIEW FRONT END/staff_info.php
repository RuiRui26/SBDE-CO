<?php
session_start();
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
require '../../DB_connection/db.php';

// Create a new instance of Database and get the connection
$database = new Database();
$pdo = $database->getConnection();

try {
    $stmt = $pdo->prepare("SELECT users.name, users.email, users.contact_number, users.role, users.profile_picture
                           FROM users
                           WHERE users.role IN ('Secretary', 'Staff', 'Agent', 'Cashier', 'Admin')");
    $stmt->execute();
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
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
            <li><a href="search_main.php"><img src="img2/search.png" alt="Search Icon"> Search Policy</a></li>
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
            if (!empty($staff)) {
                foreach ($staff as $staffMember) {
                    $imagePath = !empty($staffMember['profile_picture']) ? $staffMember['profile_picture'] : "img2/samplepic.png";

                    echo "<div class='staff-card'>";
                    echo "<img src='" . htmlspecialchars($imagePath) . "' alt='" . htmlspecialchars($staffMember['name']) . "' class='staff-photo'>";
                    echo "<div class='staff-details'>";
                    echo "<p><strong>Name:</strong> " . htmlspecialchars($staffMember['name']) . "</p>";
                    echo "<p><strong>Role:</strong> " . htmlspecialchars($staffMember['role']) . "</p>";
                    echo "<p><strong>Email:</strong> " . htmlspecialchars($staffMember['email']) . "</p>";
                    echo "<p><strong>Contact:</strong> " . htmlspecialchars($staffMember['contact_number']) . "</p>";
                    echo "</div>";
                    echo "<div class='staff-actions'>";
                    echo "<button class='edit-btn' onclick='openEditStaffModal(" . json_encode($staffMember) . ")'>Edit</button>";
                    echo "<form method='POST' action='delete_staff.php' onsubmit='return confirm(\"Are you sure you want to delete this staff?\");'>";
                    echo "<input type='hidden' name='email' value='" . htmlspecialchars($staffMember['email']) . "'>";
                    echo "<button type='submit' class='delete-btn'>Delete</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>No staff members found.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div id="editStaffModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeEditStaffModal()">&times;</span>
            <h2>Edit Staff</h2>
            <form method="POST" action="update_staff.php">
                <input type="hidden" name="original_email" id="editOriginalEmail">

                <label>Name:</label>
                <input type="text" name="name" id="editName" required>

                <label>Email:</label>
                <input type="email" name="email" id="editEmail" required>

                <label>Contact Number:</label>
                <input type="text" name="contact_number" id="editContact" required>

                <label>Role:</label>
                <select name="role" id="editRole" required>
                    <option>Secretary</option>
                    <option>Staff</option>
                    <option>Agent</option>
                    <option>Cashier</option>
                    <option>Admin</option>
                </select>

                <button type="submit">Update</button>
            </form>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeAddStaffModal()">&times;</span>
            <h2>Add Staff</h2>
            <form id="addStaffForm">
                <label>Name:</label>
                <input type="text" name="name" required>

                <label>Email:</label>
                <input type="email" name="email" required>

                <label>Password:</label>
                <input type="password" name="password" required>

                <label>Contact Number:</label>
                <input type="text" name="contact_number" required>

                <label>Role:</label>
                <select name="role" required>
                    <option>Secretary</option>
                    <option>Staff</option>
                    <option>Agent</option>
                    <option>Cashier</option>
                    <option>Admin</option>
                </select>

                <button type="submit">Register</button>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function searchStaff() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let staffCards = document.querySelectorAll('.staff-card');

            staffCards.forEach(card => {
                let text = card.innerText.toLowerCase();
                card.style.display = text.includes(input) ? "block" : "none";
            });
        }

        function openAddStaffModal() {
            document.getElementById("addStaffModal").style.display = "flex";
        }

        function closeAddStaffModal() {
            document.getElementById("addStaffModal").style.display = "none";
        }

        function openEditStaffModal(data) {
            document.getElementById("editStaffModal").style.display = "flex";
            document.getElementById("editOriginalEmail").value = data.email;
            document.getElementById("editName").value = data.name;
            document.getElementById("editEmail").value = data.email;
            document.getElementById("editContact").value = data.contact_number;
            document.getElementById("editRole").value = data.role;
        }

        function closeEditStaffModal() {
            document.getElementById("editStaffModal").style.display = "none";
        }

        // Handle Add Staff Form submission via AJAX
        document.getElementById("addStaffForm").addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("add_staff.php", {
                method: "POST",
                body: formData,
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload(); // Refresh to show the new staff
                }
            })
            .catch(error => {
                alert("An error occurred while adding staff.");
                console.error(error);
            });
        });
    </script>
</body>
</html>
