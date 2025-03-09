<?php
require_once "../../DB_connection/db.php";

$database = new Database();
$conn = $database->getConnection();

try {
    $stmt = $conn->prepare("
        SELECT ld.Lost_ID, c.full_name, v.plate_number, ld.COC_Picture, ld.Reported_At, ld.Status 
        FROM lost_documents ld
        JOIN clients c ON ld.Client_ID = c.Client_ID
        JOIN vehicles v ON ld.Vehicle_ID = v.Vehicle_ID
        ORDER BY ld.Reported_At DESC;
    ");
    $stmt->execute();
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $result]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error fetching lost documents"]);
}
?>
