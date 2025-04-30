<?php
require_once "../../DB_connection/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

if (!isset($_POST['insurance_id'], $_POST['new_status'])) {
    http_response_code(400);
    echo "Missing parameters";
    exit;
}

$insurance_id = (int)$_POST['insurance_id'];
$new_status = $_POST['new_status'];

$valid_statuses = ['Pending', 'Approved', 'Rejected'];
if (!in_array($new_status, $valid_statuses, true)) {
    http_response_code(400);
    echo "Invalid status value";
    exit;
}

// If Approved, schedule_date is required
$schedule_date = null;
if ($new_status === 'Approved') {
    if (empty($_POST['schedule_date'])) {
        http_response_code(400);
        echo "Appointment date is required when approving";
        exit;
    }
    $schedule_date = $_POST['schedule_date'];
    $date_check = DateTime::createFromFormat('Y-m-d', $schedule_date);
    if (!$date_check || $date_check->format('Y-m-d') !== $schedule_date) {
        http_response_code(400);
        echo "Invalid appointment date format";
        exit;
    }

    // Check appointment date is at least 3 days from today
$today = new DateTime('today');
$minDate = (clone $today)->modify('+3 days');
if ($date_check < $minDate) {
    http_response_code(400);
    echo "Appointment date must be at least 3 days from today";
    exit;
}

}

try {
    $database = new Database();
    $conn = $database->getConnection();

    $conn->beginTransaction();

    $update_sql = "UPDATE nmg_insurance.insurance_registration 
                   SET status = :status, scheduled_date = :scheduled_date 
                   WHERE insurance_id = :insurance_id";

    $stmt = $conn->prepare($update_sql);
    $stmt->bindParam(':status', $new_status);
    if ($new_status === 'Approved') {
        $stmt->bindParam(':scheduled_date', $schedule_date);
    } else {
        $stmt->bindValue(':scheduled_date', null, PDO::PARAM_NULL);
    }
    $stmt->bindParam(':insurance_id', $insurance_id, PDO::PARAM_INT);
    $stmt->execute();

    $conn->commit();

    echo "Status updated successfully";

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
}
