<?php
require_once "../../DB_connection/db.php"; // Adjust path if necessary

header("Content-Type: application/json"); // Ensure correct JSON response

$database = new Database();
$conn = $database->getConnection();

try {
    $stmt = $conn->prepare("
        SELECT ir.insurance_id, 
               COALESCE(NULLIF(c.full_name, ''), 'Unknown') AS full_name, 
               v.plate_number, 
               ir.type_of_insurance, 
               ir.created_at, 
               ir.status 
        FROM insurance_registration ir
        JOIN clients c ON ir.client_id = c.client_id
        JOIN vehicles v ON ir.vehicle_id = v.vehicle_id
        ORDER BY ir.created_at DESC;
    ");
    $stmt->execute();

    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["success" => true, "data" => $transactions]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error fetching transactions", "error" => $e->getMessage()]);
}
?>
