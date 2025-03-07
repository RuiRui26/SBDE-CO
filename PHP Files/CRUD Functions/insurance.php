<?php
require_once "../../DB_connection/db.php";

$database = new Database();
$conn = $database->getConnection();

try {
    $stmt = $conn->prepare("
        SELECT ir.Insurance_ID, c.full_name, v.plate_number, ir.Insurance_Type, ir.Premium_Amount, 
               ir.Start_Date, ir.End_Date, ir.Status 
        FROM insurance_registration ir
        JOIN clients c ON ir.Client_ID = c.Client_ID
        JOIN vehicles v ON ir.Vehicle_ID = v.Vehicle_ID
        ORDER BY ir.Start_Date DESC;
    ");
    $stmt->execute();
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $result]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error fetching insurance applications"]);
}
?>
