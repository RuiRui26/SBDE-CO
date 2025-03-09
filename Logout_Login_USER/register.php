<?php
require_once '../DB_connection/db.php';
session_start();

$database = new Database();
$db = $database->getConnection();

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Registration</title>
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
        <form id="clientForm" method="POST" action="register_api.php">
            <input type="text" id="name" name="name" placeholder="Full Name" required>
            <span id="name-error" class="error"></span>

            <input type="email" id="email" name="email" placeholder="Email" required>
            <span id="email-error" class="error"></span>

            <input type="text" id="phone" name="contact_number" placeholder="Mobile Number" required>
            <span id="phone-error" class="error"></span>

            <input type="text" id="address" name="address" placeholder="Address" required>
            <span id="address-error" class="error"></span>

            <input type="password" id="password" name="password" placeholder="Password" required>
            <span id="password-error" class="error"></span>

            <input type="checkbox" id="show-password"> Show Password

            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

<script>
    const form = document.getElementById("clientForm");
    const passwordField = document.getElementById("password");
    const showPasswordCheckbox = document.getElementById("show-password");

    showPasswordCheckbox.addEventListener("change", function () {
        passwordField.type = this.checked ? "text" : "password";
    });

    form.addEventListener("submit", async function (e) {
        e.preventDefault();
        const name = document.getElementById("name").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const email = document.getElementById("email").value.trim();
        const address = document.getElementById("address").value.trim();
        const pass = document.getElementById("password").value.trim();

        let isValid = true;
        document.querySelectorAll(".error").forEach(el => el.textContent = "");

        const nameRegex = /^[a-zA-Z ]{2,50}$/;
        if (!nameRegex.test(name)) {
            document.getElementById("name-error").textContent = "Name must be 2-50 letters (spaces allowed).";
            isValid = false;
        }

        const emailRegex = /^([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+)\.([a-zA-Z]{2,})$/;
        if (!emailRegex.test(email)) {
            document.getElementById("email-error").textContent = "Invalid email address.";
            isValid = false;
        }

        const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/;
        if (!passRegex.test(pass)) {
            document.getElementById("password-error").textContent = "Password must be 8-20 chars, uppercase, lowercase, number, special char.";
            isValid = false;
        }

        const phoneRegex = /^[0-9]{11}$/;
        if (!phoneRegex.test(phone)) {
            document.getElementById("phone-error").textContent = "Phone number must be exactly 11 digits.";
            isValid = false;
        }

        if (isValid) {
            let formData = new FormData(form);
            let response = await fetch("register_api.php", {
                method: "POST",
                body: formData
            });

            let result = await response.json();
            alert(result.message);
            if (result.success) window.location.href = "login.php";
        }
    });
</script>
</body>
</html>
