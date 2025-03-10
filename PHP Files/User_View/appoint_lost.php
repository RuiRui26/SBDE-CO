<?php
require_once "../../DB_connection/db.php";

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Start output buffering to prevent header issues
        ob_start();

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

        // Step 2: Generate an appointment date (e.g., 3 days from today)
        $appointment_date = date('Y-m-d', strtotime('+3 days'));

        // Step 3: Insert lost document request into `lost_documents`
        $stmt = $conn->prepare("INSERT INTO lost_documents (Client_ID, document_type, Status, appointment_date) 
                                VALUES (:client_id, 'COC', 'Pending', :appointment_date)");
        $stmt->bindParam(":client_id", $client_id, PDO::PARAM_INT);
        $stmt->bindParam(":appointment_date", $appointment_date);

        if ($stmt->execute()) {
            // Ensure the buffer is clean before redirecting
            ob_clean();
            
            // Redirect to success page
            header("Location: success_page.php");
            exit;
        } else {
            throw new Exception("Database insertion failed.");
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage()); // Log the error for debugging
        echo json_encode(["success" => false, "message" => "An error occurred. Please try again."]);
    }
}
?>
