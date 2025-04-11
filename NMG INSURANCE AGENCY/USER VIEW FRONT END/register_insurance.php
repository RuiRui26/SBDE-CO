<?php
session_start();
require_once '../../DB_connection/db.php';
$allowed_roles = ['Client'];
require '../../Logout_Login_USER/Restricted.php';

$database = new Database();
$pdo = $database->getConnection();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user information
$stmt = $pdo->prepare("SELECT first_name, last_name, contact_number FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User record not found.']);
    exit;
}

$user_first_name = $user['first_name'] ?? '';
$user_last_name = $user['last_name'] ?? '';
$user_mobile = $user['contact_number'] ?? '';

// Get client information
$stmt = $pdo->prepare("SELECT client_id FROM clients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Client record not found.']);
    exit;
}

$client_id = $client['client_id']; // <-- Missing semicolon was here

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Start transaction
    $pdo->beginTransaction();

    try {
        // Process form data
        $plate_number = $_POST['plate_number'] ?? null;
        $mv_file_number = $_POST['mv_file_number'] ?? null;
        $chassis_number = $_POST['chassis_number'] ?? null;
        $vehicle_type = $_POST['vehicle_type'] ?? null;
        $insurance_type = $_POST['insurance_type'] ?? null;
        $start_date = $_POST['start_date'] ?? null;

        // Validate required fields
        $errors = [];
        if (empty($chassis_number)) $errors[] = "Chassis number is required.";
        if (empty($vehicle_type)) $errors[] = "Vehicle type is required.";
        if (empty($insurance_type)) $errors[] = "Insurance type is required.";
        if (empty($start_date)) $errors[] = "Start date is required.";
        
        // Check if at least one identifier is provided
        if (empty($plate_number) && empty($mv_file_number)) {
            $errors[] = "Either plate number or MV file number is required.";
        }

        // Validate file uploads
        if (empty($_FILES['or_picture']['tmp_name'])) {
            $errors[] = "OR picture is required.";
        }
        if (empty($_FILES['cr_picture']['tmp_name'])) {
            $errors[] = "CR picture is required.";
        }

        if (!empty($errors)) {
            throw new Exception(implode(" ", $errors));
        }

        // Check if vehicle exists
        $vehicle_id = null;
        if (!empty($plate_number)) {
            $stmt = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE plate_number = :plate_number");
            $stmt->bindParam(':plate_number', $plate_number);
            $stmt->execute();
            $existing_vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_vehicle) {
                $vehicle_id = $existing_vehicle['vehicle_id'];
            }
        } elseif (!empty($mv_file_number)) {
            $stmt = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE mv_file_number = :mv_file_number");
            $stmt->bindParam(':mv_file_number', $mv_file_number);
            $stmt->execute();
            $existing_vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_vehicle) {
                $vehicle_id = $existing_vehicle['vehicle_id'];
            }
        }

        // If vehicle doesn't exist, create new vehicle record
        if (!$vehicle_id) {
            $stmt = $pdo->prepare("INSERT INTO vehicles 
                                  (client_id, plate_number, vehicle_type, chassis_number, mv_file_number, type_of_insurance) 
                                  VALUES 
                                  (:client_id, :plate_number, :vehicle_type, :chassis_number, :mv_file_number, :insurance_type)");
            
            $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $stmt->bindParam(':plate_number', $plate_number);
            $stmt->bindParam(':vehicle_type', $vehicle_type);
            $stmt->bindParam(':chassis_number', $chassis_number);
            $stmt->bindParam(':mv_file_number', $mv_file_number);
            $stmt->bindParam(':insurance_type', $insurance_type);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create vehicle record.");
            }
            
            $vehicle_id = $pdo->lastInsertId();
        }

        // Handle file uploads
        $upload_dir = "../../uploads/insurance_docs/";
        
        // Create upload directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Process OR picture
        $or_picture_path = null;
        if (isset($_FILES['or_picture']) && $_FILES['or_picture']['error'] === UPLOAD_ERR_OK) {
            $or_file_name = "OR_" . $client_id . "_" . time() . "." . pathinfo($_FILES['or_picture']['name'], PATHINFO_EXTENSION);
            $or_picture_path = $upload_dir . $or_file_name;
            
            if (!move_uploaded_file($_FILES['or_picture']['tmp_name'], $or_picture_path)) {
                throw new Exception("Failed to upload OR picture.");
            }
        }

        // Process CR picture
        $cr_picture_path = null;
        if (isset($_FILES['cr_picture']) && $_FILES['cr_picture']['error'] === UPLOAD_ERR_OK) {
            $cr_file_name = "CR_" . $client_id . "_" . time() . "." . pathinfo($_FILES['cr_picture']['name'], PATHINFO_EXTENSION);
            $cr_picture_path = $upload_dir . $cr_file_name;
            
            if (!move_uploaded_file($_FILES['cr_picture']['tmp_name'], $cr_picture_path)) {
                throw new Exception("Failed to upload CR picture.");
            }
        }

        // Insert insurance registration
        $stmt = $pdo->prepare("INSERT INTO insurance_registration 
                              (client_id, vehicle_id, type_of_insurance, or_picture, cr_picture, created_at) 
                              VALUES 
                              (:client_id, :vehicle_id, :type_of_insurance, :or_picture, :cr_picture, NOW())");
        
        $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->bindParam(':vehicle_id', $vehicle_id, PDO::PARAM_INT);
        $stmt->bindParam(':type_of_insurance', $insurance_type);
        $stmt->bindParam(':or_picture', $or_picture_path);
        $stmt->bindParam(':cr_picture', $cr_picture_path);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to submit insurance registration.");
        }

        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Insurance registration submitted successfully!'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        
        // Delete uploaded files if transaction failed
        if (isset($or_picture_path) && file_exists($or_picture_path)) {
            @unlink($or_picture_path);
        }
        if (isset($cr_picture_path) && file_exists($cr_picture_path)) {
            @unlink($cr_picture_path);
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// If not a POST request, continue with the HTML rendering below
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Insurance | NMG Insurance Agency</title>
    <link rel="stylesheet" href="css/register_insurance.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        /* Modal styles - Enhanced Design */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            position: relative;
            animation: slideInDown 0.4s;
            border: none;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .close {
            color: #95a5a6;
            font-size: 28px;
            font-weight: bold;
            transition: color 0.2s;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }

        .close:hover {
            color: #e74c3c;
        }

        /* Input group styling */
        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }

        .input-group input[type="date"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .input-group input[type="date"]:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        /* Modal body */
        .modal-body {
            margin-bottom: 25px;
            color: #7f8c8d;
            line-height: 1.6;
        }

        .modal-body ul {
            padding-left: 20px;
            margin: 15px 0;
        }

        .modal-body li {
            margin-bottom: 8px;
        }

        /* Modal footer/buttons */
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .modal-btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 15px;
        }

        .modal-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.3);
        }

        .confirm-btn {
            background-color: #27ae60;
            color: white;
        }

        .confirm-btn:hover {
            background-color: #2ecc71;
            transform: translateY(-2px);
        }

        .cancel-btn {
            background-color: #e74c3c;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        .neutral-btn {
            background-color: #3498db;
            color: white;
        }

        .neutral-btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        /* Confirmation specific styles */
        .confirmation-text {
            font-size: 16px;
            color: #2c3e50;
            margin: 20px 0;
            text-align: center;
        }

        /* Success modal */
        .success-icon {
            text-align: center;
            font-size: 60px;
            color: #27ae60;
            margin: 20px 0;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .modal-content {
                margin: 20% auto;
                padding: 20px;
                width: 95%;
            }
            
            .modal-footer {
                flex-direction: column;
            }
            
            .modal-btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        /* Date picker customization */
        input[type="date"]::-webkit-calendar-picker-indicator {
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
        }

        .date-input-container {
            position: relative;
        }

        .date-input-container::after {
            content: "📅";
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }
        </style>
   



   <script>
let selectedDate = '';

// Validate the form inputs before proceeding
function validateForm() {
    let plateNumber = document.getElementById("plate_number").value.trim();
    let mvFileNumber = document.getElementById("mv_file_number").value.trim();
    let mvFileError = document.getElementById("mvFileError");
    let plateError = document.getElementById("plateError");

    mvFileError.textContent = "";
    plateError.textContent = "";

    if (!mvFileNumber && !plateNumber) {
        mvFileError.textContent = "Either MV File Number or Plate Number is required.";
        plateError.textContent = "Either MV File Number or Plate Number is required.";
        return false;
    }

    if (mvFileNumber && !/^\d{15}$/.test(mvFileNumber)) {
        mvFileError.textContent = "MV File Number must be exactly 15 digits (numbers only).";
        return false;
    }

    return true;
}

// Show modal with insurance information
function showInsuranceInfo() {
    const type = document.getElementById('insurance_type').value;
    const modal = document.getElementById('insuranceModal'); 
    const modalContent = document.getElementById('modalContent');

    if (type === "TPL") {
        modalContent.innerHTML = `
            <h2>Third Party Liability (TPL) Insurance</h2>
            <p>TPL insurance covers the policyholder's legal responsibility for causing injury or death to others in a vehicle accident.</p>
            <h2>Benefits</h2>
            <ul style="text-align: left;">
                <li>Covers medical expenses for third parties injured in an accident.</li>
                <li>Provides compensation for accidental death or disability of third parties.</li>
                <li>Legal protection against claims arising from third-party injuries or fatalities.</li>
                <li>Mandatory coverage to legally drive a vehicle.</li>
            </ul>
             <h2>Requirements</h2>
            <ul style="text-align: left;">
                <li>Certificate of Registration (CR)</li>
                <li>Official Receipt (OR)</li>
                <li>Smoke Emission</li>
            </ul>
        `;
    } else if (type === "TPPD") {
        modalContent.innerHTML = `
            <h2>Third-Party Property Damage (TPPD) Insurance</h2>
            <p>TPPD insurance covers the cost of repairing or replacing third-party property damaged during an accident.</p>
            <h2>Benefits</h2>
            <ul style="text-align: left;">
                <li>Pays for property damage caused to other people's cars or property.</li>
                <li>Reduces financial burden by covering expensive repair costs.</li>
                <li>Legal coverage if the third party takes legal action.</li>
                <li>Peace of mind knowing property damage is covered.</li>
            </ul>
            <h2>Requirements</h2>
            <ul style="text-align: left;">
                <li>Certificate of Registration (CR)</li>
                <li>Official Receipt (OR)</li>
                <li>Smoke Emission</li>
            </ul>
        `;
    } else {
        modal.style.display = "none";
        return;
    }

    modal.style.display = "block";
}

function closeModal() {
    document.getElementById('insuranceModal').style.display = "none";
}

function showDateModal() {
    if (validateForm()) {
        document.getElementById('dateModal').style.display = "block";
    }
}

function closeDateModal() {
    document.getElementById('dateModal').style.display = "none";
}

function showConfirmationModal() {
    const dateInput = document.getElementById('start_date');
    if (!dateInput.value) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Please select an insurance start date.',
            confirmButtonText: 'OK'
        });
        return;
    }

    selectedDate = dateInput.value;
    const formattedDate = new Date(selectedDate);
    const year = formattedDate.getFullYear().toString().slice(-2);
    const month = (formattedDate.getMonth() + 1).toString().padStart(2, '0');
    const day = formattedDate.getDate().toString().padStart(2, '0');
    const formattedDateString = `${year}/${month}/${day}`;

    document.getElementById('confirmationMessage').textContent =
        `Are you sure you want to submit your insurance application with a start date of ${formattedDateString}?`;

    closeDateModal();
    document.getElementById('confirmModal').style.display = "block";
}

