<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Link to External CSS -->
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="login-container">
        <!-- Left Side (Background Image) -->
        <div class="login-image"></div>

        <!-- Right Side (Login Form) -->
        <div class="login-form">
            <img src="img/logo.png" alt="Company Logo" class="logo">
            <h2>Log In</h2>

            <!-- Login Form -->
            <form id="loginForm">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                <span id="email-error" class="error"></span>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <span id="password-error" class="error"></span>

                <button type="submit">Login</button>
            </form>

            <!-- Error Display -->
            <p id="login-error" class="error"></p>

            <!-- Register Link -->
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </div>

    <script>
        document.querySelector("#loginForm").addEventListener("submit", function (e) {
            e.preventDefault(); // Prevent page reload

            let formData = new FormData(this);

            fetch("login_api.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    document.querySelector("#login-error").textContent = data.error;
                }
            })
            .catch(error => console.error("Error:", error));
        });
    </script>
</body>

</html>