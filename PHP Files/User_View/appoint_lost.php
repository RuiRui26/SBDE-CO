<?php
require_once "../../DB_connection/db.php";

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Sanitize input to prevent SQL injection & XSS
        $name = htmlspecialchars(trim($_POST["name"]));

        // Step 1: Check if the client exists
        $stmt = $conn->prepare("SELECT Client_ID FROM clients WHERE full_name = :name");
        $stmt->bindParam(":name", $name);
        $stmt->execute();
        $client_id = $stmt->fetchColumn(); // Fetch the single column result

        // If client does not exist, return an error
        if (!$client_id) {
            echo json_encode(["success" => false, "message" => "Error: Client not found. Please register first."]);
            exit;
        }

        // Step 2: Insert lost document request into `lost_documents`
        $stmt = $conn->prepare("INSERT INTO lost_documents (Client_ID, document_type, Status) 
                                VALUES (:client_id, 'COC', 'Pending')");
        $stmt->bindParam(":client_id", $client_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Lost COC document request submitted successfully!"]);
        } else {
            throw new Exception("Database insertion failed.");
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage()); // Log the error for debugging
        echo json_encode(["success" => false, "message" => "An error occurred. Please try again."]);
    }
}
?>
