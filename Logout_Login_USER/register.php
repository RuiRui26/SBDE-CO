<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Registration</title>
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
    <div class="container">

        <!-- Registration Title -->
        <h2>Client Registration</h2>
        <p>Fill in your details below to create an account.</p>

        <form id="clientForm" method="POST" action="register_api.php">

            <!-- Full Name -->
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Enter your full name" required>
            <span id="name-error" class="error"></span>

            <!-- Email Address -->
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <span id="email-error" class="error"></span>

            <!-- Mobile Number -->
            <label for="phone">Mobile Number</label>
            <input type="text" id="phone" name="contact_number" placeholder="Enter your mobile number" required>
            <span id="phone-error" class="error"></span>

            <!-- Address -->
            <label for="address">Address</label>
            <input type="text" id="address" name="address" placeholder="Enter your address" required>
            <span id="address-error" class="error"></span>

            <!-- Password -->
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Create a password" required>
            <span id="password-error" class="error"></span>

            <!-- Show Password Checkbox -->
            <div class="checkbox-group">
                <input type="checkbox" id="show-password"> 
                <label for="show-password">Show Password</label>
            </div>

            <!-- Submit Button -->
            <button type="submit">Register</button>
        </form>

        <!-- Redirect to Login -->
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
