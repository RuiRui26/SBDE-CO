<?php
session_start();
require_once '../../DB_connection/db.php';
$allowed_roles = ['Client'];
require '../../Logout_Login/Restricted.php';

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
$stmt = $pdo->prepare("SELECT first_name, middle_name, last_name, contact_number FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User record not found.']);
    exit;
}

$user_first_name = htmlspecialchars($user['first_name'] ?? '');
$user_middle_name = htmlspecialchars($user['middle_name'] ?? '');
$user_last_name = htmlspecialchars($user['last_name'] ?? '');
$user_mobile = htmlspecialchars($user['contact_number'] ?? '');

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

$client_id = $client['client_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Start transaction
    $pdo->beginTransaction();

    try {
        // Sanitize and validate input
        $plate_number = !empty($_POST['plate_number']) ? preg_replace('/[^A-Z0-9]/', '', strtoupper($_POST['plate_number'])) : null;
        $mv_file_number = !empty($_POST['mv_file_number']) ? preg_replace('/[^0-9]/', '', $_POST['mv_file_number']) : null;
        $chassis_number = !empty($_POST['chassis_number']) ? preg_replace('/[^A-Z0-9]/', '', strtoupper($_POST['chassis_number'])) : null;
        $vehicle_type = !empty($_POST['vehicle_type']) ? htmlspecialchars($_POST['vehicle_type']) : null;
        $insurance_type = !empty($_POST['insurance_type']) ? htmlspecialchars($_POST['insurance_type']) : null;
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $brand = !empty($_POST['brand']) ? htmlspecialchars($_POST['brand']) : null;
        $model = !empty($_POST['model']) ? htmlspecialchars($_POST['model']) : null;
        $year = !empty($_POST['year']) ? intval($_POST['year']) : null;
        $color = !empty($_POST['color']) ? htmlspecialchars($_POST['color']) : null;
        $is_proxy = ($_POST['is_proxy'] ?? 'no') === 'yes' ? 'yes' : 'no';

        // Validate required fields
        $errors = [];
        if (empty($brand)) $errors[] = "Brand is required.";
        if (empty($model)) $errors[] = "Model is required.";
        if (empty($color)) $errors[] = "Color is required.";
        if (empty($chassis_number)) $errors[] = "Chassis number is required.";
        if (empty($vehicle_type)) $errors[] = "Vehicle type is required.";
        if (empty($insurance_type)) $errors[] = "Insurance type is required.";
        if (empty($start_date)) $errors[] = "Start date is required.";
        
        // Check if at least one identifier is provided
        if (empty($plate_number) && empty($mv_file_number)) {
            $errors[] = "Either plate number or MV file number is required.";
        }

        // Validate file uploads
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        
        if (empty($_FILES['or_picture']['tmp_name'])) {
            $errors[] = "OR picture is required.";
        } elseif (!in_array(mime_content_type($_FILES['or_picture']['tmp_name']), $allowed_mime_types)) {
            $errors[] = "OR picture must be a valid image file (JPEG, PNG, GIF).";
        } elseif ($_FILES['or_picture']['size'] > $max_file_size) {
            $errors[] = "OR picture must be less than 5MB.";
        }

        if (empty($_FILES['cr_picture']['tmp_name'])) {
            $errors[] = "CR picture is required.";
        } elseif (!in_array(mime_content_type($_FILES['cr_picture']['tmp_name']), $allowed_mime_types)) {
            $errors[] = "CR picture must be a valid image file (JPEG, PNG, GIF).";
        } elseif ($_FILES['cr_picture']['size'] > $max_file_size) {
            $errors[] = "CR picture must be less than 5MB.";
        }

        // Validate proxy information if registering as proxy
        if ($is_proxy === 'yes') {
            $proxy_first_name = !empty($_POST['proxy_first_name']) ? htmlspecialchars($_POST['proxy_first_name']) : null;
            $proxy_last_name = !empty($_POST['proxy_last_name']) ? htmlspecialchars($_POST['proxy_last_name']) : null;
            $proxy_relationship = !empty($_POST['proxy_relationship']) ? htmlspecialchars($_POST['proxy_relationship']) : null;
            $proxy_contact = !empty($_POST['proxy_contact']) ? preg_replace('/[^0-9]/', '', $_POST['proxy_contact']) : null;
            $proxy_birthday = !empty($_POST['proxy_birthday']) ? $_POST['proxy_birthday'] : null;
            
            if (empty($proxy_first_name)) $errors[] = "Proxy first name is required.";
            if (empty($proxy_last_name)) $errors[] = "Proxy last name is required.";
            if (empty($proxy_relationship)) $errors[] = "Proxy relationship is required.";
            if (empty($proxy_contact)) $errors[] = "Proxy contact number is required.";
            if (empty($proxy_birthday)) $errors[] = "Proxy birthday is required.";
            
            if ($proxy_relationship === 'Other' && empty($_POST['other_relationship'])) {
                $errors[] = "Please specify proxy relationship.";
            }
            
            if (empty($_FILES['authorization_letter']['tmp_name'])) {
                $errors[] = "Authorization letter is required for proxy registration.";
            } elseif (!in_array(mime_content_type($_FILES['authorization_letter']['tmp_name']), $allowed_mime_types)) {
                $errors[] = "Authorization letter must be a valid image file (JPEG, PNG, GIF).";
            } elseif ($_FILES['authorization_letter']['size'] > $max_file_size) {
                $errors[] = "Authorization letter must be less than 5MB.";
            }
        }

        if (!empty($errors)) {
            throw new Exception(implode(" ", $errors));
        }

        // Check if vehicle exists
        $vehicle_id = null;
        if (!empty($plate_number)) {
            $stmt = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE plate_number = :plate_number");
            $stmt->bindParam(':plate_number', $plate_number, PDO::PARAM_STR);
            $stmt->execute();
            $existing_vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_vehicle) {
                $vehicle_id = $existing_vehicle['vehicle_id'];
            }
        } elseif (!empty($mv_file_number)) {
            $stmt = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE mv_file_number = :mv_file_number");
            $stmt->bindParam(':mv_file_number', $mv_file_number, PDO::PARAM_STR);
            $stmt->execute();
            $existing_vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_vehicle) {
                $vehicle_id = $existing_vehicle['vehicle_id'];
            }
        }

        // If vehicle doesn't exist, create new vehicle record
        if (!$vehicle_id) {
            $stmt = $pdo->prepare("INSERT INTO vehicles 
                                  (client_id, plate_number, vehicle_type, chassis_number, mv_file_number, type_of_insurance, brand, model, year, color) 
                                  VALUES 
                                  (:client_id, :plate_number, :vehicle_type, :chassis_number, :mv_file_number, :insurance_type, :brand, :model, :year, :color)");
            
            $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $stmt->bindParam(':plate_number', $plate_number, PDO::PARAM_STR);
            $stmt->bindParam(':vehicle_type', $vehicle_type, PDO::PARAM_STR);
            $stmt->bindParam(':chassis_number', $chassis_number, PDO::PARAM_STR);
            $stmt->bindParam(':mv_file_number', $mv_file_number, PDO::PARAM_STR);
            $stmt->bindParam(':insurance_type', $insurance_type, PDO::PARAM_STR);
            $stmt->bindParam(':brand', $brand, PDO::PARAM_STR);
            $stmt->bindParam(':model', $model, PDO::PARAM_STR);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->bindParam(':color', $color, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create vehicle record.");
            }
            
            $vehicle_id = $pdo->lastInsertId();
        }

        // Handle file uploads
        $upload_dir = "../../uploads/insurance_docs/";
        
        // Create upload directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception("Failed to create upload directory.");
            }
        }

        // Process OR picture
        $or_picture_path = null;
        if (isset($_FILES['or_picture']) && $_FILES['or_picture']['error'] === UPLOAD_ERR_OK) {
            $file_ext = pathinfo($_FILES['or_picture']['name'], PATHINFO_EXTENSION);
            $or_file_name = "OR_" . $client_id . "_" . time() . "." . $file_ext;
            $or_picture_path = $upload_dir . $or_file_name;
            
            if (!move_uploaded_file($_FILES['or_picture']['tmp_name'], $or_picture_path)) {
                throw new Exception("Failed to upload OR picture.");
            }
        }

        // Process CR picture
        $cr_picture_path = null;
        if (isset($_FILES['cr_picture']) && $_FILES['cr_picture']['error'] === UPLOAD_ERR_OK) {
            $file_ext = pathinfo($_FILES['cr_picture']['name'], PATHINFO_EXTENSION);
            $cr_file_name = "CR_" . $client_id . "_" . time() . "." . $file_ext;
            $cr_picture_path = $upload_dir . $cr_file_name;
            
            if (!move_uploaded_file($_FILES['cr_picture']['tmp_name'], $cr_picture_path)) {
                throw new Exception("Failed to upload CR picture.");
            }
        }

        // Process proxy information if registering as proxy
        $proxy_id = null;
        $authorization_letter_path = null;

        if ($is_proxy === 'yes') {
            // Process authorization letter
            if (isset($_FILES['authorization_letter']) && $_FILES['authorization_letter']['error'] === UPLOAD_ERR_OK) {
                $file_ext = pathinfo($_FILES['authorization_letter']['name'], PATHINFO_EXTENSION);
                $auth_file_name = "AUTH_" . $client_id . "_" . time() . "." . $file_ext;
                $authorization_letter_path = $upload_dir . $auth_file_name;
                
                if (!move_uploaded_file($_FILES['authorization_letter']['tmp_name'], $authorization_letter_path)) {
                    throw new Exception("Failed to upload authorization letter.");
                }
            }
            
            // Insert proxy information
            $proxy_middle_name = !empty($_POST['proxy_middle_name']) ? htmlspecialchars($_POST['proxy_middle_name']) : null;
            $proxy_relationship_final = ($proxy_relationship === 'Other') ? 
                htmlspecialchars($_POST['other_relationship']) : $proxy_relationship;
            
            $stmt = $pdo->prepare("INSERT INTO proxies 
                                  (user_id, client_id, first_name, middle_name, last_name, birthday, relationship, contact_number, authorization_letter_path) 
                                  VALUES 
                                  (:user_id, :client_id, :first_name, :middle_name, :last_name, :birthday, :relationship, :contact_number, :auth_letter_path)");
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $stmt->bindParam(':first_name', $proxy_first_name, PDO::PARAM_STR);
            $stmt->bindParam(':middle_name', $proxy_middle_name, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $proxy_last_name, PDO::PARAM_STR);
            $stmt->bindParam(':birthday', $proxy_birthday, PDO::PARAM_STR);
            $stmt->bindParam(':relationship', $proxy_relationship_final, PDO::PARAM_STR);
            $stmt->bindParam(':contact_number', $proxy_contact, PDO::PARAM_STR);
            $stmt->bindParam(':auth_letter_path', $authorization_letter_path, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to save proxy information.");
            }
            
            $proxy_id = $pdo->lastInsertId();
        }

        // Insert insurance registration
        $stmt = $pdo->prepare("INSERT INTO insurance_registration 
                              (client_id, vehicle_id, proxy_id, type_of_insurance, or_picture, cr_picture, start_date, created_at)
                              VALUES 
                              (:client_id, :vehicle_id, :proxy_id, :type_of_insurance, :or_picture, :cr_picture, :start_date, NOW())");
        
        $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->bindParam(':vehicle_id', $vehicle_id, PDO::PARAM_INT);
        $stmt->bindParam(':proxy_id', $proxy_id, PDO::PARAM_INT);
        $stmt->bindParam(':type_of_insurance', $insurance_type, PDO::PARAM_STR);
        $stmt->bindParam(':or_picture', $or_picture_path, PDO::PARAM_STR);
        $stmt->bindParam(':cr_picture', $cr_picture_path, PDO::PARAM_STR);
        $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
        
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
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Delete uploaded files if transaction failed
        if (isset($or_picture_path) && file_exists($or_picture_path)) {
            @unlink($or_picture_path);
        }
        if (isset($cr_picture_path) && file_exists($cr_picture_path)) {
            @unlink($cr_picture_path);
        }
        if (isset($authorization_letter_path) && file_exists($authorization_letter_path)) {
            @unlink($authorization_letter_path);
        }
        
        error_log("Insurance registration error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    exit;
}
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
</head>

<body>

<?php include 'nav.php'; ?>

    <main class="form-section">
        <div class="welcome-message">
            <h2>Welcome!</h2>
            <p>A quick step before we continue—please provide your information.</p>
            <div class="step-progress">
                <div class="step active" id="progress-step1">
                    <div class="step-number">1</div>
                    <div class="step-title">Select Types of Insurance</div>
                </div>
                <div class="step" id="progress-step2">
                    <div class="step-number">2</div>
                    <div class="step-title">Fillup Personal Informations</div>
                </div>
                <div class="step" id="progress-step3">
                    <div class="step-number">3</div>
                    <div class="step-title">Input Vehicle Information</div>
                </div>
                <div class="step" id="progress-step4">
                    <div class="step-number">4</div>
                    <div class="step-title">Submit</div>
                </div>
            </div>
            <div class="back-button-container">
                <button type="button" class="back-button" onclick="goBack()">
                    &larr; Back to Previous Page
                </button>
            </div>
        </div>

        <div class="form-container">
        <form id="insuranceForm" action="../../PHP_Files/User_View/register_insurance.php" method="POST" enctype="multipart/form-data" class="insurance-form">
                <!-- First Step: Type of Insurance -->
                <div class="form-step active" id="step1">
                    <h3>Step 1: Insurance Type</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="insurance_type" class="required">Type of Insurance</label>
                            <select id="insurance_type" name="insurance_type" required onchange="showInsuranceInfo(); updateNextButtonState();">
                                <option value="">Select Insurance Type</option>
                                <option value="TPL">Third Party Liability (TPL)</option>
                                <option value="TPPD">Third Party Property Damage (TPPD)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" id="step1NextBtn" disabled onclick="goToStep(2)">Next</button>
                    </div>
                </div>

                <!-- Second Step: Personal Information -->
                <div class="form-step" id="step2">
                    <h3>Step 2: Personal Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name" class="required">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo $user_first_name; ?>" required readonly>
                        </div>
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="<?php echo $user_middle_name; ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="required">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo $user_last_name; ?>" required readonly>
                        </div>
                        <div class="form-group">
                            <label for="mobile" class="required">Mobile Number</label>
                            <input type="text" id="mobile" name="mobile" value="<?php echo $user_mobile; ?>" required readonly>
                        </div>
                        
                        <!-- Proxy Registration Section -->
                        <div class="form-group full-width">
                            <label for="is_proxy">Are you registering as a proxy?</label>
                            <select id="is_proxy" name="is_proxy" onchange="toggleProxyFields()">
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>
                        
                        <div id="proxyFields" class="form-grid" style="display: none;">
                            <div class="form-group">
                                <label for="proxy_first_name" class="required">First Name</label>
                                <input type="text" id="proxy_first_name" name="proxy_first_name" onblur="validateInputForXSS(this)">
                            </div>
                            <div class="form-group">
                                <label for="proxy_middle_name">Middle Name (Optional)</label>
                                <input type="text" id="proxy_middle_name" name="proxy_middle_name" onblur="validateInputForXSS(this)">
                            </div>
                            <div class="form-group">
                                <label for="proxy_last_name" class="required">Last Name</label>
                                <input type="text" id="proxy_last_name" name="proxy_last_name" onblur="validateInputForXSS(this)">
                            </div>
                            <div class="form-group">
                                <label for="proxy_birthday" class="required">Birthday</label>
                                <input type="date" id="proxy_birthday" name="proxy_birthday" onchange="checkProxyAge()">
                                <div id="proxyAgeValidation" class="age-validation"></div>
                            </div>
                            <div class="form-group">
                                <label for="proxy_relationship" class="required">Relationship</label>
                                <select id="proxy_relationship" name="proxy_relationship" onchange="toggleOtherRelationship()">
                                    <option value="">Select Relationship</option>
                                    <option value="Relative">Relative</option>
                                    <option value="Friend">Friend</option>
                                    <option value="Representative">Representative</option>
                                    <option value="Other">Other (please specify)</option>
                                </select>
                            </div>
                            <div class="form-group" id="otherRelationshipContainer" style="display: none;">
                                <label for="other_relationship" class="required">Specify Relationship</label>
                                <input type="text" id="other_relationship" name="other_relationship" onblur="validateInputForXSS(this)">
                            </div>
                            <div class="form-group">
                                <label for="proxy_contact" class="required">Contact Number</label>
                                <input type="text" id="proxy_contact" name="proxy_contact" onblur="validateInputForXSS(this)">
                            </div>
                            <div class="form-group full-width">
                                <label for="authorization_letter" class="required">Authorization Letter (Image)</label>
                                <input type="file" id="authorization_letter" name="authorization_letter" accept="image/*" onchange="validateAuthorizationLetter()">
                                <small>Upload a clear image of the signed authorization letter</small>
                                <span class="error-message" id="authorizationLetterError"></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" onclick="goToStep(1)">Back</button>
                        <button type="button" id="step2NextBtn" onclick="goToStep(3)">Next</button>
                    </div>
                </div>

                <!-- Third Step: Vehicle Information and Document Upload -->
                <div class="form-step" id="step3">
                    <h3>Step 3: Vehicle Information and Document Upload</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="plate_number">Plate Number</label>
                            <input type="text" id="plate_number" name="plate_number" placeholder="Enter plate number" oninput="validateIdentifierFields()" onblur="validateInputForXSS(this)">
                            <span class="error-message" id="plateError"></span>
                        </div>
                        <div class="form-group">
                            <label for="mv_file_number">MV File Number</label>
                            <input type="text" id="mv_file_number" name="mv_file_number" maxlength="15" placeholder="15-character MV File" oninput="validateIdentifierFields()" onblur="validateInputForXSS(this)">
                            <span class="error-message" id="mvFileError"></span>
                        </div>
                        <div class="form-group">
                            <label for="brand" class="required">Brand</label>
                            <input type="text" id="brand" name="brand" placeholder="e.g. Toyota" required oninput="updateNextButtonState()" onblur="validateInputForXSS(this)">
                            <span class="error-message" id="brandError"></span>
                        </div>
                        <div class="form-group">
                            <label for="model" class="required">Model</label>
                            <input type="text" id="model" name="model" placeholder="e.g. Corolla" required oninput="updateNextButtonState()" onblur="validateInputForXSS(this)">
                            <span class="error-message" id="modelError"></span>
                        </div>
                        <div class="form-group">
                            <label for="year" class="required">Year</label>
                            <input type="number" id="year" name="year" placeholder="e.g. 2020" min="1900" max="2099" step="1" required oninput="updateNextButtonState()" onblur="validateYear()">
                            <span class="error-message" id="yearError"></span>
                        </div>
                        <div class="form-group">
                            <label for="vehicle_type" class="required">Vehicle Type</label>
                            <select id="vehicle_type" name="vehicle_type" required onchange="updateNextButtonState()">
                                <option value="">Select Vehicle Type</option>
                                <option value="Motorcycle">Motorcycle</option>
                                <option value="4 Wheels">4 Wheels</option>
                                <option value="Truck">Truck</option>
                            </select>
                            <span class="error-message" id="vehicleTypeError"></span>
                        </div>
                        <div class="form-group">
                            <label for="color" class="required">Color</label>
                            <input type="text" id="color" name="color" placeholder="e.g. Red" required oninput="updateNextButtonState()" onblur="validateInputForXSS(this)">
                            <span class="error-message" id="colorError"></span>
                        </div>
                        <div class="form-group">
                            <label for="chassis_number" class="required">Chassis Number</label>
                            <input type="text" id="chassis_number" name="chassis_number" placeholder="Enter chassis number" required oninput="updateNextButtonState()" onblur="validateInputForXSS(this)">
                            <span class="error-message" id="chassisError"></span>
                        </div>
                        <div class="form-group">
                            <label for="or_picture" class="required">OR Picture</label>
                            <input type="file" id="or_picture" name="or_picture" accept="image/*" required onchange="validateFileUpload(this, 'OR'); updateNextButtonState()">
                            <span class="error-message" id="orPictureError"></span>
                        </div>
                        <div class="form-group">
                            <label for="cr_picture" class="required">CR Picture</label>
                            <input type="file" id="cr_picture" name="cr_picture" accept="image/*" required onchange="validateFileUpload(this, 'CR'); updateNextButtonState()">
                            <span class="error-message" id="crPictureError"></span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" onclick="goToStep(2)">Back</button>
                        <button type="button" id="submitBtn" disabled onclick="showDateModal()">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </main>

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
                    <label for="start_date" class="required">Insurance Start Date</label>
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
// Global variables
let currentStep = 1;
const totalSteps = 3;
let isProxyAgeValid = false;

// XSS Protection Functions
function validateInputForXSS(inputElement) {
    const value = inputElement.value;
    if (/<script.*?>|<\/script>|javascript:/i.test(value)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Input',
            text: 'The input contains potentially harmful content. Please try again.',
            confirmButtonText: 'OK'
        }).then(() => {
            inputElement.value = '';
            inputElement.focus();
        });
        return false;
    }
    return true;
}

