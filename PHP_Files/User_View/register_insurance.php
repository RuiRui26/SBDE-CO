<?php
session_start();
require_once '../../DB_connection/db.php';

$allowed_roles = ['Client'];
require '../../Logout_Login_USER/Restricted.php';

$database = new Database();
$conn = $database->getConnection();
header("Content-Type: application/json");

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Verify role
$stmt = $conn->prepare("SELECT role FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'Client') {
    echo json_encode(["success" => false, "message" => "Only clients can apply for insurance."]);
    exit;
}

// Get client details
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

// Get form data
$plate_number = $_POST['plate_number'] ?? null;
$mv_file_number = $_POST['mv_file_number'] ?? null;
$insurance_type = $_POST['insurance_type'] ?? null;
$chassis_number = $_POST['chassis_number'] ?? null;
$vehicle_type = $_POST['vehicle_type'] ?? null;
$brand = $_POST['brand'] ?? null;
$model = $_POST['model'] ?? null;
$year = $_POST['year'] ?? null;
$color = $_POST['color'] ?? null;
$start_date = $_POST['start_date'] ?? null;
$is_proxy = $_POST['is_proxy'] ?? 'no';

// Format start date if provided
$start_date_formatted = null;
if (!empty($start_date)) {
    $dateParts = explode('-', $start_date);
    if (count($dateParts) === 3) {
        $day = $dateParts[0];
        $month = $dateParts[1];
        $year = $dateParts[2];
        if (checkdate($month, $day, $year)) {
            $start_date_formatted = "$year-$month-$day";
        } else {
            echo json_encode(["success" => false, "message" => "Invalid start date format."]);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid date format. Use DD-MM-YYYY."]);
        exit;
    }
}

// Require either plate number or mv file number
if (!$plate_number && !$mv_file_number) {
    echo json_encode(["success" => false, "message" => "Either MV File Number or Plate Number is required."]);
    exit;
}

// Validate MV file number
if ($mv_file_number && !preg_match("/^\d{15}$/", $mv_file_number)) {
    echo json_encode(["success" => false, "message" => "MV File Number must be exactly 15 digits."]);
    exit;
}

// Check for duplicates
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
if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(["success" => false, "message" => "You already submitted an insurance application for this vehicle."]);
    exit;
}

// File upload directory
$uploadDir = '../../secured_uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// File upload handler
function processFileUpload($file, $prefix, $uploadDir, $client_name) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) throw new Exception("Invalid file type for $prefix.");

    $safe_name = preg_replace('/[^a-zA-Z0-9-_]/', '_', $client_name);
    $filename = "{$safe_name}_{$prefix}_" . time() . ".$ext";
    $subdir = $uploadDir . strtolower($prefix);
    if (!is_dir($subdir)) mkdir($subdir, 0777, true);
    $path = "$subdir/$filename";
    if (!move_uploaded_file($file['tmp_name'], $path)) throw new Exception("Failed to upload $prefix picture.");
    return $filename;
}

// Upload OR/CR
try {
    $or_filename = processFileUpload($_FILES['or_picture'], "OR", $uploadDir, $user_name);
    $cr_filename = processFileUpload($_FILES['cr_picture'], "CR", $uploadDir, $user_name);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}

$authorization_letter_filename = null;
$proxy_id = null;

try {
    $conn->beginTransaction();

    // Insert vehicle
    $stmt = $conn->prepare("
        INSERT INTO vehicles (client_id, plate_number, mv_file_number, chassis_number, vehicle_type, brand, model, year, color)
        VALUES (:client_id, :plate_number, :mv_file_number, :chassis_number, :vehicle_type, :brand, :model, :year, :color)
    ");
    $stmt->execute([
        'client_id' => $client_id,
        'plate_number' => $plate_number,
        'mv_file_number' => $mv_file_number,
        'chassis_number' => $chassis_number,
        'vehicle_type' => $vehicle_type,
        'brand' => $brand,
        'model' => $model,
        'year' => $year,
        'color' => $color
    ]);
    $vehicle_id = $conn->lastInsertId();

    // Documents
    if ($or_filename) {
        $stmt = $conn->prepare("INSERT INTO documents (client_id, vehicle_id, document_type, file_path) VALUES (:client_id, :vehicle_id, 'OR', :file_path)");
        $stmt->execute(['client_id' => $client_id, 'vehicle_id' => $vehicle_id, 'file_path' => $or_filename]);
        $or_document_id = $conn->lastInsertId();
    }

    if ($cr_filename) {
        $stmt = $conn->prepare("INSERT INTO documents (client_id, vehicle_id, document_type, file_path) VALUES (:client_id, :vehicle_id, 'CR', :file_path)");
        $stmt->execute(['client_id' => $client_id, 'vehicle_id' => $vehicle_id, 'file_path' => $cr_filename]);
        $cr_document_id = $conn->lastInsertId();
    }

    // Proxy
    if ($is_proxy === 'yes') {
        $proxy_first_name = $_POST['proxy_first_name'] ?? null;
        $proxy_middle_name = $_POST['proxy_middle_name'] ?? null;
        $proxy_last_name = $_POST['proxy_last_name'] ?? null;
        $proxy_birthday = $_POST['proxy_birthday'] ?? null;
        $proxy_relationship = $_POST['proxy_relationship'] === 'Other' ? ($_POST['other_relationship'] ?? null) : $_POST['proxy_relationship'];
        $proxy_contact = $_POST['proxy_contact'] ?? null;

        if (empty($proxy_first_name) || empty($proxy_last_name) || empty($proxy_birthday) || empty($proxy_relationship) || empty($proxy_contact)) {
            throw new Exception("Missing proxy information.");
        }

        if (!isset($_FILES['authorization_letter']) || $_FILES['authorization_letter']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Authorization letter required.");
        }

        $authorization_letter_filename = processFileUpload($_FILES['authorization_letter'], "AUTH", $uploadDir, $user_name);

        $stmt = $conn->prepare("
            INSERT INTO proxies (user_id, client_id, first_name, middle_name, last_name, birthday, relationship, contact_number, authorization_letter_path)
            VALUES (:user_id, :client_id, :first_name, :middle_name, :last_name, :birthday, :relationship, :contact, :letter_path)
        ");
        $stmt->execute([
            'user_id' => $user_id,
            'client_id' => $client_id,
            'first_name' => $proxy_first_name,
            'middle_name' => $proxy_middle_name,
            'last_name' => $proxy_last_name,
            'birthday' => $proxy_birthday,
            'relationship' => $proxy_relationship,
            'contact' => $proxy_contact,
            'letter_path' => $authorization_letter_filename
        ]);

        $proxy_id = $conn->lastInsertId();
    }

    // Insurance registration
    $stmt = $conn->prepare("
        INSERT INTO insurance_registration 
        (client_id, vehicle_id, proxy_id, type_of_insurance, or_picture, cr_picture, start_date)
        VALUES (:client_id, :vehicle_id, :proxy_id, :insurance_type, :or_picture, :cr_picture, :start_date)
    ");
    $stmt->execute([
        'client_id' => $client_id,
        'vehicle_id' => $vehicle_id,
        'proxy_id' => $proxy_id,
        'insurance_type' => $insurance_type,
        'or_picture' => $or_filename,
        'cr_picture' => $cr_filename,
        'start_date' => $start_date_formatted
    ]);

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Insurance registration successful."]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>