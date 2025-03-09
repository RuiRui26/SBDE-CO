<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
        }
        input, button {
            display: block;
            width: calc(100% - 20px);
            margin: 10px auto;
            padding: 10px;
        }
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <form id="loginForm">
            <input type="email" id="email" name="email" placeholder="Email">
            <span id="email-error" class="error"></span>
            <input type="password" id="password" name="password" placeholder="Password">
            <span id="password-error" class="error"></span>
            <button type="submit">Login</button>
        </form>
        <p id="login-error" class="error"></p>
        <p>Don't have an account? <a href="register.php">Register</a></p>
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
