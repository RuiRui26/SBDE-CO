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
$user_name = $_SESSION['user_name'] ?? '';
$mobile = $_SESSION['contact_number'] ?? '';

$plate_number = $_POST['plate_number'] ?? null;
$mv_file_number = $_POST['mv_file_number'] ?? null;
$insurance_type = $_POST['insurance_type'] ?? null;
$or_picture = $_FILES['or_picture'] ?? null;
$cr_picture = $_FILES['cr_picture'] ?? null;

// Validation: At least one identifier must be provided
if (!$plate_number && !$mv_file_number) {
    echo json_encode(["success" => false, "message" => "Either MV File Number or Plate Number is required."]);
    exit;
}

// Validation: MV File Number must be 15 digits
if ($mv_file_number && !preg_match("/^\d{15}$/", $mv_file_number)) {
    echo json_encode(["success" => false, "message" => "MV File Number must be exactly 15 digits."]);
    exit;
}

// Sanitize file names using Full Name
$clean_name = preg_replace("/[^a-zA-Z0-9]/", "_", strtolower($user_name));
$or_filename = $clean_name . "_OR." . pathinfo($or_picture['name'], PATHINFO_EXTENSION);
$cr_filename = $clean_name . "_CR." . pathinfo($cr_picture['name'], PATHINFO_EXTENSION);
$uploadDir = '../../secured_uploads/';

// Ensure the upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Validate and move uploaded files
if (!move_uploaded_file($or_picture['tmp_name'], $uploadDir . $or_filename) ||
    !move_uploaded_file($cr_picture['tmp_name'], $uploadDir . $cr_filename)) {
    echo json_encode(["success" => false, "message" => "Failed to upload OR/CR pictures."]);
    exit;
}

try {
    $conn->beginTransaction();

    // Insert client data (Ensuring the client exists)
    $stmt = $conn->prepare("INSERT INTO clients (user_id, full_name, contact_number)
                        VALUES (:user_id, :name, :mobile)
                        ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), contact_number = VALUES(contact_number)");

    $stmt->execute([
        'user_id' => $user_id,
        'name' => $user_name,
        'mobile' => $mobile
    ]);

    // Retrieve the client_id
    $stmt = $conn->prepare("SELECT client_id FROM clients WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$client) {
        throw new Exception("Client ID not found.");
    }
    $client_id = $client['client_id'];

    // Insert into vehicles (Ensuring the vehicle exists)
    $stmt = $conn->prepare("
        INSERT INTO vehicles (client_id, plate_number, mv_file_number)
        VALUES (:client_id, :plate_number, :mv_file_number)
        ON DUPLICATE KEY UPDATE client_id = VALUES(client_id), mv_file_number = VALUES(mv_file_number)
    ");
    $stmt->execute([
        'client_id' => $client_id,
        'plate_number' => !empty($plate_number) ? $plate_number : NULL,
        'mv_file_number' => !empty($mv_file_number) ? $mv_file_number : NULL
    ]);

    // Retrieve the vehicle_id (Always fetch the existing or new vehicle)
    $stmt = $conn->prepare("
        SELECT vehicle_id FROM vehicles 
        WHERE plate_number = :plate_number OR mv_file_number = :mv_file_number
    ");
    $stmt->execute([
        'plate_number' => $plate_number,
        'mv_file_number' => $mv_file_number
    ]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$vehicle) {
        throw new Exception("Vehicle ID not found.");
    }
    $vehicle_id = $vehicle['vehicle_id'];

    // Insert into documents table for OR
    $stmt = $conn->prepare("
        INSERT INTO documents (client_id, vehicle_id, document_type, file_path)
        VALUES (:client_id, :vehicle_id, 'OR', :file_path)
    ");
    $stmt->execute([
        'client_id' => $client_id,
        'vehicle_id' => $vehicle_id,
        'file_path' => $or_filename
    ]);

    // Insert into documents table for CR
    $stmt = $conn->prepare("
        INSERT INTO documents (client_id, vehicle_id, document_type, file_path)
        VALUES (:client_id, :vehicle_id, 'CR', :file_path)
    ");
    $stmt->execute([
        'client_id' => $client_id,
        'vehicle_id' => $vehicle_id,
        'file_path' => $cr_filename
    ]);

    // Insert into insurance_registration (No OR/CR fields here)
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