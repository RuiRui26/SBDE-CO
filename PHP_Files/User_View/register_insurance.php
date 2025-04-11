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

$stmt = $conn->prepare("
    SELECT 1 FROM insurance_registration ir
    JOIN vehicles v ON ir.vehicle_id = v.vehicle_id
    WHERE ir.client_id = :client_id
    AND (v.plate_number = :plate_number OR v.chassis_number = :chassis_number)
");
$stmt->execute([
    'client_id' => $client_id,
    'plate_number' => $plate_number,
    'chassis_number' => $chassis_number
]);
$existingApplication = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingApplication) {
    echo json_encode(["success" => false, "message" => "You already submitted an insurance application for this vehicle (plate or chassis already used)."]);
    exit;
}

// File upload directory
$uploadDir = '../../secured_uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// File Upload Function
function processFileUpload($file, $prefix, $uploadDir, $client_name) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Invalid file type for $prefix. Only JPG, JPEG, PNG, and PDF are allowed.");
    }

    $safe_name = preg_replace('/[^a-zA-Z0-9-_]/', '_', $client_name);
    $filename = "{$safe_name}_{$prefix}_" . time() . "." . $fileExtension;

    $subdir = ($prefix === 'OR') ? 'or' : 'cr'; 
    $destination = $uploadDir . $subdir . '/' . $filename;

    if (!is_dir($uploadDir . $subdir)) {
        mkdir($uploadDir . $subdir, 0777, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to upload $prefix picture.");
    }

    return $filename;
}

// Process file uploads
try {
    $or_filename = processFileUpload($_FILES['or_picture'], "OR", $uploadDir, $user_name);
    $cr_filename = processFileUpload($_FILES['cr_picture'], "CR", $uploadDir, $user_name);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}

try {
    $conn->beginTransaction();

    // Ensure vehicle entry exists
    $stmt = $conn->prepare("
        INSERT INTO vehicles (client_id, plate_number, mv_file_number, chassis_number, vehicle_type, brand, model, year, color)
        VALUES (:client_id, :plate_number, :mv_file_number, :chassis_number, :vehicle_type, :brand, :model, :year, :color)
        ON DUPLICATE KEY UPDATE 
            plate_number = IF(VALUES(plate_number) IS NOT NULL, VALUES(plate_number), plate_number),
            mv_file_number = IF(VALUES(mv_file_number) IS NOT NULL, VALUES(mv_file_number), mv_file_number),
            chassis_number = IF(VALUES(chassis_number) IS NOT NULL, VALUES(chassis_number), chassis_number),
            vehicle_type = IF(VALUES(vehicle_type) IS NOT NULL, VALUES(vehicle_type), vehicle_type),
            brand = IF(VALUES(brand) IS NOT NULL, VALUES(brand), brand),
            model = IF(VALUES(model) IS NOT NULL, VALUES(model), model),
            year = IF(VALUES(year) IS NOT NULL, VALUES(year), year),
            color = IF(VALUES(color) IS NOT NULL, VALUES(color), color)
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

    // Insert OR document if uploaded
    if ($or_filename) {
        $stmt = $conn->prepare("
            INSERT INTO documents (client_id, vehicle_id, document_type, file_path)
            VALUES (:client_id, :vehicle_id, 'OR', :file_path)
        ");
        $stmt->execute([
            'client_id' => $client_id,
            'vehicle_id' => $vehicle_id,
            'file_path' => $or_filename
        ]);
        // Get the inserted document_id for OR
        $or_document_id = $conn->lastInsertId();
    }

    // Insert CR document if uploaded
    if ($cr_filename) {
        $stmt = $conn->prepare("
            INSERT INTO documents (client_id, vehicle_id, document_type, file_path)
            VALUES (:client_id, :vehicle_id, 'CR', :file_path)
        ");
        $stmt->execute([
            'client_id' => $client_id,
            'vehicle_id' => $vehicle_id,
            'file_path' => $cr_filename
        ]);
        // Get the inserted document_id for CR
        $cr_document_id = $conn->lastInsertId();
    }

    // Insert insurance registration data
    $stmt = $conn->prepare("
    INSERT INTO insurance_registration (client_id, vehicle_id, type_of_insurance, or_picture, cr_picture, start_date, document_id)
    VALUES (:client_id, :vehicle_id, :type_of_insurance, :or_picture, :cr_picture, :start_date, :document_id)
");
$stmt->execute([
    'client_id' => $client_id,
    'vehicle_id' => $vehicle_id,
    'type_of_insurance' => $insurance_type, // Correct column name here
    'or_picture' => $or_filename,
    'cr_picture' => $cr_filename,
    'start_date' => $start_date,
    'document_id' => $or_document_id // Store document ID here
]);

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Insurance registration successful."]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}

?>
