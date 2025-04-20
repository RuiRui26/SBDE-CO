<?php
session_start();
require '../../DB_connection/db.php';  // Include the database connection file

// Ensure the user is logged in and has the proper role
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';  // Use your existing session check

// Create a new instance of Database and get the connection
$database = new Database();
$pdo = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch the form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $contact_number = $_POST['contact_number'];
    $role = $_POST['role'];

    try {
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Prepare the SQL statement to insert the new staff into the database
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, contact_number, role)
                               VALUES (:first_name, :last_name, :email, :password, :contact_number, :role)");

        // Bind the parameters to the SQL query
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':contact_number', $contact_number);
        $stmt->bindParam(':role', $role);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to the staff page or show success message
            header("Location: ../admin/staff_info.php");
            exit();
        } else {
            echo "Error: Unable to add staff.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
