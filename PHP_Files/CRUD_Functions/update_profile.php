<?php
session_start();
require '../../DB_connection/db.php';
$pdo = (new Database())->getConnection();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $profile_picture = $_FILES['profile_picture'] ?? null;
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate the password
    if (!empty($new_password) && $new_password !== $confirm_password) {
        die("New password and confirmation password do not match!");
    }

    try {
        // Prepare SQL for updating the profile
        $updateQuery = "UPDATE users SET name = :name, email = :email, contact_number = :phone";
        
        // If a profile picture is uploaded, handle the file upload
        if ($profile_picture && $profile_picture['error'] === UPLOAD_ERR_OK) {
            // Check for valid image types (JPEG, PNG, GIF)
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($profile_picture['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                die("Only JPG, PNG, or GIF images are allowed.");
            }

            // Set the upload directory
            $uploadDir = '../../secured_uploads/pics/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);  // Create the directory if it doesn't exist
            }

            $fileName = basename($profile_picture['name']);
            $targetPath = $uploadDir . $fileName;

            // Check if the file already exists and avoid overwriting
            if (file_exists($targetPath)) {
                die("File already exists. Please choose a different name.");
            }

            // Move the uploaded file to the designated directory
            move_uploaded_file($profile_picture['tmp_name'], $targetPath);
            $updateQuery .= ", profile_picture = :profile_picture";
        }

        // If password is being updated, handle the password change
        if (!empty($new_password)) {
            // Hash the new password before updating
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $updateQuery .= ", password = :password";
        }

        // Add where condition to update the correct user
        $updateQuery .= " WHERE email = :email AND role = 'Admin'";

        $stmt = $pdo->prepare($updateQuery);

        // Bind the parameters for SQL query
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);

        if ($profile_picture && $profile_picture['error'] === UPLOAD_ERR_OK) {
            $stmt->bindParam(':profile_picture', $fileName);
        }

        if (!empty($new_password)) {
            $stmt->bindParam(':password', $hashedPassword);
        }

        // Execute the update query
        $stmt->execute();

        // Check if update was successful
        if ($stmt->rowCount() > 0) {
            echo "Profile updated successfully!";
        } else {
            echo "No changes made or something went wrong.";
        }
    } catch (PDOException $e) {
        die("Error updating profile: " . $e->getMessage());
    }
}
?>
