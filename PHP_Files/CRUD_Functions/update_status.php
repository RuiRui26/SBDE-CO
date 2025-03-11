<?php
require_once "../../PHP_Files/CRUD_Functions/insurance_queries.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if necessary POST parameters are present
    if (!isset($_POST['id']) || !isset($_POST['status'])) {
        echo "invalid"; // Missing parameters
        exit;
    }

    $insurance_id = $_POST['id'];
    $new_status = $_POST['status'];

    // Debugging: Log received values to a debug file
    file_put_contents("debug_log.txt", "Received ID: $insurance_id, Status: $new_status\n", FILE_APPEND);

    // Ensure status is valid
    $valid_statuses = ['Pending', 'Approved', 'Rejected'];  // Corrected status values
    if (!in_array($new_status, $valid_statuses)) {
        echo "invalid";
        exit;
    }

    // Database connection
    $conn = new mysqli("localhost", "root", "", "nmg_insurance");
    if ($conn->connect_error) {
        echo "db_error";
        exit;
    }

    // First, get the current status of the transaction
    $stmt = $conn->prepare("SELECT status, created_at FROM insurance_registration WHERE insurance_id = ?");
    $stmt->bind_param("i", $insurance_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_status_data = $result->fetch_assoc();
    
    // Debugging: Log current status and applied date
    file_put_contents("debug_log.txt", "Current Status for ID: $insurance_id is " . $current_status_data['status'] . ", Applied Date: " . $current_status_data['created_at'] . "\n", FILE_APPEND);

    // Check if the current status is different from the new status
    if ($current_status_data['status'] === $new_status) {
        file_put_contents("debug_log.txt", "No status change needed. Current status is already $new_status.\n", FILE_APPEND);
        echo "no_change";
        exit;
    }

    // Prepare and execute the SQL statement to update status (without modifying created_at)
    $stmt = $conn->prepare("UPDATE insurance_registration SET status = ? WHERE insurance_id = ?");
    if ($stmt === false) {
        file_put_contents("debug_log.txt", "Prepare statement failed: " . $conn->error . "\n", FILE_APPEND);
        echo "db_error";
        exit;
    }

    $stmt->bind_param("si", $new_status, $insurance_id);
    $stmt->execute();

    // Debugging: Check if query was executed successfully
    if ($stmt->affected_rows > 0) {
        file_put_contents("debug_log.txt", "Update successful for ID: $insurance_id\n", FILE_APPEND);
        echo "success";
    } else {
        // This could happen if the insurance_id doesn't exist or if status is the same as before
        file_put_contents("debug_log.txt", "Update failed for ID: $insurance_id. Affected rows: " . $stmt->affected_rows . "\n", FILE_APPEND);
        echo "failed";
    }

    $stmt->close();
    $conn->close();
}
?>
