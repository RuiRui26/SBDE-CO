<?php

require '../../DB_connection/db.php';

$database = new Database();
$conn = $database->getConnection();
session_start();
header("Content-Type: application/json");

// Check if user is logged in
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
$or_picture = $_FILES['or_picture'] ?? null;
$cr_picture = $_FILES['cr_picture'] ?? null;

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

// ✅ Check if client has already submitted an insurance application
$stmt = $conn->prepare("
    SELECT 1 FROM insurance_registration 
    WHERE client_id = :client_id
");
$stmt->execute(['client_id' => $client_id]);
$existingApplication = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingApplication) {
    echo json_encode(["success" => false, "message" => "You have already submitted an insurance application."]);
    exit;
}

// File upload directory
$uploadDir = '../../secured_uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// File Upload Function (Now includes client's full name)
function processFileUpload($file, $prefix, $uploadDir, $client_name) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Invalid file type for $prefix. Only JPG, JPEG, PNG, and PDF are allowed.");
    }

    // Sanitize client name for file naming
    $safe_name = preg_replace('/[^a-zA-Z0-9-_]/', '_', $client_name);

    // Construct the new filename: [ClientName]_[Prefix]_[Timestamp].[Extension]
    $filename = "{$safe_name}_{$prefix}_" . time() . "." . $fileExtension;
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to upload $prefix picture.");
    }

    return $filename;
}

// Process file uploads (now includes the client name)
try {
    $or_filename = processFileUpload($or_picture, "OR", $uploadDir, $user_name);
    $cr_filename = processFileUpload($cr_picture, "CR", $uploadDir, $user_name);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}


try {
    $conn->beginTransaction();

    // ✅ Ensure vehicle entry exists
    $stmt = $conn->prepare("
    INSERT INTO vehicles (client_id, plate_number, mv_file_number, chassis_number)
    VALUES (:client_id, :plate_number, :mv_file_number, :chassis_number)
    ON DUPLICATE KEY UPDATE 
        plate_number = IF(VALUES(plate_number) IS NOT NULL, VALUES(plate_number), plate_number),
        mv_file_number = IF(VALUES(mv_file_number) IS NOT NULL, VALUES(mv_file_number), mv_file_number),
        chassis_number = IF(VALUES(chassis_number) IS NOT NULL, VALUES(chassis_number), chassis_number)
");

$stmt->execute([
    'client_id' => $client_id,
    'plate_number' => !empty($plate_number) ? $plate_number : null,
    'mv_file_number' => !empty($mv_file_number) ? $mv_file_number : null,
    'chassis_number' => !empty($chassis_number) ? $chassis_number : null
]);


    // ✅ Retrieve the latest vehicle_id
    $stmt = $conn->prepare("
        SELECT vehicle_id FROM vehicles 
        WHERE client_id = :client_id 
        ORDER BY vehicle_id DESC LIMIT 1
    ");
    $stmt->execute(['client_id' => $client_id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) {
        throw new Exception("Vehicle ID not found.");
    }

    $vehicle_id = $vehicle['vehicle_id'];

    // ✅ Insert OR document if uploaded
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
    }

    // ✅ Insert CR document if uploaded
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
    }

    // ✅ Insert insurance registration
    $stmt = $conn->prepare("
        INSERT INTO insurance_registration (client_id, vehicle_id, type_of_insurance)
        VALUES (:client_id, :vehicle_id, :insurance_type)
    ");
    $stmt->execute([
        'client_id' => $client_id,
        'vehicle_id' => $vehicle_id,
        'insurance_type' => $insurance_type
    ]);

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Insurance application submitted successfully!"]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["success" => false, "message" => "Transaction failed: " . $e->getMessage()]);
}

?>
