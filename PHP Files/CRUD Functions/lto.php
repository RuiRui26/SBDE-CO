<?php
require_once "../../DB_connection/db.php";

$database = new Database();
$conn = $database->getConnection();

try {
    $stmt = $conn->prepare("
        SELECT lt.Transaction_ID, c.full_name, v.plate_number, lt.Status, lt.Created_At 
        FROM lto_transaction lt
        JOIN clients c ON lt.Client_ID = c.Client_ID
        JOIN vehicles v ON lt.Vehicle_ID = v.Vehicle_ID
        ORDER BY lt.Created_At DESC;
    ");
    $stmt->execute();
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $result]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error fetching LTO transactions"]);
}
?>
