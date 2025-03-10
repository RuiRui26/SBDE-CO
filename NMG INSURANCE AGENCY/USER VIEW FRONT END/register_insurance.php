



<?php
session_start();
require_once '../../DB_connection/db.php';
$allowed_roles = ['Admin'];
require '../../Logout_Login_USER/Restricted.php';

$database = new Database();
$pdo = $database->getConnection();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User is not logged in.");
}

$user_id = $_SESSION['user_id'];

// Fetch user details from clients table
$stmt = $pdo->prepare("SELECT Full_Name, Contact_Number FROM clients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

$user_name = $client ? $client['Full_Name'] : '';
$user_mobile = $client ? $client['Contact_Number'] : '';

// Handle potential errors
$mobileError = "";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Insurance | NMG Insurance Agency</title>
    <link rel="stylesheet" href="css/register_insurance.css">
    <script>
    function validateForm() {
        let plateNumber = document.getElementById("plate_number").value.trim();
        let mvFileNumber = document.getElementById("mv_file_number").value.trim();
        let mvFileError = document.getElementById("mvFileError");
        let plateError = document.getElementById("plateError");

        mvFileError.textContent = "";
        plateError.textContent = "";

        // Ensure at least one of the fields is filled
        if (mvFileNumber === "" && plateNumber === "") {
            mvFileError.textContent = "Either MV File Number or Plate Number is required.";
            plateError.textContent = "Either MV File Number or Plate Number is required.";
            return false;
        }

        // Validate MV File Number (must be exactly 15 digits)
        if (mvFileNumber !== "" && !/^\d{15}$/.test(mvFileNumber)) {
            mvFileError.textContent = "MV File Number must be exactly 15 digits (numbers only).";
            return false;
        }

        return true;
    }
</script>

</head>
<body>

<header class="header">
    <img src="img/NMG3.png" alt="NMG Insurance Logo" class="logo">
    <h1 class="header-title">Apply for Insurance</h1>
</header>

<main class="form-section">
    <form id="insuranceForm" action="../../PHP_Files/User_View/register_insurance.php" method="POST" enctype="multipart/form-data" class="insurance-form" onsubmit="return validateForm()">

        <div class="form-column">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user_name) ?>" readonly>

            <label for="mobile">Mobile Number:</label>
            <input type="tel" id="mobile" name="mobile" value="<?= htmlspecialchars($user_mobile) ?>" readonly>

            <label for="plate_number">Plate Number:</label>
            <input type="text" id="plate_number" name="plate_number" placeholder="Enter your plate number">
            <span class="error-message" id="plateError"></span>

            <label for="mv_file_number">MV File Number (15 chars):</label>
            <input type="text" id="mv_file_number" name="mv_file_number" maxlength="15" placeholder="Enter 15-character MV File Number">
            <span class="error-message" id="mvFileError"></span>
        </div>

        <div class="form-column">
            <label for="chassis_number">Chassis Number:</label>
            <input type="text" id="chassis_number" name="chassis_number" required placeholder="Enter chassis number">

            <label for="insurance_type">Type of Insurance:</label>
            <select id="insurance_type" name="insurance_type" required>
                <option value="">Select Insurance Type</option>
                <option value="TPL">Third Party Liability (TPL) Insurance</option>
                <option value="TPPD">Third Party Property Damages (TPPD) Insurance</option>
            </select>

            <label for="vehicle_type">Vehicle Type:</label>
            <select id="vehicle_type" name="vehicle_type" required>
                <option value="">Select Vehicle Type</option>
                <option value="Motorcycle">Motorcycle</option>
                <option value="4 Wheels">4 Wheels</option>
                <option value="Truck">Truck</option>
            </select>

            <label for="or_picture">Upload OR Picture:</label>
            <input type="file" id="or_picture" name="or_picture" accept="image/*" required>

            <label for="cr_picture">Upload CR Picture:</label>
            <input type="file" id="cr_picture" name="cr_picture" accept="image/*" required>
        </div>

        <div class="submit-container">
            <button type="submit" class="submit-btn">Submit Application</button>
        </div>
    </form>
</main>

<footer>
    <p>© 2025 NMG Insurance Agency. All Rights Reserved.</p>
</footer>

</body>
</html>
