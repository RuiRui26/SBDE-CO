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

// Get client information
$stmt = $pdo->prepare("SELECT client_id, full_name, contact_number FROM clients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Client record not found.']);
    exit;
}

$client_id = $client['client_id'];
$user_name = $client['full_name'] ?? '';
$user_mobile = $client['contact_number'] ?? '';

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
        $insurance_date = $_POST['insurance_date'] ?? null;

        // Validate required fields
        $errors = [];
        if (empty($chassis_number)) $errors[] = "Chassis number is required.";
        if (empty($vehicle_type)) $errors[] = "Vehicle type is required.";
        if (empty($insurance_type)) $errors[] = "Insurance type is required.";
        if (empty($insurance_date)) $errors[] = "Insurance start date is required.";
        
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
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        /* Date modal specific styles */
        #dateModal .modal-content,
        #confirmModal .modal-content {
            text-align: center;
        }

        #dateModal .input-group {
            margin: 20px 0;
        }

        #dateModal label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        #dateModal input[type="date"] {
            padding: 8px;
            width: 100%;
            max-width: 250px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .modal-buttons {
            margin-top: 20px;
        }

        .modal-btn {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .confirm-btn {
            background-color: #4CAF50;
            color: white;
        }

        .cancel-btn {
            background-color: #f44336;
            color: white;
        }

        /* Confirmation modal styles */
        #confirmModal .confirmation-text {
            margin: 20px 0;
            font-size: 18px;
        }
    </style>
    <script>
   // Store selected date for confirmation
   let selectedDate = '';
    
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

    // Show insurance information modal
    function showInsuranceInfo() {
        const type = document.getElementById('insurance_type').value;
        const modal = document.getElementById('insuranceModal');
        const modalContent = document.getElementById('modalContent');

        if (type === "TPL") {
            modalContent.innerHTML = `
                <h2>Third Party Liability (TPL) Insurance</h2>
                <p>TPL insurance covers the policyholder's legal responsibility for causing injury or death to other people (third parties) or damage to their property due to a vehicle-related accident.</p>
                <p><strong>Benefits:</strong></p>
                <ul style="text-align: left;">
                <li>Covers medical expenses for third parties injured in an accident.</li>
                <li>Provides compensation for accidental death or disability of third parties.</li>
                <li>Legal protection against claims arising from third-party injuries or fatalities.</li>
                <li>Mandatory coverage in many regions to legally drive a vehicle.</li>
             </ul>
                <p><strong>Requirements:</strong></p>
                <ul style="text-align: left;">
                <li>Official Receipt (OR)</li>
                <li>Certificate of Registration (CR)</li>
                <li>Smoke Emission</li>
            </ul>
            `;
        } else if (type === "TPPD") {
            modalContent.innerHTML = `
                <h2>Third-Party Property Damage (TPPD) Insurance</h2>
                <p>TPPD insurance specifically covers the cost of repairing or replacing third-party property damaged by your vehicle during an accident.</p>
                <p><strong>Benefits:</strong></p>
                <ul style="text-align: left;">
                <li>Pays for property damage caused to other people's cars, buildings, or infrastructure.</li>
                <li>Reduces financial burden by covering expensive repair costs.</li>
                <li>Legal coverage if the third party takes legal action for property damage.</li>
                <li>Peace of mind knowing accidental damage to others' property is covered.</li>
             </ul>
             <p><strong>Requirements:</strong></p>
                <ul style="text-align: left;">
                <li>Official Receipt (OR)</li>
                <li>Certificate of Registration (CR)</li>
                <li>Smoke Emission</li>
            </ul>
            `;
        } else {
            modal.style.display = "none";
            return;
        }

        modal.style.display = "block";
    }

    // Close the modal
    function closeModal() {
        document.getElementById('insuranceModal').style.display = "none";
    }

    // Show date selection modal
    function showDateModal() {
        if (validateForm()) {
            document.getElementById('dateModal').style.display = "block";
        }
    }

    // Close date modal
    function closeDateModal() {
        document.getElementById('dateModal').style.display = "none";
    }

    // Show confirmation modal
    function showConfirmationModal() {
        const dateInput = document.getElementById('insurance_date');
        if (!dateInput.value) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please select an insurance start date.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Store the selected date
        selectedDate = dateInput.value;
        
        // Format date for display
        const formattedDate = new Date(selectedDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Set confirmation message
        document.getElementById('confirmationMessage').textContent = 
            `Are you sure you want to submit your insurance application with a start date of ${formattedDate}?`;
        
        // Close date modal and show confirmation modal
        closeDateModal();
        document.getElementById('confirmModal').style.display = "block";
    }

    // Close confirmation modal
    function closeConfirmationModal() {
        document.getElementById('confirmModal').style.display = "none";
    }

    // Final form submission
    function submitForm() {
        // Add the date to the form
        const hiddenDateInput = document.createElement('input');
        hiddenDateInput.type = 'hidden';
        hiddenDateInput.name = 'insurance_date';
        hiddenDateInput.value = selectedDate;
        document.getElementById('insuranceForm').appendChild(hiddenDateInput);
        
        // Close confirmation modal
        closeConfirmationModal();
        
        // Submit the form via AJAX
        const form = document.getElementById('insuranceForm');
        const formData = new FormData(form);
        
        // Show loading indicator
        Swal.fire({
            title: 'Processing',
            html: 'Please wait while we submit your application...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    // You can redirect or reset the form here if needed
                    // window.location.href = 'success_page.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while submitting the form.',
                confirmButtonText: 'OK'
            });
            console.error('Error:', error);
        });
    }

    window.onclick = function(event) {
        const insuranceModal = document.getElementById('insuranceModal');
        const dateModal = document.getElementById('dateModal');
        const confirmModal = document.getElementById('confirmModal');
        
        if (event.target == insuranceModal) {
            closeModal();
        }
        
        if (event.target == dateModal) {
            closeDateModal();
        }
        
        if (event.target == confirmModal) {
            closeConfirmationModal();
        }
    };
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
                <label for="name">Full Name:</label>
                <input type="hidden" name="name" value="<?= htmlspecialchars($user_name) ?>">

                <label for="mobile">Mobile Number:</label>
                <input type="hidden" name="mobile" value="<?= htmlspecialchars($user_mobile) ?>">

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

                <label for="or_picture">Upload OR Picture:</label>
                <input type="file" id="or_picture" name="or_picture" accept="image/*" required>

                <label for="cr_picture">Upload CR Picture:</label>
                <input type="file" id="cr_picture" name="cr_picture" accept="image/*" required>
            </div>

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
        <div class="modal-content">
            <span class="close" onclick="closeDateModal()">&times;</span>
            <h2>Select Insurance Start Date</h2>
            
            <div class="input-group">
                <label for="insurance_date">Insurance Start Date:</label>
                <input type="date" id="insurance_date" name="insurance_date" required>
            </div>
            
            <div class="modal-buttons">
                <button class="modal-btn cancel-btn" onclick="closeDateModal()">Cancel</button>
                <button class="modal-btn confirm-btn" onclick="showConfirmationModal()">Continue</button>
            </div>
        </div>
    </div>

    <!-- Modal for Confirmation -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeConfirmationModal()">&times;</span>
            <h2>Confirm Submission</h2>
            
            <p id="confirmationMessage" class="confirmation-text"></p>
            
            <div class="modal-buttons">
                <button class="modal-btn cancel-btn" onclick="closeConfirmationModal()">Cancel</button>
                <button class="modal-btn confirm-btn" onclick="submitForm()">Confirm</button>
            </div>
        </div>
    </div>

</body>

</html>