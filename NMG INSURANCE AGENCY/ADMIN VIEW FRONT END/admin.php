<?php
session_start(); 
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
require '../../DB_connection/db.php';

// Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Fetch admin data from database
$adminData = [];
try {
    $stmt = $pdo->prepare("SELECT name, email, contact_number, role, profile_picture 
                          FROM users 
                          WHERE email = :email AND role = 'Admin'");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->execute();
    $adminData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$adminData) {
        die("Admin profile not found");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
   
    <div class="admin-container">
        <div class="cover-photo"></div>

        <div class="admin-profile">
            <img src="<?php echo !empty($adminData['profile_picture']) ? htmlspecialchars($adminData['profile_picture']) : 'img2/samplepic.png'; ?>" 
                 alt="Admin Picture" class="admin-picture">
            <div class="admin-info">
                <h1 id="adminName"><?php echo htmlspecialchars($adminData['name']); ?></h1>
                <p id="adminPosition"><?php echo htmlspecialchars($adminData['role']); ?></p>
                <p id="adminEmail"><?php echo htmlspecialchars($adminData['email']); ?></p>
                <p id="adminPhone"><?php echo htmlspecialchars($adminData['contact_number']); ?></p>
                <div class="admin-actions">
                    <button onclick="openModal()">Edit Profile</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Profile</h2>
            <form id="profileForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($adminData['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($adminData['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($adminData['contact_number']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="profile_picture">Profile Picture:</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                
                <div class="modal-buttons">
                    <button type="submit" class="save-btn">Save Changes</button>
                    <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('editProfileModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }

        // Form submission handling
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New password and confirmation password do not match!');
            }
        });

        // Toggle Submenu for Settings (Hover + Click Support)
        function toggleSubmenu(event) {
            event.stopPropagation();
            const submenu = event.currentTarget.querySelector('.submenu');
            submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
        }
    </script>
</body>
</html>