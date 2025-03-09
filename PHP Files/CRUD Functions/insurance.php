<?php
require_once "../../DB_connection/db.php";

$database = new Database();
$conn = $database->getConnection();

session_start();
$user_id = $_SESSION['user_id']; // Get logged-in user ID

try {
    $stmt = $conn->prepare("SELECT ir.Insurance_ID, v.plate_number, ir.mv_file_number, ir.Insurance_Type, ir.Premium_Amount, ir.Start_Date, ir.End_Date, ir.Status 
                            FROM insurance_registration ir
                            JOIN vehicles v ON ir.Vehicle_ID = v.Vehicle_ID
                            WHERE ir.User_ID = ?
                            ORDER BY ir.Start_Date DESC;");
    $stmt->execute([$user_id]);
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $result]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error fetching insurance applications"]);
}
?>
