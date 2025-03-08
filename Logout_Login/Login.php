<?php
session_start();
require_once "../DB_connection/db.php";

// If already logged in, redirect to their respective dashboard
if (isset($_SESSION['username']) && isset($_SESSION['user_role'])) {
    header("Location: redirect.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';  // Use null coalescing to avoid errors
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Both email and password are required.";
    } else {
        // Allow all roles except Client
        $sql = "SELECT * FROM users WHERE email = ? AND role != 'Client'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['email'];

            // Redirect to role-based dashboard
            header("Location: redirect.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }

    $db = null; // Close DB connection
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/SDBE-CO/css/logout.css">
</head>
<body>
    <div class="container">
        <img src="/SDBE-CO/img2/logo.png" alt="Logo" class="logo">
        <h2>Login</h2>
        <?php if (isset($error)) { echo "<p style='color: red;'>$error</p>"; } ?>
        
        <form action="Login.php" method="POST">
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
