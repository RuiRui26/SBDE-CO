<?php
session_start();
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
require '../../DB_connection/db.php';

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    try {
        // Initialize DB connection
        $database = new Database();
        $pdo = $database->getConnection();

        // Prepare the DELETE statement
        $stmt = $pdo->prepare("DELETE FROM users WHERE email = :email AND role IN ('Secretary', 'Staff', 'Agent', 'Cashier', 'Admin')");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }

    } catch (PDOException $e) {
        error_log("Delete staff error: " . $e->getMessage());
        echo "error";
    }
} else {
    echo "invalid";
}
?>
