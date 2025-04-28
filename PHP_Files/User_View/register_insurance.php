<?php
session_start();
require_once '../../DB_connection/db.php';

header("Content-Type: application/json");

// === CSRF Token Validation ===
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    echo json_encode(["success" => false, "message" => "Invalid CSRF token."]);
    exit;
}

// Sanitize input function
function sanitize_input($data) {
    return htmlspecialchars(trim(strip_tags($data)), ENT_QUOTES, 'UTF-8');
}

// Restrict to allowed role
$allowed_roles = ['Client'];
require '../../Logout_Login_USER/Restricted.php';

$database = new Database();
$conn = $database->getConnection();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Verify user role from DB
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

// Check if this is a proxy registration
$is_proxy = isset($_POST['is_proxy']) && $_POST['is_proxy'] === 'yes';
$proxy_id = null;

// Handle proxy registration if needed
if ($is_proxy) {
    // Validate proxy fields
    $required_proxy_fields = [
        'proxy_first_name' => 'First name',
        'proxy_last_name' => 'Last name',
        'proxy_birthday' => 'Birthday',
        'proxy_relationship' => 'Relationship',
        'proxy_contact' => 'Contact number'
    ];
    
    foreach ($required_proxy_fields as $field => $name) {
        if (empty($_POST[$field])) {
            echo json_encode(["success" => false, "message" => "Proxy $name is required."]);
            exit;
        }
    }
    
    // Validate proxy age (must be at least 18)
    $birthday = new DateTime($_POST['proxy_birthday']);
    $today = new DateTime();
    $age = $birthday->diff($today)->y;
    
    if ($age < 18) {
        echo json_encode(["success" => false, "message" => "Proxy must be at least 18 years old."]);
        exit;
    }
    
    // Handle relationship field
    $relationship = sanitize_input($_POST['proxy_relationship']);
    if ($relationship === 'Other') {
        if (empty($_POST['other_relationship'])) {
            echo json_encode(["success" => false, "message" => "Please specify relationship."]);
            exit;
        }
        $relationship = sanitize_input($_POST['other_relationship']);
    }
    
    // Process authorization letter upload
    if (!isset($_FILES['authorization_letter']) || $_FILES['authorization_letter']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "message" => "Authorization letter is required for proxy registration."]);
        exit;
    }
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $fileExtension = strtolower(pathinfo($_FILES['authorization_letter']['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode(["success" => false, "message" => "Invalid file type for authorization letter. Only JPG, JPEG, PNG, and PDF allowed."]);
        exit;
    }
    
    $uploadDir = '../../secured_uploads/proxy_auth/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $safe_name = preg_replace('/[^a-zA-Z0-9-_]/', '_', $user_name);
    $auth_filename = "proxy_auth_{$safe_name}_" . time() . "." . $fileExtension;
    $destination = $uploadDir . $auth_filename;
    
    if (!move_uploaded_file($_FILES['authorization_letter']['tmp_name'], $destination)) {
        echo json_encode(["success" => false, "message" => "Failed to upload authorization letter."]);
        exit;
    }
}

// Retrieve and sanitize form data
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

foreach (['plate_number', 'mv_file_number', 'insurance_type', 'chassis_number', 'vehicle_type', 'brand', 'model', 'year', 'color', 'start_date'] as $field) {
    if (isset($$field)) {
        $$field = sanitize_input($$field);
    }
}

// Validate start date (expecting YYYY-MM-DD)
if ($start_date) {
    $dateParts = explode('-', $start_date);
    if (count($dateParts) === 3) {
        [$year_part, $month, $day] = $dateParts;
        if (!checkdate((int)$month, (int)$day, (int)$year_part)) {
            echo json_encode(["success" => false, "message" => "Invalid start date."]);
            exit;
        }
        
        $start_date_obj = new DateTime($start_date);
        $today = new DateTime();
        
        if ($start_date_obj < $today) {
            echo json_encode(["success" => false, "message" => "Start date must not be in the past."]);
            exit;
        }
        
        // Calculate expiration date (365 days from start date)
        $expired_at = clone $start_date_obj;
        $expired_at->add(new DateInterval('P365D'));
    } else {
        echo json_encode(["success" => false, "message" => "Invalid date format. Use YYYY-MM-DD."]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Start date is required."]);
    exit;
}

// Validate plate_number or mv_file_number presence
if (!$plate_number && !$mv_file_number) {
    echo json_encode(["success" => false, "message" => "Either Plate Number or MV File Number is required."]);
    exit;
}

// Validate mv_file_number length (15 digits)
if ($mv_file_number && !preg_match("/^\d{15}$/", $mv_file_number)) {
    echo json_encode(["success" => false, "message" => "MV File Number must be exactly 15 digits."]);
    exit;
}

// Check for existing insurance application for the vehicle
$stmt = $conn->prepare("
    SELECT 1 FROM insurance_registration ir
    JOIN vehicles v ON ir.vehicle_id = v.vehicle_id
    WHERE ir.client_id = :client_id
    AND (v.plate_number = :plate_number OR v.chassis_number = :chassis_number)
");
$stmt->execute([
    'client_id' => $client_id,
    'plate_number' => $plate_number ?: '',
    'chassis_number' => $chassis_number ?: ''
]);
$existingApplication = $stmt->fetch(PDO::FETCH_ASSOC);
if ($existingApplication) {
    echo json_encode(["success" => false, "message" => "You already submitted an application for this vehicle."]);
    exit;
}

// Upload directory for OR/CR
$uploadDir = '../../secured_uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function processFileUpload($file, $prefix, $uploadDir, $client_name) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Invalid file type for $prefix. Only JPG, JPEG, PNG, and PDF allowed.");
    }

    $safe_name = preg_replace('/[^a-zA-Z0-9-_]/', '_', $client_name);
    $filename = "{$safe_name}_{$prefix}_" . time() . "." . $fileExtension;

    $subdir = ($prefix === 'OR') ? 'or' : 'cr';
    $destinationDir = $uploadDir . $subdir . '/';
    if (!is_dir($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }
    $destination = $destinationDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to upload $prefix document.");
    }
    return $filename;
}

try {
    $or_filename = processFileUpload($_FILES['or_picture'] ?? null, "OR", $uploadDir, $user_name);
    $cr_filename = processFileUpload($_FILES['cr_picture'] ?? null, "CR", $uploadDir, $user_name);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}

// Insert data transactionally
try {
    $conn->beginTransaction();

    // Register proxy first if needed
    if ($is_proxy) {
        $stmt = $conn->prepare("
            INSERT INTO proxies (
                user_id, 
                client_id, 
                first_name, 
                middle_name, 
                last_name, 
                birthday, 
                relationship, 
                other_relationship, 
                contact_number, 
                authorization_letter_path
            ) VALUES (
                :user_id, 
                :client_id, 
                :first_name, 
                :middle_name, 
                :last_name, 
                :birthday, 
                :relationship, 
                :other_relationship, 
                :contact_number, 
                :authorization_letter_path
            )
        ");
        
        $stmt->execute([
            'user_id' => $user_id,
            'client_id' => $client_id,
            'first_name' => sanitize_input($_POST['proxy_first_name']),
            'middle_name' => isset($_POST['proxy_middle_name']) ? sanitize_input($_POST['proxy_middle_name']) : null,
            'last_name' => sanitize_input($_POST['proxy_last_name']),
            'birthday' => $_POST['proxy_birthday'],
            'relationship' => $relationship,
            'other_relationship' => isset($_POST['other_relationship']) ? sanitize_input($_POST['other_relationship']) : null,
            'contact_number' => sanitize_input($_POST['proxy_contact']),
            'authorization_letter_path' => $auth_filename
        ]);
        
        $proxy_id = $conn->lastInsertId();
    }

    // Insert or update vehicle (Make sure unique key exists on vehicles table)
    $stmt = $conn->prepare("
        INSERT INTO vehicles (client_id, plate_number, mv_file_number, chassis_number, vehicle_type, brand, model, year, color)
        VALUES (:client_id, :plate_number, :mv_file_number, :chassis_number, :vehicle_type, :brand, :model, :year, :color)
        ON DUPLICATE KEY UPDATE 
            vehicle_type = VALUES(vehicle_type),
            brand = VALUES(brand),
            model = VALUES(model),
            year = VALUES(year),
            color = VALUES(color)
    ");
    $stmt->execute([
        'client_id' => $client_id,
        'plate_number' => $plate_number ?: null,
        'mv_file_number' => $mv_file_number ?: null,
        'chassis_number' => $chassis_number ?: null,
        'vehicle_type' => $vehicle_type,
        'brand' => $brand,
        'model' => $model,
        'year' => $year,
        'color' => $color
    ]);

    // Retrieve the vehicle id by unique identifiers
    $stmt = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE client_id = :client_id AND (plate_number = :plate_number OR mv_file_number = :mv_file_number OR chassis_number = :chassis_number) LIMIT 1");
    $stmt->execute([
        'client_id' => $client_id,
        'plate_number' => $plate_number ?: '',
        'mv_file_number' => $mv_file_number ?: '',
        'chassis_number' => $chassis_number ?: ''
    ]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) {
        throw new Exception("Vehicle ID not found after insert.");
    }
    $vehicle_id = $vehicle['vehicle_id'];

    // Insert documents if uploaded
    $or_document_id = null;
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
        $or_document_id = $conn->lastInsertId();
    }

    $cr_document_id = null;
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
        $cr_document_id = $conn->lastInsertId();
    }

    // Insert insurance registration with proxy_id and expiration date
    $stmt = $conn->prepare("
        INSERT INTO insurance_registration (
            client_id, 
            vehicle_id, 
            type_of_insurance, 
            or_picture, 
            cr_picture, 
            start_date, 
            expired_at,
            document_id,
            proxy_id
        ) VALUES (
            :client_id, 
            :vehicle_id, 
            :type_of_insurance, 
            :or_picture, 
            :cr_picture, 
            :start_date, 
            :expired_at,
            :document_id,
            :proxy_id
        )
    ");
    $stmt->execute([
        'client_id' => $client_id,
        'vehicle_id' => $vehicle_id,
        'type_of_insurance' => $insurance_type,
        'or_picture' => $or_filename,
        'cr_picture' => $cr_filename,
        'start_date' => $start_date,
        'expired_at' => $expired_at->format('Y-m-d'),
        'document_id' => $or_document_id ?? null,
        'proxy_id' => $proxy_id
    ]);

    $conn->commit();

    echo json_encode(["success" => true, "message" => "Insurance application submitted successfully."]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["success" => false, "message" => "Failed to submit insurance application: " . $e->getMessage()]);
}
?>