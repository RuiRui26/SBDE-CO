<?php
require '../../DB_connection/db.php';

$database = new Database();
$pdo = $database->getConnection();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the POST data
    $user_id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $role = $_POST['role'];

    try {
        // Prepare and execute the UPDATE query
        $stmt = $pdo->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, contact_number = :contact_number, role = :role WHERE user_id = :user_id");
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contact_number', $contact_number);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':user_id', $user_id);
        
        // Execute the query
        if ($stmt->execute()) {
            echo "success";  // Return success
        } else {
            echo "error";  // Return error if the update fails
        }
    } catch (PDOException $e) {
        echo "error: " . $e->getMessage();  // Handle any errors
    }
}
?>
