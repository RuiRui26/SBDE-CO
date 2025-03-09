<?php
session_start();

require_once "../DB_connection/db.php";
// If already logged in, redirect to dashboard
if (isset($_SESSION['username'])) {
    header("Location: ../../NMG Insurance Agency/ADMIN VIEW FRONT END/index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];


     // Prepare SQL query to check admin credentials
     $sql = "SELECT * FROM admin WHERE email = ?";
     $stmt = $conn->prepare($sql);
     $stmt->bind_param("s", $email);
     $stmt->execute();
     $result = $stmt->get_result();
     
     if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_email'] = $admin['email']; // Store session
            header("Location: ../../NMG Insurance Agency/ADMIN VIEW FRONT END/index.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | NMG Insurance</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="container">
        <!-- Logo with enhanced UI -->
        <img src="img/logo.png" alt="Company Logo" class="logo">

        <!-- Title -->
        <h2>Log In</h2>

        <!-- Error Message -->
        <?php if (isset($error)) { echo "<p>$error</p>"; } ?>

        <!-- Login Form -->
        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>

</html>
