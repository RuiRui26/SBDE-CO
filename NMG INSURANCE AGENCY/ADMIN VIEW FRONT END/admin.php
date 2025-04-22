<?php
session_start();
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
require '../../DB_connection/db.php';

// Check if the session is still valid
if (!isset($_SESSION['email'])) {
    header('Location: ../../Logout_Login/login.php');
    exit();
}

// Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Fetch admin data from the database
$adminData = [];
try {
    $stmt = $pdo->prepare("SELECT user_id, name, email, contact_number, role, profile_picture 
                          FROM users 
                          WHERE email = :email AND role = 'Admin'");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->execute();
    $adminData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$adminData) {
        header('Location: error.php?msg=Admin profile not found');
        exit();
    }
} catch (PDOException $e) {
    header('Location: error.php?msg=Database error: ' . $e->getMessage());
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch form data and sanitize
    $name = htmlspecialchars($_POST['name']) ?: $adminData['name'];
    $email = htmlspecialchars($_POST['email']) ?: $adminData['email'];
    $contact_number = htmlspecialchars($_POST['phone']) ?: $adminData['contact_number'];
    $new_password = $_POST['new_password'] ?: null;
    $confirm_password = $_POST['confirm_password'] ?: null;
    $current_password = $_POST['current_password'] ?: null;
    $profile_picture = $_FILES['profile_picture'];

    // Check if the email is changed
    $emailChanged = $email !== $adminData['email'];

    if ($emailChanged) {
        // Check if the new email is already taken
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $emailCount = $stmt->fetchColumn();

        if ($emailCount > 0) {
            $_SESSION['error'] = "The email is already registered.";
            header('Location: admin.php');
            exit();
        }
    }

    // Handle password update if provided
    if (!empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $_SESSION['error'] = "Current password is required to change the password.";
            header('Location: admin.php');
            exit();
        }

        // Verify the current password with the database (for security purposes)
        $stmt = $pdo->prepare("SELECT password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $_SESSION['email']);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if current password matches the database
        if (!password_verify($current_password, $userData['password'])) {
            $_SESSION['error'] = "Current password is incorrect.";
            header('Location: admin.php');
            exit();
        }

        // Check if new password matches confirmation password
        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = "New password and confirmation password do not match.";
            header('Location: admin.php');
            exit();
        }

        // Hash the new password before updating it
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    } else {
        $hashed_password = null; // No password change
    }

    // Handle profile picture upload if provided
    $target_dir = "C:/xampp/htdocs/SBDE-CO/secured_uploads/pics/";
    $profile_picture_path = $adminData['profile_picture']; // Default to current picture

    if (!empty($profile_picture['name'])) {
        // Handle file upload if new picture is uploaded
        $target_file = $target_dir . basename($profile_picture["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file is an image
        $check = getimagesize($profile_picture["tmp_name"]);
        if ($check === false) {
            $_SESSION['error'] = "File is not an image.";
            header('Location: admin.php');
            exit();
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            header('Location: admin.php');
            exit();
        }

        // Check file size (2MB max)
        if ($profile_picture["size"] > 2000000) {
            $_SESSION['error'] = "Sorry, your file is too large. Maximum file size is 2MB.";
            header('Location: admin.php');
            exit();
        }

        // Try to upload the file
        if (!move_uploaded_file($profile_picture["tmp_name"], $target_file)) {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
            header('Location: admin.php');
            exit();
        }

        $profile_picture_path = "secured_uploads/pics/" . basename($profile_picture["name"]);
    }

    // Prepare SQL query to update profile data
    $sql = "UPDATE users SET name = :name, email = :email, contact_number = :contact_number, profile_picture = :profile_picture";

    // If password is being updated, include password in the update query
    if ($hashed_password) {
        $sql .= ", password = :password";
    }

    // Prepare the statement and execute
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':contact_number', $contact_number);
    $stmt->bindParam(':profile_picture', $profile_picture_path);

    // Bind password only if it's being updated
    if ($hashed_password) {
        $stmt->bindParam(':password', $hashed_password);
    }

    try {
        $stmt->execute();
        $_SESSION['success'] = "Profile updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Profile update failed: " . $e->getMessage();
    }
    header('Location: admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
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
            <form id="profileForm" action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($adminData['name']); ?>" />
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($adminData['email']); ?>" />
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($adminData['contact_number']); ?>" />
                </div>
                
                <div class="form-group">
                    <label for="profile_picture">Profile Picture:</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" />
                </div>
                
                <!-- Display current profile picture -->
                <?php if (!empty($adminData['profile_picture'])): ?>
                    <div>
                        <img src="<?php echo htmlspecialchars($adminData['profile_picture']); ?>" alt="Profile Picture" width="100" height="100">
                        <p>Current Picture</p>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="current_password">Current Password (only if changing password):</label>
                    <input type="password" id="current_password" name="current_password" />
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password (optional):</label>
                    <input type="password" id="new_password" name="new_password" />
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password (optional):</label>
                    <input type="password" id="confirm_password" name="confirm_password" />
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
    </script>
</body>
</html>
