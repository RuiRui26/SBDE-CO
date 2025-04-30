<?php
session_start();
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
require '../../DB_connection/db.php';

// Create a new instance of Database and get the connection
$database = new Database();
$pdo = $database->getConnection();

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                addStaff($pdo);
                break;
            case 'edit':
                editStaff($pdo);
                break;
            case 'delete':
                deleteStaff($pdo);
                break;
        }
    }
}

function addStaff($pdo) {
    try {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $contact_number = $_POST['contact_number'];
        $role = $_POST['role'];
        $profile_picture = 'img2/samplepic.png'; // Default profile picture

        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, contact_number, role, profile_picture) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $password, $contact_number, $role, $profile_picture]);

        echo json_encode(['status' => 'success', 'message' => 'Staff added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

function editStaff($pdo) {
    try {
        $user_id = $_POST['user_id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $original_email = $_POST['original_email'];
        $contact_number = $_POST['contact_number'];
        $role = $_POST['role'];

        // Check if email is being changed and if new email already exists
        if ($email !== $original_email) {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetchColumn() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
                exit;
            }
        }

        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, contact_number = ?, role = ? 
                              WHERE user_id = ?");
        $stmt->execute([$first_name, $last_name, $email, $contact_number, $role, $user_id]);

        echo json_encode(['status' => 'success', 'message' => 'Staff updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

function deleteStaff($pdo) {
    try {
        $email = $_POST['email'];

        $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
        $stmt->execute([$email]);

        echo json_encode(['status' => 'success', 'message' => 'Staff deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Fetch staff data
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
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            position: relative;
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-content h2 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .modal-content label {
            display: block;
            font-weight: 600;
            margin: 12px 0 6px;
            color: #333;
        }

        .modal-content input,
        .modal-content select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border 0.3s;
        }

        .modal-content input:focus,
        .modal-content select:focus {
            border-color: #007BFF;
        }

        .modal-content button[type="submit"] {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .modal-content button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .close {
            position: absolute;
            top: 14px;
            right: 18px;
            font-size: 24px;
            color: #888;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #000;
        }

        @keyframes fadeIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @media (max-width: 500px) {
            .modal-content {
                padding: 20px;
                max-width: 90%;
            }

            .modal-content h2 {
                font-size: 20px;
            }
        }

        /* Staff card styles */
        .staff-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .staff-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .staff-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
        }

        .staff-details {
            flex: 1;
            min-width: 200px;
        }

        .staff-details p {
            margin: 8px 0;
        }

        .staff-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .edit-btn, .delete-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .edit-btn {
            background-color: #4CAF50;
            color: white;
        }

        .edit-btn:hover {
            background-color: #45a049;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .add-staff-btn {
            background-color: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .add-staff-btn:hover {
            background-color: #0b7dda;
        }

        .search-bar {
            width: 100%;
            max-width: 400px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            margin-bottom: 20px;
            outline: none;
            transition: border-color 0.3s;
        }

        .search-bar:focus {
            border-color: #007BFF;
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

                    echo "<div class='staff-card' id='staff-".htmlspecialchars($staffMember['email'])."'>";
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
            <form id="addStaffForm" onsubmit="return submitAddStaffForm(event)">
                <label>First Name:</label>
                <input type="text" name="first_name" required>

                <label>Last Name:</label>
                <input type="text" name="last_name" required>

                <label>Email:</label>
                <input type="email" name="email" required>

                <label>Password:</label>
                <input type="password" name="password" required minlength="6">

                <label>Contact Number:</label>
                <input type="text" name="contact_number" required>

                <label>Role:</label>
                <select name="role" required>
                    <option value="Secretary">Secretary</option>
                    <option value="Staff">Staff</option>
                    <option value="Agent">Agent</option>
                    <option value="Cashier">Cashier</option>
                    <option value="Admin">Admin</option>
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
            <form id="editStaffForm" onsubmit="return submitEditStaffForm(event)">
                <input type="hidden" name="action" value="edit">
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
                    <option value="Secretary">Secretary</option>
                    <option value="Staff">Staff</option>
                    <option value="Agent">Agent</option>
                    <option value="Cashier">Cashier</option>
                    <option value="Admin">Admin</option>
                </select>

                <button type="submit">Update</button>
            </form>
        </div>
    </div>

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

        function submitAddStaffForm(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            formData.append('action', 'add');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Success!', data.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'An error occurred: ' + error, 'error');
            });

            return false;
        }

        function submitEditStaffForm(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            formData.append('action', 'edit');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Success!', data.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'An error occurred: ' + error, 'error');
            });

            return false;
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
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('email', email);

                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.getElementById("staff-" + email).remove();
                            Swal.fire('Deleted!', data.message, 'success');
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'An error occurred: ' + error, 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>