// Initialize the form
document.addEventListener("DOMContentLoaded", function() {
    updateNextButtonState();
    updateProgressIndicator();
    
    // Initialize proxy fields
    toggleProxyFields();
    toggleOtherRelationship();
    
    // Set maximum date for birthday (must be at least 18 years old)
    const today = new Date();
    const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
    document.getElementById('proxy_birthday').max = maxDate.toISOString().split('T')[0];
    
    // Add event listeners for XSS validation on all input fields
    const inputs = document.querySelectorAll('input[type="text"], input[type="date"], textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateInputForXSS(this);
        });
    });
});

// Navigation functions
function goToStep(step) {
    if (step < 1 || step > totalSteps) return;
    
    // Validate before proceeding to next step
    if (step > currentStep && !validateCurrentStep()) {
        return;
    }
    
    // Hide current step
    document.getElementById(`step${currentStep}`).classList.remove('active');
    
    // Show new step
    document.getElementById(`step${step}`).classList.add('active');
    currentStep = step;
    
    // Update button states
    updateNextButtonState();
    updateProgressIndicator();
}

function goBack() {
    if (document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
        window.location.href = document.referrer;
    } else {
        window.location.href = 'index.php';
    }
}

// Update the progress indicator to highlight current step
function updateProgressIndicator() {
    // Reset all steps
    document.querySelectorAll('.step-progress .step').forEach(step => {
        step.classList.remove('active', 'completed');
    });
    
    // Mark previous steps as completed
    for (let i = 1; i < currentStep; i++) {
        const stepElement = document.getElementById(`progress-step${i}`);
        if (stepElement) {
            stepElement.classList.add('completed');
        }
    }
    
    // Mark current step as active
    const currentStepElement = document.getElementById(`progress-step${currentStep}`);
    if (currentStepElement) {
        currentStepElement.classList.add('active');
    }
    
    // Special case for submission step (step 4)
    if (currentStep === 3 && document.getElementById('submitBtn').disabled === false) {
        document.getElementById('progress-step4').classList.add('active');
    }
}

