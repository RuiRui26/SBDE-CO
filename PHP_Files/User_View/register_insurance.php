<?php
session_start();
require_once '../../DB_connection/db.php';

$allowed_roles = ['Client'];
require '../../Logout_Login_USER/Restricted.php';

$database = new Database();
$conn = $database->getConnection();
header("Content-Type: application/json");

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

// Verify that the user is a Client
$stmt = $conn->prepare("SELECT role FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'Client') {
    echo json_encode(["success" => false, "message" => "Only clients can apply for insurance."]);
    exit;
}

// Retrieve client details
$stmt = $conn->prepare("SELECT client_id, full_name, contact_number FROM clients WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    echo json_encode(["success" => false, "message" => "Client record not found."]);
    exit;
}

$client_id = $client['client_id'];
$user_name = $client['full_name'];
$mobile = $client['contact_number'];

if (!$user_name || !$mobile) {
    echo json_encode(["success" => false, "message" => "Client data is incomplete. Please update your profile."]);
    exit;
}

// Retrieve form data
$plate_number = $_POST['plate_number'] ?? null;
$mv_file_number = $_POST['mv_file_number'] ?? null;
$insurance_type = $_POST['insurance_type'] ?? null;
$chassis_number = $_POST['chassis_number'] ?? null;
$vehicle_type = $_POST['vehicle_type'] ?? null;
$brand = $_POST['brand'] ?? null;
$model = $_POST['model'] ?? null;
$year = $_POST['year'] ?? null;
$color = $_POST['color'] ?? null;
$start_date = $_POST['start_date'] ?? null; // Assuming the start date is passed in the form

// Proxy Information
$proxy_id = $_POST['proxy_id'] ?? null;  // Optional proxy ID if applicable
$authorization_letter = $_FILES['authorization_letter'] ?? null;  // Optional proxy authorization letter

// Validate start date
if ($start_date) {
    $current_date = date('Y-m-d'); // Current date in Y-m-d format
    $dateParts = explode('-', $start_date);
    if (count($dateParts) === 3) {
        $day = $dateParts[0];
        $month = $dateParts[1];
        $year = $dateParts[2];
        
        if (checkdate($month, $day, $year)) {
            $start_date = "$year-$month-$day"; // Convert to YYYY-MM-DD
        } else {
            echo json_encode(["success" => false, "message" => "Invalid date format."]);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid date format. Please use DD-MM-YYYY."]);
        exit;
    }

    // Check if the start date is in the past
    if ($start_date < $current_date) {
        echo json_encode(["success" => false, "message" => "Start date must be today or a future date."]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Start date is required."]);
    exit;
}

// Ensure at least one identifier (Plate Number or MV File Number) is given
if (!$plate_number && !$mv_file_number) {
    echo json_encode(["success" => false, "message" => "Either MV File Number or Plate Number is required."]);
    exit;
}

// Validate MV File Number format
if ($mv_file_number && !preg_match("/^\d{15}$/", $mv_file_number)) {
    echo json_encode(["success" => false, "message" => "MV File Number must be exactly 15 digits."]);
    exit;
}

// File upload directory
$uploadDir = '../../secured_uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// File Upload Function for Proxy's Authorization Letter
function processAuthorizationLetter($file, $uploadDir, $client_name) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Invalid file type for authorization letter. Only JPG, JPEG, PNG, and PDF are allowed.");
    }

    $safe_name = preg_replace('/[^a-zA-Z0-9-_]/', '_', $client_name);
    $filename = "{$safe_name}_auth_" . time() . "." . $fileExtension;

    $destination = $uploadDir . 'authorization_letters/' . $filename;

    if (!is_dir($uploadDir . 'authorization_letters')) {
        mkdir($uploadDir . 'authorization_letters', 0777, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to upload authorization letter.");
    }

    return $filename;
}

// Process proxy's authorization letter upload
try {
    $authorization_letter_filename = processAuthorizationLetter($authorization_letter, $uploadDir, $user_name);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}

try {
    $conn->beginTransaction();

    // Insert vehicle details
    $stmt = $conn->prepare("
        INSERT INTO vehicles (client_id, plate_number, mv_file_number, chassis_number, vehicle_type, brand, model, year, color)
        VALUES (:client_id, :plate_number, :mv_file_number, :chassis_number, :vehicle_type, :brand, :model, :year, :color)
    ");
    $stmt->execute([
        'client_id' => $client_id,
        'plate_number' => !empty($plate_number) ? $plate_number : null,
        'mv_file_number' => !empty($mv_file_number) ? $mv_file_number : null,
        'chassis_number' => !empty($chassis_number) ? $chassis_number : null,
        'vehicle_type' => $vehicle_type,
        'brand' => $brand,
        'model' => $model,
        'year' => $year,
        'color' => $color
    ]);

    // Retrieve the latest vehicle_id
    $stmt = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE client_id = :client_id ORDER BY vehicle_id DESC LIMIT 1");
    $stmt->execute(['client_id' => $client_id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) {
        throw new Exception("Vehicle ID not found.");
    }

    $vehicle_id = $vehicle['vehicle_id'];

    // Insert insurance registration data (with calculated expired_at)
    $stmt = $conn->prepare("
    INSERT INTO insurance_registration (client_id, vehicle_id, start_date, insurance_type, proxy_id, authorization_letter, expired_at)
    VALUES (:client_id, :vehicle_id, :start_date, :insurance_type, :proxy_id, :authorization_letter, :expired_at)
    ");
    $stmt->execute([
        'client_id' => $client_id,
        'vehicle_id' => $vehicle_id,
        'start_date' => $start_date,
        'insurance_type' => $insurance_type,
        'proxy_id' => $proxy_id,
        'authorization_letter' => $authorization_letter_filename ?? null,
        'expired_at' => $expired_at // Add expired_at value here
    ]);

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Insurance registration successful."]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
