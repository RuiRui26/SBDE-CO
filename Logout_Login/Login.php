<?php
session_start();

// Dummy credentials for testing (Replace with database validation)
$valid_email = "admin@example.com";
$valid_password = "password123";

// If already logged in, redirect to dashboard
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email and password match
    if ($email === $valid_email && $password === $valid_password) {
        $_SESSION['username'] = $email; // Store session
        header("Location: dashboard.php"); // Redirect to dashboard
        exit();
    } else {
        $error = "Invalid email or password.";
    }
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
        
        <form action="Login.php" method="POST"> <!-- Ensure correct case -->
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
