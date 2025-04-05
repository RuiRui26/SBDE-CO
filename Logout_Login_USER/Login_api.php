<?php
session_start();
require_once "../DB_connection/db.php";

header("Content-Type: application/json"); // Ensure JSON response

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Invalid request method."]);
    exit();
}

// Check if data is received properly
if (!isset($_POST['email']) || !isset($_POST['password'])) {
    echo json_encode(["error" => "Missing email or password."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$email = trim($_POST['email']);
$password = $_POST['password'];

// Validate inputs
if (empty($email) || empty($password)) {
    echo json_encode(["error" => "Both email and password are required."]);
    exit();
}

// Fetch user details from the database
$sql = "SELECT user_id, name, email, password, role FROM users WHERE email = ? LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['full_name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    echo json_encode(["success" => "Login successful.", "redirect" => "../NMG INSURANCE AGENCY/USER VIEW FRONT END/index.php"]);
    exit();
} else {
    echo json_encode(["error" => "Invalid email or password."]);
    exit();
}
?>