// Form validation functions
function validateCurrentStep() {
    switch(currentStep) {
        case 1:
            return validateStep1();
        case 2:
            return validateStep2();
        case 3:
            return validateStep3();
        default:
            return true;
    }
}

function validateStep1() {
    const insuranceType = document.getElementById('insurance_type').value;
    if (!insuranceType) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please select an insurance type to continue.',
            confirmButtonText: 'OK'
        });
        return false;
    }
    return true;
}

function validateStep2() {
    const isProxy = document.getElementById('is_proxy').value === 'yes';
    
    // Validate all text inputs for XSS
    const textInputs = document.querySelectorAll('#step2 input[type="text"]');
    for (const input of textInputs) {
        if (!validateInputForXSS(input)) {
            return false;
        }
    }
    
    if (isProxy) {
        // Validate proxy fields
        const requiredFields = [
            'proxy_first_name', 'proxy_last_name', 
            'proxy_relationship', 'proxy_contact',
            'proxy_birthday', 'authorization_letter'
        ];
        
        let isValid = true;
        
        requiredFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                isValid = false;
            }
        });
        
        // Check if "Other" is selected but not specified
        if (document.getElementById('proxy_relationship').value === 'Other' && 
            !document.getElementById('other_relationship').value.trim()) {
            isValid = false;
        }
        
        // Check if age is valid
        if (!isProxyAgeValid) {
            isValid = false;
        }
        
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'Please fill all required proxy information fields and ensure the proxy is at least 18 years old.',
                confirmButtonText: 'OK'
            });
            return false;
        }
    }
    
    return true;
}

