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
    <title>Register</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="container">
        <img src="img/logo.png" alt="Company Logo" class="logo">
        <h2>Register</h2>

        <form id="myForm" method="POST" action="register_api.php">
            <div class="input-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Full Name" required>
                <span id="name-error" class="error"></span>
            </div>

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" required>
                <span id="email-error" class="error"></span>
            </div>

            <div class="input-group">
                <label for="phone">Mobile Number</label>
                <input type="text" id="phone" name="contact_number" placeholder="Mobile Number" required>
                <span id="phone-error" class="error"></span>
            </div>

            <div class="input-group">
                <label for="role">Select Role</label>
                <select id="role" name="role" class="styled-select" required>
                    <option value="">Select Role</option>
                    <option value="Admin">Admin</option>
                    <option value="Secretary">Secretary</option>
                    <option value="Staff">Staff</option>
                    <option value="Agent">Agent</option>
                    <option value="Cashier">Cashier</option>
                </select>
                <span id="role-error" class="error"></span>
            </div>

            <div class="input-group password-container">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <span class="toggle-password" onclick="togglePassword()">👁️</span>
                </div>
                <span id="password-error" class="error"></span>
            </div>

            <button type="submit" class="btn">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordField = document.getElementById("password");
            passwordField.type = passwordField.type === "password" ? "text" : "password";
        }

        // Form Validation
        const form = document.getElementById("myForm");

        form.addEventListener("submit", async function (e) {
            e.preventDefault();

            const name = document.getElementById("name").value.trim();
            const phone = document.getElementById("phone").value.trim();
            const email = document.getElementById("email").value.trim();
            const pass = document.getElementById("password").value.trim();
            const role = document.getElementById("role").value.trim();

            let isValid = true;
            document.querySelectorAll(".error").forEach(el => el.textContent = "");

            // Full Name Validation (2-50 letters)
            const nameRegex = /^[a-zA-Z ]{2,50}$/;
            if (!nameRegex.test(name)) {
                document.getElementById("name-error").textContent = "Name must be 2-50 letters (spaces allowed).";
                isValid = false;
            }

            // Email Validation
            const emailRegex = /^([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+)\.([a-zA-Z]{2,})$/;
            if (!emailRegex.test(email)) {
                document.getElementById("email-error").textContent = "Invalid email address.";
                isValid = false;
            }

            // Password Validation (8-20 characters, uppercase, lowercase, number, special char)
            const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/;
            if (!passRegex.test(pass)) {
                document.getElementById("password-error").textContent = "Password must be 8-20 characters, with uppercase, lowercase, number, and special char.";
                isValid = false;
            }

            // Phone Validation (11 digits)
            const phoneRegex = /^[0-9]{11}$/;
            if (!phoneRegex.test(phone)) {
                document.getElementById("phone-error").textContent = "Phone number must be exactly 11 digits.";
                isValid = false;
            }

            // Role Validation
            if (role === "") {
                document.getElementById("role-error").textContent = "Please select a role.";
                isValid = false;
            }

            // If valid, submit the form via AJAX
            if (isValid) {
                let formData = new FormData(form);
                let response = await fetch("register_api.php", {
                    method: "POST",
                    body: formData
                });

                let result = await response.json();
                alert(result.message);
                if (result.success) window.location.href = "../Login_Logout/login.php";
            }
        });
    </script>

</body>

</html>