function closeConfirmationModal() {
    document.getElementById('confirmModal').style.display = "none";
}

function submitForm() {
    const form = document.getElementById('insuranceForm');
    const formData = new FormData(form);

    // Format the date and attach it
    const dateInput = document.getElementById('start_date').value;
    if (!dateInput) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please enter a valid date.',
            confirmButtonText: 'OK'
        });
        return;
    }

    const [year, month, day] = dateInput.split("-");
    const formattedDate = `${day}-${month}-${year}`;
    formData.set("start_date", formattedDate);

    fetch(form.action, {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeConfirmationModal();
            showPostSubmissionModal();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: data.message || 'Something went wrong.',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error("Submission error:", error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred.',
            confirmButtonText: 'OK'
        });
    });
}

function showPostSubmissionModal() {
    document.getElementById('postSubmissionModal').style.display = "block";
}

function closePostSubmissionModal() {
    document.getElementById('postSubmissionModal').style.display = "none";
}

function goToDashboard() {
    window.location.href = 'USER_PROFILE/index.php'; // Replace as needed
}

function submitAnotherTransaction() {
    window.location.href = '/new-transaction'; // Replace as needed
}

// Event listeners for modal buttons
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById('submitBtn').addEventListener('click', function (e) {
        e.preventDefault();
        showDateModal();
    });

    document.getElementById('confirmSubmitBtn').addEventListener('click', function (e) {
        e.preventDefault();
        submitForm();
    });

    document.getElementById('cancelSubmitBtn').addEventListener('click', function (e) {
        e.preventDefault();
        closeConfirmationModal();
    });
});
</script>



