<?php
session_start();
require '../../DB_connection/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Invalid request method';
    header("Location: ../../NMG INSURANCE AGENCY/CASHIER VIEW/cashier.php");
    exit;
}

$database = new Database();
$conn = $database->getConnection();

// Get and validate inputs
$insurance_id = $_POST['insurance_id'] ?? null;
$action = $_POST['action'] ?? null;
$coc_number = $_POST['certificate_of_coverage'] ?? null;

// Validate insurance ID
if (!$insurance_id || !ctype_digit($insurance_id)) {
    $_SESSION['error_message'] = 'Invalid insurance ID';
    header("Location: ../../NMG INSURANCE AGENCY/CASHIER VIEW/cashier.php");
    exit;
}

// Validate action
$allowed_actions = ['mark_paid', 'mark_unpaid', 'mark_claimed', 'mark_unclaimed'];
if (!in_array($action, $allowed_actions)) {
    $_SESSION['error_message'] = 'Invalid action';
    header("Location: ../../NMG INSURANCE AGENCY/CASHIER VIEW/cashier_insurance_details.php?id=" . base64_encode($insurance_id));
    exit;
}

try {
    // Handle each action type
    if ($action === 'mark_paid') {
        $stmt = $conn->prepare("UPDATE insurance_registration SET is_paid = 'Paid' WHERE insurance_id = :id");
        $stmt->execute([':id' => $insurance_id]);
        $_SESSION['success_message'] = 'Insurance marked as Paid successfully';
    } 
    elseif ($action === 'mark_unpaid') {
        $stmt = $conn->prepare("UPDATE insurance_registration SET is_paid = 'Unpaid' WHERE insurance_id = :id");
        $stmt->execute([':id' => $insurance_id]);
        $_SESSION['success_message'] = 'Insurance marked as Unpaid successfully';
    } 
    elseif ($action === 'mark_claimed') {
        // Require COC number for claiming
        if (empty($coc_number)) {
            $_SESSION['error_message'] = 'Certificate of Coverage number is required';
            header("Location: ../../NMG INSURANCE AGENCY/CASHIER VIEW/cashier_insurance_details.php?id=" . base64_encode($insurance_id));
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE insurance_registration 
                              SET is_claimed = 'Claimed', certificate_of_coverage = :coc 
                              WHERE insurance_id = :id");
        $stmt->execute([
            ':coc' => $coc_number,
            ':id' => $insurance_id
        ]);
        $_SESSION['success_message'] = 'Insurance marked as Claimed successfully with COC: ' . htmlspecialchars($coc_number);
    } 
    elseif ($action === 'mark_unclaimed') {
        $stmt = $conn->prepare("UPDATE insurance_registration 
                              SET is_claimed = 'Unclaimed', certificate_of_coverage = NULL 
                              WHERE insurance_id = :id");
        $stmt->execute([':id' => $insurance_id]);
        $_SESSION['success_message'] = 'Insurance marked as Unclaimed successfully';
    }

    // Redirect back to the details page
    header("Location: ../../NMG INSURANCE AGENCY/CASHIER VIEW/cashier_insurance_details.php?id=" . base64_encode($insurance_id));
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    header("Location: ../../NMG INSURANCE AGENCY/CASHIER VIEW/cashier_insurance_details.php?id=" . base64_encode($insurance_id));
    exit;
}