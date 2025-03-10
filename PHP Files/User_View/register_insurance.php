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


// Validate and move uploaded files
if (!move_uploaded_file($or_picture['tmp_name'], $uploadDir . $or_filename) ||
    !move_uploaded_file($cr_picture['tmp_name'], $uploadDir . $cr_filename)) {
    echo json_encode(["success" => false, "message" => "Failed to upload OR/CR pictures."]);
    exit;
}

try {
    $conn->beginTransaction();

    // Insert client data
    $stmt = $conn->prepare("INSERT INTO clients (User_ID, Full_Name, Contact_Number)
                            VALUES (:user_id, :name, :mobile)
                            ON DUPLICATE KEY UPDATE Full_Name = VALUES(Full_Name), Contact_Number = VALUES(Contact_Number)");
    $stmt->execute([
        'user_id' => $user_id,
        'name' => $user_name,
        'mobile' => $mobile
    ]);

    // Insert into vehicles
    $stmt = $conn->prepare("INSERT INTO vehicles (Plate_Number, MV_File_Number, User_ID)
                            VALUES (:plate_number, :mv_file_number, :user_id)");
    $stmt->execute([
        'plate_number' => !empty($plate_number) ? $plate_number : NULL,
        'mv_file_number' => !empty($mv_file_number) ? $mv_file_number : NULL,
        'user_id' => $user_id
    ]);

    // Insert into insurance_registration
    $stmt = $conn->prepare("INSERT INTO insurance_registration (User_ID, Plate_Number, MV_File_Number, Insurance_Type, OR_Picture, CR_Picture)
                            VALUES (:user_id, :plate_number, :mv_file_number, :insurance_type, :or_picture, :cr_picture)");
    $stmt->execute([
        'user_id' => $user_id,
        'plate_number' => !empty($plate_number) ? $plate_number : NULL,
        'mv_file_number' => !empty($mv_file_number) ? $mv_file_number : NULL,
        'insurance_type' => $insurance_type,
        'or_picture' => $or_filename,
        'cr_picture' => $cr_filename
    ]);

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Insurance application submitted successfully!"]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["success" => false, "message" => "Transaction failed: " . $e->getMessage()]);
}
?>