</head>

<body>

    <header class="header">
        <img src="img/NMG3.png" alt="NMG Insurance Logo" class="logo">
        <h1 class="header-title">Apply for Insurance</h1>
    </header>

    <main class="form-section">
    <form id="insuranceForm" action="../../PHP_Files/User_View/register_insurance.php" method="POST" enctype="multipart/form-data" class="insurance-form">

        <div class="form-column">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" placeholder="Enter first name" required>

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" placeholder="Enter last name" required>

        <label for="mobile">Mobile Number:</label>
        <input type="text" id="mobile" name="mobile" placeholder="Enter mobile number" required>


            <label for="insurance_type">Type of Insurance:</label>
            <select id="insurance_type" name="insurance_type" required onchange="showInsuranceInfo()">
                <option value="">Select Insurance Type</option>
                <option value="TPL">Third Party Liability (TPL) Insurance</option>
                <option value="TPPD">Third Party Property Damage (TPPD) Insurance</option>
            </select>

            <label for="plate_number">Plate Number:</label>
            <input type="text" id="plate_number" name="plate_number" placeholder="Enter your plate number">
            <span class="error-message" id="plateError"></span>

            <label for="mv_file_number">MV File Number (15 chars):</label>
            <input type="text" id="mv_file_number" name="mv_file_number" maxlength="15" placeholder="Enter 15-character MV File Number">
            <span class="error-message" id="mvFileError"></span>

            <label for="brand">Brand:</label>
            <input type="text" id="brand" name="brand" placeholder="e.g. Toyota" required>

            <label for="model">Model:</label>
            <input type="text" id="model" name="model" placeholder="e.g. Vios" required>
        </div>

        <div class="form-column">
            <label for="chassis_number">Chassis Number:</label>
            <input type="text" id="chassis_number" name="chassis_number" required placeholder="Enter chassis number">

            <label for="vehicle_type">Vehicle Type:</label>
            <select id="vehicle_type" name="vehicle_type" required>
                <option value="">Select Vehicle Type</option>
                <option value="Motorcycle">Motorcycle</option>
                <option value="4 Wheels">4 Wheels</option>
                <option value="Truck">Truck</option>
            </select>

            <label for="year">Year:</label>
            <input type="number" id="year" name="year" min="1980" max="2025" placeholder="e.g. 2020" required>

            <label for="color">Color:</label>
            <input type="text" id="color" name="color" placeholder="e.g. Black" required>

            <label for="or_picture">Upload OR Picture:</label>
            <input type="file" id="or_picture" name="or_picture" accept="image/*" required>

            <label for="cr_picture">Upload CR Picture:</label>
            <input type="file" id="cr_picture" name="cr_picture" accept="image/*" required>
        </div>

    
        <label>
  <input type="checkbox" id="useMyInfo" onchange="fillMyInfo(this.checked)">
  Use my saved information
