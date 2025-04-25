<?php
session_start();
require_once "../DB_connection/db.php";

// If already logged in, redirect
if (isset($_SESSION['user_role'])) {
    header("Location: redirect.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Both email and password are required.";
    } else {
        // Secure query to prevent SQL injection
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password and allow all roles including 'Client'
if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['last_activity'] = time();
    $_SESSION['timeout_duration'] = 1800;
    // Redirect based on role
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
    <link rel="stylesheet" href="../Logout_Login/css/login.css">
</head>
<body>
    <div class="container">
        <img src="../Logout_Login/img/logo.png" alt="Logo" class="logo">
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

            <p>Don't have an account? <a href="register.php">Register</a></p>
        </form>
    </div>
</body>
</html>