function validateStep3() {
    let isValid = true;
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    
    // Validate all text inputs for XSS
    const textInputs = document.querySelectorAll('#step3 input[type="text"]');
    for (const input of textInputs) {
        if (!validateInputForXSS(input)) {
            isValid = false;
        }
    }
    
    // Validate at least one identifier
    const plateNumber = document.getElementById("plate_number").value.trim();
    const mvFileNumber = document.getElementById("mv_file_number").value.trim();
    
    if (!plateNumber && !mvFileNumber) {
        document.getElementById("mvFileError").textContent = "Either MV File Number or Plate Number is required.";
        document.getElementById("plateError").textContent = "Either MV File Number or Plate Number is required.";
        isValid = false;
    }
    
    if (mvFileNumber && !/^\d{15}$/.test(mvFileNumber)) {
        document.getElementById("mvFileError").textContent = "MV File Number must be exactly 15 digits (numbers only).";
        isValid = false;
    }
    
    // Validate required fields
    const requiredFields = [
        'brand', 'model', 'vehicle_type', 'color', 'chassis_number',
        'or_picture', 'cr_picture'
    ];
    
    requiredFields.forEach(field => {
        const element = document.getElementById(field);
        const errorElement = document.getElementById(`${field}Error`) || element.parentNode.querySelector('.error-message');
        
        if (element.type === 'file') {
            if (!element.files || element.files.length === 0) {
                if (errorElement) errorElement.textContent = 'This field is required';
                isValid = false;
            }
        } else if (!element.value.trim()) {
            if (errorElement) errorElement.textContent = 'This field is required';
            isValid = false;
        }
    });
    
    if (!isValid) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please fill all required fields correctly.',
            confirmButtonText: 'OK'
        });
    }
    
    return isValid;
}