</label>


        <div class="submit-container">
            <button type="button" class="submit-btn" onclick="showDateModal()">Proceed</button>
        </div>
    </form>
</main>

    <footer>
        <p>© 2025 NMG Insurance Agency. All Rights Reserved.</p>
    </footer>

   <!-- Modal for Insurance Info -->
<div id="insuranceModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="modalContent"></div>
    </div>
</div>

<!-- Modal for Date Selection -->
<div id="dateModal" class="modal">
        <div class="modal-content animate__animated animate__fadeInDown">
            <div class="modal-header">
                <h2 class="modal-title">Select Start Date</h2>
                <button class="close" onclick="closeDateModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="input-group">
                    <label for="start_date">Insurance Start Date</label>
                    <div class="date-input-container">
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                    <small class="text-muted">Select the date when your insurance coverage should begin</small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn cancel-btn" onclick="closeDateModal()">Cancel</button>
                <button class="modal-btn confirm-btn" onclick="showConfirmationModal()">Continue</button>
            </div>
        </div>
    </div>

    <!-- Modal for Confirmation -->
    <div id="confirmModal" class="modal">
        <div class="modal-content animate__animated animate__fadeInDown">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Submission</h2>
                <button class="close" onclick="closeConfirmationModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="confirmation-text" id="confirmationMessage"></div>
                <p>Please review all information before submitting. You won't be able to make changes after submission.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn cancel-btn" onclick="closeConfirmationModal()">Go Back</button>
                <button class="modal-btn confirm-btn" onclick="submitForm()">Confirm Submission</button>
            </div>
        </div>
    </div>

 <!-- Modal for Post-Submission Options -->
 <div id="postSubmissionModal" class="modal">
        <div class="modal-content animate__animated animate__fadeInDown">
            <div class="modal-header">
                <h2 class="modal-title">Application Submitted</h2>
                <button class="close" onclick="closePostSubmissionModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="success-icon">✓</div>
                <p style="text-align: center;">Your insurance application has been successfully submitted!</p>
                <p style="text-align: center;">Reference number: <strong id="referenceNumber"></strong></p>
                <p style="text-align: center; font-size: 14px; color: #7f8c8d;">You'll receive a confirmation email shortly.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn neutral-btn" onclick="goToDashboard()">View Dashboard</button>
                <button class="modal-btn confirm-btn" onclick="submitAnotherTransaction()">New Application</button>
            </div>
        </div>
    </div>

<script>
function fillMyInfo(checked) {
  console.log("Checkbox checked:", checked);  // This will log true or false when the checkbox is checked/unchecked
  if (checked) {
    const firstName = "<?= htmlspecialchars($user_first_name ?? '') ?>";
    const lastName = "<?= htmlspecialchars($user_last_name ?? '') ?>";
    const userMobile = "<?= htmlspecialchars($user_mobile ?? '') ?>";

    console.log("First Name:", firstName);  // Check if first name is correctly populated
    console.log("Last Name:", lastName);    // Check if last name is correctly populated
    console.log("Mobile:", userMobile);     // Check if mobile is correctly populated

    document.getElementById('first_name').value = firstName;
    document.getElementById('last_name').value = lastName;
    document.getElementById('mobile').value = userMobile;
  } else {
    document.getElementById('first_name').value = '';
    document.getElementById('last_name').value = '';
    document.getElementById('mobile').value = '';
  }
}



</script>


</body>

</html>