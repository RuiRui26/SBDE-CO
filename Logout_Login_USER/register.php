<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Client Registration</title>
    <link rel="stylesheet" href="css/register.css" />
</head>

<body>
    <!-- Registration Form -->
    <div class="registration-container">

        <!-- Header -->
        <div class="form-header">
            <h2>Register as a Client</h2>
            <p>Fill in the details below to create your account</p>
        </div>

        <form id="clientForm" method="POST" action="register_api.php">

            <!-- First Row -->
            <div class="form-row">
                <div class="input-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="Enter First Name" required />
                    <span class="error" id="first-name-error"></span>
                </div>

                <div class="input-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Enter Last Name" required />
                    <span class="error" id="last-name-error"></span>
                </div>

                <div class="input-group small">
                    <label for="middle_initial">M.I (Optional)</label>
                    <input type="text" id="middle_initial" name="middle_initial" placeholder="M" maxlength="1" />
                </div>
            </div>

            <!-- Contact Number Field -->
            <div class="input-group">
                <label for="contact_number">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number" placeholder="09XXXXXXXXX" required />
                <span class="error" id="contact-error"></span>
            </div>

            <!-- Second Row -->
            <div class="form-row">
                <div class="input-group medium">
                    <label for="birthday">Birthday *</label>
                    <input type="date" id="birthday" name="birthday" required />
                    <span class="error" id="birthday-error"></span>
                    <span class="error" id="age-error"></span> <!-- Added age error span -->
                </div>

                <div class="input-group medium">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required />
                </div>

                <div class="input-group small">
                    <label for="zip_code">Zip Code</label>
                    <input type="text" id="zip_code" name="zip_code" placeholder="Enter Zip" required />
                    <span class="error" id="zip-code-error"></span>
                </div>

                <div class="input-group">
                    <label for="street_address">Street Address</label>
                    <input type="text" id="street_address" name="street_address" placeholder="Enter Address" required />
                </div>
            </div>

            <!-- Third Row -->
            <div class="form-row">
                <div class="input-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" placeholder="Enter City" required />
                </div>
        

            <div class="input-group medium">
                <label for="barangay">Barangay</label>
                <select id="barangay" name="barangay" required>
                    <option value="">Select Barangay</option>
                    <option value="barangay1">Arena Blanco</option>
                    <option value="barangay2">Ayala</option>
                    <option value="barangay3">Baliwasan</option>
                    <option value="barangay4">Baluno</option>
                    <option value="barangay5">Barangay Zone I</option>
                    <option value="barangay6">Barangay Zone II</option>
                    <option value="barangay7">Barangay Zone III</option>
                    <option value="barangay8">Barangay Zone IV</option>
                    <option value="barangay9">Boalan</option>
                    <option value="barangay10">Bolong</option>
                    <option value="barangay11">Buenavista</option>
                <option value="barangay12">Bunguiao</option>
                <option value="barangay13">Busay</option>
                <option value="barangay14">Cabaluay</option>
                <option value="barangay15">Cabatangan</option>
                <option value="barangay16">Camino Nuevo</option>
                <option value="barangay17">Campo Islam</option>
                <option value="barangay18">Canelar</option>
                <option value="barangay19">Capisan</option>
                <option value="barangay20">Cawit</option>
                <option value="barangay21">Culianan</option>
                <option value="barangay22">Curuan</option>
                <option value="barangay23">Dita</option>
                <option value="barangay24">Divisoria</option>
                <option value="barangay25">Dulian (Upper Bunguiao)</option>
                <option value="barangay26">Dulian (Upper Pasonanca)</option>
                <option value="barangay27">Guisao</option>
                <option value="barangay28">Guiwan</option>
                <option value="barangay29">Kasanyangan</option>
                <option value="barangay30">La Paz</option>
                <option value="barangay31">Labuan</option>
                <option value="barangay32">Lamisahan</option>
                <option value="barangay33">Landang Gua</option>
                <option value="barangay34">Landang Laum</option>
                <option value="barangay35">Lanzones</option>
                <option value="barangay36">Lapakan</option>
                <option value="barangay37">Latuan</option>
                <option value="barangay38">Licomo</option>
                <option value="barangay39">Limaong</option>
                <option value="barangay40">Limpapa</option>
                <option value="barangay41">Lubigan</option>
                <option value="barangay42">Lumayang</option>
                <option value="barangay43">Lumbangan</option>
                <option value="barangay44">Lunzuran</option>
                <option value="barangay45">Maasin</option>
                <option value="barangay46">Malagutay</option>
                <option value="barangay47">Mampang</option>
                <option value="barangay48">Manalipa</option>
                <option value="barangay49">Mangusu</option>
                <option value="barangay50">Manicahan</option>
                <option value="barangay51">Mariki</option>
                <option value="barangay52">Mercedes</option>
                <option value="barangay53">Muti</option>
                <option value="barangay54">Pamucutan</option>
                <option value="barangay55">Pangapuyan</option>
                <option value="barangay56">Panubigan</option>
                <option value="barangay57">Pasilmanta</option>
                <option value="barangay58">Pasobolong</option>
                <option value="barangay59">Pasonanca</option>
                <option value="barangay60">Patalon</option>
                <option value="barangay61">Putik</option>
                <option value="barangay62">Quiniput</option>
                <option value="barangay63">Recodo</option>
                <option value="barangay64">Salaan</option>
                <option value="barangay65">San Jose Cawa-cawa</option>
                <option value="barangay66">San Jose Gusu</option>
                <option value="barangay67">San Roque</option>
                <option value="barangay68">Sangali</option>
                <option value="barangay69">Santa Barbara</option>
                <option value="barangay70">Santa Catalina</option>
                <option value="barangay71">Santa Maria</option>
                <option value="barangay72">Santo Niño</option>
                <option value="barangay73">Sibulao</option>
                <option value="barangay74">Sinubung</option>
                <option value="barangay75">Sinunoc</option>
                <option value="barangay76">Tagasilay</option>
                <option value="barangay77">Taguiti</option>
                <option value="barangay78">Talabaan</option>
                <option value="barangay79">Talisayan</option>
                <option value="barangay80">Talon-talon</option>
                <option value="barangay81">Taluksangay</option>
                <option value="barangay82">Tetuan</option>
                <option value="barangay83">Tictapul</option>
                <option value="barangay84">Tigbalabag</option>
                <option value="barangay85">Tigtabon</option>
                <option value="barangay86">Tolosa</option>
                <option value="barangay87">Tugbungan</option>
                <option value="barangay88">Tulungatung</option>
                <option value="barangay89">Tumaga</option>
                <option value="barangay90">Tumalutab</option>
                <option value="barangay91">Tumitus</option>
                <option value="barangay92">Victoria</option>
                <option value="barangay93">Vitali</option>
                <option value="barangay94">Zambowood</option>
                </select>
            </div>
        </div>

            <!-- Password Fields -->
            <div class="form-row">
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a Password" required />
                    <span class="error" id="password-error"></span>

                    <!-- Show Password Checkbox -->
                    <label class="show-password">
                        <input type="checkbox" id="show-password" /> Show Password
                    </label>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter Password" required />
                    <span class="error" id="confirm-password-error"></span>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="form-row submit-container">
                <button type="submit" class="submit-btn">Register</button>
            </div>

            <p class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </form>
    </div>

    <script>
        const form = document.getElementById("clientForm");
        const passwordField = document.getElementById("password");
        const confirmPasswordField = document.getElementById("confirm_password");
        const showPasswordCheckbox = document.getElementById("show-password");

        showPasswordCheckbox.addEventListener("change", function () {
            const type = this.checked ? "text" : "password";
            passwordField.type = type;
            confirmPasswordField.type = type;
        });

        // Helper function to calculate age from birthday string (YYYY-MM-DD)
        function calculateAge(birthdayStr) {
            const today = new Date();
            const birthDate = new Date(birthdayStr);
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }

        form.addEventListener("submit", async function (e) {
            e.preventDefault();

            // Clear errors first
            document.querySelectorAll(".error").forEach(el => el.textContent = "");

            const lastName = document.getElementById("last_name").value.trim();
            const firstName = document.getElementById("first_name").value.trim();
            const birthday = document.getElementById("birthday").value;
            const password = passwordField.value.trim();
            const confirmPassword = confirmPasswordField.value.trim();

            let isValid = true;

            // Name Validation
            const nameRegex = /^[a-zA-Z ]{2,50}$/;
            if (!nameRegex.test(lastName)) {
                document.getElementById("last-name-error").textContent = "Last name must be 2-50 letters.";
                isValid = false;
            }
            if (!nameRegex.test(firstName)) {
                document.getElementById("first-name-error").textContent = "First name must be 2-50 letters.";
                isValid = false;
            }

            // Contact Number Validation
            const contactNumber = document.getElementById("contact_number").value.trim();
            const contactRegex = /^09\d{9}$/; // Must start with "09" and be 11 digits
            if (!contactRegex.test(contactNumber)) {
                document.getElementById("contact-error").textContent = "Enter a valid 11-digit PH number (09XXXXXXXXX).";
                isValid = false;
            }

            // Zip Code Validation
            const zipCode = document.getElementById("zip_code").value.trim();
            const zipCodeRegex = /^[0-9]{4}$/;
            if (!zipCodeRegex.test(zipCode)) {
                document.getElementById("zip-code-error").textContent = "Enter a valid 4-digit zip code.";
                isValid = false;
            }

            // Age Validation (calculate from birthday)
            if (!birthday) {
                document.getElementById("birthday-error").textContent = "Birthday is required.";
                isValid = false;
            } else {
                const age = calculateAge(birthday);
                if (age < 18) {
                    document.getElementById("age-error").textContent = "You must be 18+ to register.";
                    isValid = false;
                }
            }

            // Password Validation
            const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/;
            if (!passRegex.test(password)) {
                document.getElementById("password-error").textContent = "Password must be 8-20 chars, uppercase, lowercase, number, special char.";
                isValid = false;
            }

            // Confirm Password Validation
            if (password !== confirmPassword) {
                document.getElementById("confirm-password-error").textContent = "Passwords do not match.";
                isValid = false;
            }

            if (isValid) {
                const formData = new FormData(form);
                const response = await fetch("register_api.php", {
                    method: "POST",
                    body: formData
                });

                const result = await response.json();
                alert(result.message);
                if (result.success) {
                    window.location.href = "login.php";
                }
            }
        });
    </script>
</body>

</html>
