<?php
require_once "../../DB_connection/db.php";
session_start(); // Start session

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        ob_start(); // Start output buffering

        $name = htmlspecialchars(trim($_POST["name"]));

        // Check if the client exists
        $stmt = $conn->prepare("SELECT Client_ID FROM clients WHERE full_name = :name");
        $stmt->bindParam(":name", $name);
        $stmt->execute();
        $client_id = $stmt->fetchColumn();

        if (!$client_id) {
            echo json_encode(["success" => false, "message" => "Error: Client not found. Please register first."]);
            exit;
        }

        // Generate appointment date (3 days from today)
        $appointment_date = date('Y-m-d', strtotime('+3 days'));

        // Insert into lost_documents
        $stmt = $conn->prepare("INSERT INTO lost_documents (Client_ID, document_type, Status, appointment_date) 
                                VALUES (:client_id, 'COC', 'Pending', :appointment_date)");
        $stmt->bindParam(":client_id", $client_id, PDO::PARAM_INT);
        $stmt->bindParam(":appointment_date", $appointment_date);

        if ($stmt->execute()) {
            // Store appointment date in session to show on success page
            $_SESSION['appointment_date'] = $appointment_date;
        
            ob_clean(); // Clean output buffer before redirecting
            header("Location: ../lost_docs/success_page.php"); // Corrected path
            exit;
        } else {
            throw new Exception("Database insertion failed.");
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "An error occurred. Please try again."]);
    }
}
?>
