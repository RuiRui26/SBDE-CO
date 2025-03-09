<?php
session_start();
require_once "../DB_connection/db.php";

$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(["error" => "Both email and password are required."]);
        exit();
    }

    // Fetch user details from the users table (not clients)
    $sql = "SELECT user_id, name, email, password, role FROM users WHERE email = ? AND role = 'Client' LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['full_name'] = $user['name'];
        $_SESSION['role'] = $user['role']; // Should be 'Client'

        echo json_encode(["success" => "Login successful.", "redirect" => "../NMG INSURANCE AGENCY/USER VIEW FRONT END/index.php"]);
        exit();
    } else {
        echo json_encode(["error" => "Invalid email or password."]);
        exit();
    }
}
?>