function validateIdentifierFields() {
    const plateNumber = document.getElementById("plate_number").value.trim();
    const mvFileNumber = document.getElementById("mv_file_number").value.trim();
    const mvFileError = document.getElementById("mvFileError");
    const plateError = document.getElementById("plateError");

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

function validateFileUpload(input, type) {
    const errorElement = document.getElementById(`${input.id}Error`);
    errorElement.textContent = '';
    
    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        // Check file type
        if (!validTypes.includes(file.type)) {
            errorElement.textContent = `${type} must be a valid image file (JPEG, PNG, GIF)`;
            input.value = '';
            return false;
        }
        
        // Check file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            errorElement.textContent = `${type} image must be less than 5MB`;
            input.value = '';
            return false;
        }
    }
    
    return true;
}

function validateAuthorizationLetter() {
    const input = document.getElementById('authorization_letter');
    const errorElement = document.getElementById('authorizationLetterError');
    errorElement.textContent = '';
    
    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        // Check file type
        if (!validTypes.includes(file.type)) {
            errorElement.textContent = 'Authorization letter must be a valid image file (JPEG, PNG, GIF)';
            input.value = '';
            return false;
        }
        
        // Check file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            errorElement.textContent = 'Authorization letter must be less than 5MB';
            input.value = '';
            return false;
        }
    } else {
        errorElement.textContent = 'Authorization letter is required for proxy registration';
        return false;
    }
    
    return true;
}

