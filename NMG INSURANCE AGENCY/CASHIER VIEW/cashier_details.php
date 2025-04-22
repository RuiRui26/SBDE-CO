<?php
session_start();
$allowed_roles = ['Cashier'];
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

// Fetch cashier data from the database
$userData = [];
try {
    $stmt = $pdo->prepare("SELECT user_id, name, email, contact_number, role, profile_picture 
                           FROM users 
                           WHERE email = :email AND role = 'Cashier'");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        header('Location: error.php?msg=Cashier profile not found');
        exit();
    }
} catch (PDOException $e) {
    header('Location: error.php?msg=Database error: ' . $e->getMessage());
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']) ?: $userData['name'];
    $email = htmlspecialchars($_POST['email']) ?: $userData['email'];
    $contact_number = htmlspecialchars($_POST['phone']) ?: $userData['contact_number'];
    $new_password = $_POST['new_password'] ?: null;
    $confirm_password = $_POST['confirm_password'] ?: null;
    $current_password = $_POST['current_password'] ?: null;
    $profile_picture = $_FILES['profile_picture'];

    $emailChanged = $email !== $userData['email'];

    if ($emailChanged) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "The email is already registered.";
            header('Location: cashier.php');
            exit();
        }
    }

    if (!empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $_SESSION['error'] = "Current password is required to change the password.";
            header('Location: cashier.php');
            exit();
        }

        $stmt = $pdo->prepare("SELECT password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $_SESSION['email']);
        $stmt->execute();
        $dbPass = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($current_password, $dbPass['password'])) {
            $_SESSION['error'] = "Current password is incorrect.";
            header('Location: cashier.php');
            exit();
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = "New password and confirmation password do not match.";
            header('Location: cashier.php');
            exit();
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    } else {
        $hashed_password = null;
    }

    $target_dir = "C:/xampp/htdocs/SBDE-CO/secured_uploads/pics/";
    $profile_picture_path = $userData['profile_picture'];

    if (!empty($profile_picture['name'])) {
        $target_file = $target_dir . basename($profile_picture["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($profile_picture["tmp_name"]);
        if ($check === false) {
            $_SESSION['error'] = "File is not an image.";
            header('Location: cashier.php');
            exit();
        }

        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            header('Location: cashier.php');
            exit();
        }

        if ($profile_picture["size"] > 2000000) {
            $_SESSION['error'] = "File is too large. Max 2MB allowed.";
            header('Location: cashier.php');
            exit();
        }

        if (!move_uploaded_file($profile_picture["tmp_name"], $target_file)) {
            $_SESSION['error'] = "Error uploading file.";
            header('Location: cashier.php');
            exit();
        }

        $profile_picture_path = "secured_uploads/pics/" . basename($profile_picture["name"]);
    }

    $sql = "UPDATE users SET name = :name, email = :email, contact_number = :contact_number, profile_picture = :profile_picture";
    if ($hashed_password) {
        $sql .= ", password = :password";
    }
    $sql .= " WHERE user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':contact_number', $contact_number);
    $stmt->bindParam(':profile_picture', $profile_picture_path);
    $stmt->bindParam(':user_id', $userData['user_id']);

    if ($hashed_password) {
        $stmt->bindParam(':password', $hashed_password);
    }

    try {
        $stmt->execute();
        $_SESSION['success'] = "Profile updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Profile update failed: " . $e->getMessage();
    }
    header('Location: cashier.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cashier Profile</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="admin-container">
    <div class="cover-photo"></div>

    <div class="admin-profile">
        <img src="<?php echo !empty($userData['profile_picture']) ? htmlspecialchars($userData['profile_picture']) : 'img2/samplepic.png'; ?>" 
             alt="Cashier Picture" class="admin-picture">
        <div class="admin-info">
            <h1 id="adminName"><?php echo htmlspecialchars($userData['name']); ?></h1>
            <p id="adminPosition"><?php echo htmlspecialchars($userData['role']); ?></p>
            <p id="adminEmail"><?php echo htmlspecialchars($userData['email']); ?></p>
            <p id="adminPhone"><?php echo htmlspecialchars($userData['contact_number']); ?></p>
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
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" />
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" />
            </div>
            <div class="form-group">
                <label>Phone:</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($userData['contact_number']); ?>" />
            </div>
            <div class="form-group">
                <label>Profile Picture:</label>
                <input type="file" name="profile_picture" accept="image/*" />
            </div>
            <?php if (!empty($userData['profile_picture'])): ?>
            <div>
                <img src="<?php echo htmlspecialchars($userData['profile_picture']); ?>" width="100" height="100">
                <p>Current Picture</p>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label>Current Password (for changing password):</label>
                <input type="password" name="current_password" />
            </div>
            <div class="form-group">
                <label>New Password:</label>
                <input type="password" name="new_password" />
            </div>
            <div class="form-group">
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password" />
            </div>
            <div class="modal-buttons">
                <button type="submit">Save Changes</button>
                <button type="button" onclick="closeModal()">Cancel</button>
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
