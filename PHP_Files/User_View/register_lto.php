<?php
require_once "../../DB_connection/db.php";

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {

        // Log request data
        error_log("Form submitted: " . json_encode($_POST));

        // Sanitize input
        $name = htmlspecialchars(trim($_POST["name"]));
        $address = htmlspecialchars(trim($_POST["address"]));
        $mobile = htmlspecialchars(trim($_POST["mobile"]));
        $plate_number = htmlspecialchars(trim($_POST["plate_number"]));
        $chassis_number = htmlspecialchars(trim($_POST["chassis_number"]));

        // File upload directory
        $uploadDir = __DIR__ . "/../../secured_uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        function uploadFile($file, $prefix, $uploadDir) {
            $allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
            $allowedExtensions = ["jpg", "jpeg", "png"];
            $maxFileSize = 2 * 1024 * 1024; // 2MB limit

            if (!isset($file) || $file["error"] !== UPLOAD_ERR_OK) {
                throw new Exception("File upload error: " . ($file["error"] ?? "Unknown error"));
            }

            $fileMime = mime_content_type($file["tmp_name"]);
            if (!in_array($fileMime, $allowedTypes)) {
                throw new Exception("Invalid file type detected.");
            }

            $fileExt = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedExtensions)) {
                throw new Exception("Invalid file extension.");
            }

            if ($file["size"] > $maxFileSize) {
                throw new Exception("File size exceeds 2MB limit.");
            }

            $safeFileName = time() . "_$prefix" . "." . $fileExt;
            $filePath = $uploadDir . $safeFileName;

            if (!move_uploaded_file($file["tmp_name"], $filePath)) {
                throw new Exception("Failed to upload file: " . $file["name"]);
            }

            return $safeFileName; 
        }

        // Begin transaction
        $conn->beginTransaction();

        // Step 1: Check if client exists
        $stmt = $conn->prepare("SELECT Client_ID FROM clients WHERE full_name = :name AND contact_number = :mobile");
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":mobile", $mobile);
        $stmt->execute();
        $client_id = $stmt->fetchColumn();

        if (!$client_id) {
            $stmt = $conn->prepare("INSERT INTO clients (full_name, address, contact_number) VALUES (:name, :address, :mobile)");
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":address", $address);
            $stmt->bindParam(":mobile", $mobile);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert client.");
            }
            $client_id = $conn->lastInsertId();
        }

        // Step 2: Check if vehicle exists
        $stmt = $conn->prepare("SELECT Vehicle_ID FROM vehicles WHERE plate_number = :plate_number");
        $stmt->bindParam(":plate_number", $plate_number);
        $stmt->execute();
        $vehicle_id = $stmt->fetchColumn();

        if (!$vehicle_id) {
            $stmt = $conn->prepare("INSERT INTO vehicles (Client_ID, plate_number, chassis_number) VALUES (:client_id, :plate_number, :chassis_number)");
            $stmt->bindParam(":client_id", $client_id, PDO::PARAM_INT);
            $stmt->bindParam(":plate_number", $plate_number);
            $stmt->bindParam(":chassis_number", $chassis_number);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert vehicle.");
            }
            $vehicle_id = $conn->lastInsertId();
        }

        // Step 3: Upload images
        $documents = [
            ["OR", uploadFile($_FILES["or_picture"], "OR", $uploadDir)],
            ["CR", uploadFile($_FILES["cr_picture"], "CR", $uploadDir)],
            ["Emission", uploadFile($_FILES["emission_picture"], "Emission", $uploadDir)],
            ["Certificate of Coverage", uploadFile($_FILES["coc_picture"], "COC", $uploadDir)]
        ];

        // Step 4: Insert into `document` table (one row per document type)
        $document_ids = [];
        foreach ($documents as $doc) {
            $stmt = $conn->prepare("INSERT INTO document (Client_ID, Vehicle_ID, Document_Type, File_Path) 
                                    VALUES (:client_id, :vehicle_id, :document_type, :file_path)");
            $stmt->bindParam(":client_id", $client_id, PDO::PARAM_INT);
            $stmt->bindParam(":vehicle_id", $vehicle_id, PDO::PARAM_INT);
            $stmt->bindParam(":document_type", $doc[0]);
            $stmt->bindParam(":file_path", $doc[1]);
            if ($stmt->execute()) {
                $document_ids[$doc[0]] = $conn->lastInsertId();
            } else {
                throw new Exception("Failed to insert document: " . $doc[0]);
            }
        }

        // Step 5: Insert into `lto_transaction`
        $stmt = $conn->prepare("INSERT INTO lto_transaction (Client_ID, Vehicle_ID, document_id, OR_Picture, CR_Picture, Emission_Picture, Certificate_of_Coverage_Picture, Status) 
                                VALUES (:client_id, :vehicle_id, :document_id, :or_picture, :cr_picture, :emission_picture, :coc_picture, 'Pending')");
        $stmt->bindParam(":client_id", $client_id, PDO::PARAM_INT);
        $stmt->bindParam(":vehicle_id", $vehicle_id, PDO::PARAM_INT);
        $stmt->bindParam(":document_id", $document_ids['OR'], PDO::PARAM_INT);
        $stmt->bindParam(":or_picture", $documents[0][1]);  // OR
        $stmt->bindParam(":cr_picture", $documents[1][1]);  // CR
        $stmt->bindParam(":emission_picture", $documents[2][1]);  // Emission
        $stmt->bindParam(":coc_picture", $documents[3][1]);  // COC

        if ($stmt->execute()) {
            $conn->commit(); // Commit transaction
            echo json_encode(["success" => true, "message" => "Insurance registration successful!"]);
        } else {
            throw new Exception("Database insertion failed.");
        }

    } catch (Exception $e) {
        if ($conn->inTransaction()) {  
            $conn->rollBack(); // Rollback transaction on failure
        }

        error_log("Error: " . $e->getMessage()); // Log the error
        
        // Return error response with details
        echo json_encode([
            "success" => false, 
            "message" => "Error: " . $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine()
        ]);
    }
}
?>
