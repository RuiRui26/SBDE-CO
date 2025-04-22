<?php
session_start();
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
require '../../DB_connection/db.php';

// Create a new instance of Database and get the connection
$database = new Database();
$pdo = $database->getConnection();

try {
    $stmt = $pdo->prepare("SELECT users.user_id, users.first_name, users.last_name, users.email, users.contact_number, users.role, users.profile_picture
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            width: 300px;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>

<body>
<?php include 'sidebar.php'; ?>

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

                    echo "<div class='staff-card' id='staff-".$staffMember['email']."'>";
                    echo "<img src='" . htmlspecialchars($imagePath) . "' alt='" . htmlspecialchars($staffMember['first_name']) . "' class='staff-photo'>";
                    echo "<div class='staff-details'>";
                    echo "<p><strong>First Name:</strong> " . htmlspecialchars($staffMember['first_name']) . "</p>";
                    echo "<p><strong>Last Name:</strong> " . htmlspecialchars($staffMember['last_name']) . "</p>";
                    echo "<p><strong>Role:</strong> " . htmlspecialchars($staffMember['role']) . "</p>";
                    echo "<p><strong>Email:</strong> " . htmlspecialchars($staffMember['email']) . "</p>";
                    echo "<p><strong>Contact:</strong> " . htmlspecialchars($staffMember['contact_number']) . "</p>";
                    echo "</div>";
                    echo "<div class='staff-actions'>";
                    echo "<button class='edit-btn' onclick='openEditStaffModal(" . json_encode($staffMember) . ")'>Edit</button>";
                    echo "<button class='delete-btn' onclick='deleteStaff(\"" . htmlspecialchars($staffMember['email']) . "\")'>Delete</button>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>No staff members found.</p>";
            }
            ?>
        </div>
    </div>




    <!-- Add Staff Modal -->
<div id="addStaffModal" class="modal" onclick="closeModalOnBackgroundClick(event, 'addStaffModal')">
    <div class="modal-content" onclick="event.stopPropagation()">
        <span class="close" onclick="closeAddStaffModal()">&times;</span>
        <h2>Add Staff</h2>
        <form id="addStaffForm" action="../../PHP_Files/CRUD_Functions/add_staff.php" method="POST" onsubmit="return validateAddStaffForm()">
            <label>First Name:</label>
            <input type="text" name="first_name" required>

            <label>Last Name:</label>
            <input type="text" name="last_name" required>

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

<!-- Edit Staff Modal -->
<div id="editStaffModal" class="modal" onclick="closeModalOnBackgroundClick(event, 'editStaffModal')">
    <div class="modal-content" onclick="event.stopPropagation()">
        <span class="close" onclick="closeEditStaffModal()">&times;</span>
        <h2>Edit Staff</h2>
        <form id="editStaffForm" action="../../PHP_Files/CRUD_Functions/edit_staff.php" method="POST" onsubmit="return validateEditStaffForm()">
            <input type="hidden" name="user_id" id="edit_user_id">
            <input type="hidden" name="original_email" id="edit_original_email">

            <label>First Name:</label>
            <input type="text" name="first_name" id="edit_first_name" required>

            <label>Last Name:</label>
            <input type="text" name="last_name" id="edit_last_name" required>

            <label>Email:</label>
            <input type="email" name="email" id="edit_email" required>

            <label>Contact Number:</label>
            <input type="text" name="contact_number" id="edit_contact_number" required>

            <label>Role:</label>
            <select name="role" id="edit_role" required>
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

<!-- Add this to your existing <script> block -->
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
        document.getElementById('edit_user_id').value = data.user_id;
        document.getElementById("edit_original_email").value = data.email;
        document.getElementById("edit_first_name").value = data.first_name;
        document.getElementById("edit_last_name").value = data.last_name;
        document.getElementById("edit_email").value = data.email;
        document.getElementById("edit_contact_number").value = data.contact_number;
        document.getElementById("edit_role").value = data.role;
    }

    function closeEditStaffModal() {
        document.getElementById("editStaffModal").style.display = "none";
    }

    function closeModalOnBackgroundClick(event, modalId) {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }

    function validateAddStaffForm() {
        let form = document.forms["addStaffForm"];
        if (form["first_name"].value === "" ||
            form["last_name"].value === "" ||
            form["email"].value === "" ||
            form["password"].value === "" ||
            form["contact_number"].value === "" ||
            form["role"].value === "") {
            alert("All fields must be filled out");
            return false;
        }
        return true;
    }

    function validateEditStaffForm() {
        let form = document.forms["editStaffForm"];
        if (form["first_name"].value === "" ||
            form["last_name"].value === "" ||
            form["email"].value === "" ||
            form["contact_number"].value === "" ||
            form["role"].value === "") {
            alert("All fields must be filled out");
            return false;
        }
        return true;
    }

    function deleteStaff(email) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to delete this staff member?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "delete_staff.php",
                    data: { email: email },
                    success: function(response) {
                        if (response == "success") {
                            document.getElementById("staff-" + email).remove();
                            Swal.fire('Deleted!', 'The staff member has been deleted.', 'success');
                        } else {
                            Swal.fire('Error!', 'There was an issue deleting the staff.', 'error');
                        }
                    }
                });
            }
        });
    }
</script>
