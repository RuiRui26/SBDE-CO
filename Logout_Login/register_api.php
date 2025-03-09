<?php
require_once '../DB_connection/db.php';
session_start();

header("Content-Type: application/json"); // Ensure correct JSON response

$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $contact_number = trim($_POST["contact_number"]);
    $role = isset($_POST["role"]) ? trim($_POST["role"]) : "";

    // Allowed roles (No Client)
    $valid_roles = ["Agent", "Staff", "Secretary", "Cashier", "Admin"];
    if (!in_array($role, $valid_roles)) {
        echo json_encode(["success" => false, "message" => "Invalid role selected."]);
        exit();
    }

    if (empty($full_name) || empty($email) || empty($password) || empty($contact_number)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $db->beginTransaction();

        // Check if email already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(["success" => false, "message" => "Email is already registered."]);
            exit();
        }

        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, contact_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $password_hash, $role, $contact_number]);   


        $db->commit();
        echo json_encode(["success" => true, "message" => "Registration successful! 🎉"]);
    } catch (PDOException $e) {
        $db->rollBack();
        echo json_encode(["success" => false, "message" => "Registration failed: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