function checkProxyAge() {
    const birthdayInput = document.getElementById('proxy_birthday');
    const ageValidation = document.getElementById('proxyAgeValidation');
    
    if (!birthdayInput.value) {
        ageValidation.textContent = '';
        isProxyAgeValid = false;
        return;
    }
    
    const birthday = new Date(birthdayInput.value);
    const today = new Date();
    let age = today.getFullYear() - birthday.getFullYear();
    const monthDiff = today.getMonth() - birthday.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
        age--;
    }
    
    if (age >= 18) {
        ageValidation.textContent = `Age: ${age} (Valid)`;
        ageValidation.className = 'age-validation valid';
        isProxyAgeValid = true;
    } else {
        ageValidation.textContent = `Age: ${age} (Must be at least 18 years old)`;
        ageValidation.className = 'age-validation invalid';
        isProxyAgeValid = false;
    }
}

// Update button states based on form validity
function updateNextButtonState() {
    // Step 1 next button
    const step1NextBtn = document.getElementById('step1NextBtn');
    if (step1NextBtn) {
        step1NextBtn.disabled = !document.getElementById('insurance_type').value;
    }
    
    // Step 3 submit button
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        const requiredFields = [
            'brand', 'model', 'vehicle_type', 'color', 'chassis_number',
            'or_picture', 'cr_picture'
        ];
        
        let allFilled = true;
        requiredFields.forEach(field => {
            const element = document.getElementById(field);
            if (element.type === 'file') {
                if (!element.files || element.files.length === 0) {
                    allFilled = false;
                }
            } else if (!element.value.trim()) {
                allFilled = false;
            }
        });
        
        // Also need at least one identifier
        const plateNumber = document.getElementById("plate_number").value.trim();
        const mvFileNumber = document.getElementById("mv_file_number").value.trim();
        const hasIdentifier = plateNumber || mvFileNumber;
        
        submitBtn.disabled = !(allFilled && hasIdentifier);
        
        // Update progress indicator when submit button becomes enabled
        if (!submitBtn.disabled) {
            document.getElementById('progress-step4').classList.add('active');
        } else {
            document.getElementById('progress-step4').classList.remove('active');
        }
    }
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

