<?php
require_once "../../DB_connection/db.php"; // Ensure the path is correct

$database = new Database();
$conn = $database->getConnection();

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Sanitize input data
        $name = htmlspecialchars(trim($_POST["name"]));
        $address = htmlspecialchars(trim($_POST["address"]));
        $mobile = htmlspecialchars(trim($_POST["mobile"]));
        $plate_number = htmlspecialchars(trim($_POST["plate_number"]));
        $chassis_number = htmlspecialchars(trim($_POST["chassis_number"]));
        $type_of_insurance = htmlspecialchars(trim($_POST["insurance_type"]));
        $admin_id = 1; // Replace with the logged-in admin ID (dynamic later)

        // File upload handling
        $uploadDir = __DIR__ . "/../../secured_uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        function uploadFile($file, $prefix, $uploadDir) {
            $allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
            $maxFileSize = 2 * 1024 * 1024; // 2MB limit

            if ($file["error"] !== UPLOAD_ERR_OK) {
                throw new Exception("File upload error: " . $file["error"]);
            }

            if (!in_array($file["type"], $allowedTypes)) {
                throw new Exception("Invalid file type. Only JPG, JPEG, and PNG are allowed.");
            }

            if ($file["size"] > $maxFileSize) {
                throw new Exception("File size exceeds 2MB limit.");
            }

            $fileName = time() . "_$prefix" . "_" . basename($file["name"]);
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($file["tmp_name"], $filePath)) {
                throw new Exception("Failed to upload file: $fileName");
            }

            return $fileName; // Store only the file name in the database
        }

        // Upload OR & CR Pictures
        $or_picture = uploadFile($_FILES["or_picture"], "OR", $uploadDir);
        $cr_picture = uploadFile($_FILES["cr_picture"], "CR", $uploadDir);

        // Start a database transaction
        $conn->beginTransaction();

        // **Step 1: Find Client ID based on Full Name & Contact Number**
        $stmt = $conn->prepare("SELECT Client_ID FROM clients WHERE Full_Name = :name AND Contact_Number = :mobile");
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":mobile", $mobile);
        $stmt->execute();
        $client_id = $stmt->fetchColumn();

        // **If client does not exist, insert them**
        if (!$client_id) {
            $stmt = $conn->prepare("INSERT INTO clients (Full_Name, Address, Contact_Number, Created_At) 
                                    VALUES (:name, :address, :mobile, NOW())");
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":address", $address);
            $stmt->bindParam(":mobile", $mobile);
            $stmt->execute();
            $client_id = $conn->lastInsertId();
        }

        // **Step 2: Find or Insert Vehicle**
$stmt = $conn->prepare("SELECT Vehicle_ID FROM vehicles WHERE Plate_Number = :plate_number");
$stmt->bindParam(":plate_number", $plate_number);
$stmt->execute();
$vehicle_id = $stmt->fetchColumn();

if (!$vehicle_id) {
    $stmt = $conn->prepare("INSERT INTO vehicles (Client_ID, Plate_Number, Chassis_Number, Type_of_Insurance) 
                            VALUES (:client_id, :plate_number, :chassis_number, :insurance_type)");
    $stmt->bindParam(":client_id", $client_id, PDO::PARAM_INT);
    $stmt->bindParam(":plate_number", $plate_number);
    $stmt->bindParam(":chassis_number", $chassis_number);
    $stmt->bindParam(":insurance_type", $type_of_insurance);
    $stmt->execute();
    $vehicle_id = $conn->lastInsertId();
}

// **Step 3: Insert into insurance_registration**
$stmt = $conn->prepare("INSERT INTO insurance_registration 
    (Client_ID, Vehicle_ID, Type_of_Insurance, Status, OR_Picture, CR_Picture, Created_At) 
    VALUES (:client_id, :vehicle_id, :insurance_type, 'Pending', :or_picture, :cr_picture, NOW())");


$stmt->bindParam(":client_id", $client_id, PDO::PARAM_INT);
$stmt->bindParam(":vehicle_id", $vehicle_id, PDO::PARAM_INT);
$stmt->bindParam(":insurance_type", $type_of_insurance);
$stmt->bindParam(":or_picture", $or_picture);
$stmt->bindParam(":cr_picture", $cr_picture);


        if ($stmt->execute()) {
            $conn->commit(); // Commit transaction
            echo json_encode(["success" => true, "message" => "Insurance registration successful!"]);
        } else {
            throw new Exception("Database insertion failed.");
        }
    } catch (Exception $e) {
        $conn->rollBack(); // Rollback transaction on failure
        error_log("Error: " . $e->getMessage()); // Log the error
        echo json_encode(["success" => false, "message" => $e->getMessage()]);

    }
}
?>