// Toggle proxy information fields
function toggleProxyFields() {
    const isProxy = document.getElementById('is_proxy').value === 'yes';
    document.getElementById('proxyFields').style.display = isProxy ? 'grid' : 'none';
    
    // Clear fields when hiding
    if (!isProxy) {
        document.getElementById('proxy_first_name').value = '';
        document.getElementById('proxy_middle_name').value = '';
        document.getElementById('proxy_last_name').value = '';
        document.getElementById('proxy_relationship').value = '';
        document.getElementById('other_relationship').value = '';
        document.getElementById('proxy_contact').value = '';
        document.getElementById('proxy_birthday').value = '';
        document.getElementById('authorization_letter').value = '';
        document.getElementById('proxyAgeValidation').textContent = '';
    }
}

// Toggle other relationship field
function toggleOtherRelationship() {
    const isOther = document.getElementById('proxy_relationship').value === 'Other';
    document.getElementById('otherRelationshipContainer').style.display = isOther ? 'block' : 'none';
    if (!isOther) {
        document.getElementById('other_relationship').value = '';
    }
}

function showDateModal() {
    if (validateCurrentStep()) {
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
    if (isNaN(formattedDate)) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Invalid date format. Please try again.',
            confirmButtonText: 'OK'
        });
        return;
    }

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
            
            // Generate a reference number (you might want to use the actual ID from the database)
            const refNumber = 'REF-' + Math.floor(100000 + Math.random() * 900000);
            document.getElementById('referenceNumber').textContent = refNumber;
            
            // Update progress indicator to show completion
            document.querySelectorAll('.step-progress .step').forEach(step => {
                step.classList.remove('active');
                step.classList.add('completed');
            });
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
    window.location.href = 'USER_PROFILE/index.php';
}

function submitAnotherTransaction() {
    // Reset form and go back to step 1
    document.getElementById('insuranceForm').reset();
    document.querySelectorAll('.form-step').forEach(step => {
        step.classList.remove('active');
    });
    document.getElementById('step1').classList.add('active');
    currentStep = 1;
    updateNextButtonState();
    closePostSubmissionModal();
    
    // Clear file inputs (they don't reset with form.reset())
    document.getElementById('or_picture').value = '';
    document.getElementById('cr_picture').value = '';
    document.getElementById('authorization_letter').value = ''; 
    
    // Reset proxy fields
    document.getElementById('is_proxy').value = 'no';
    toggleProxyFields();
    
    // Reset progress indicator
    document.querySelectorAll('.step-progress .step').forEach(step => {
        step.classList.remove('active', 'completed');
    });
    document.getElementById('progress-step1').classList.add('active');
}
</script>

</body>
</html>