<?php
session_start();
require '../../DB_connection/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid request method');
}

$database = new Database();
$conn = $database->getConnection();

$insurance_id = $_POST['insurance_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$insurance_id || !ctype_digit($insurance_id)) {
    exit('Invalid insurance ID');
}

if (!in_array($action, ['mark_paid', 'mark_unpaid', 'mark_claimed', 'mark_unclaimed'])) {
    exit('Invalid action');
}

try {
    if ($action === 'mark_paid') {
        $stmt = $conn->prepare("UPDATE insurance_registration SET is_paid = 'Paid' WHERE insurance_id = :id");
    } elseif ($action === 'mark_unpaid') {
        $stmt = $conn->prepare("UPDATE insurance_registration SET is_paid = 'Unpaid' WHERE insurance_id = :id");
    } elseif ($action === 'mark_claimed') {
        $stmt = $conn->prepare("UPDATE insurance_registration SET is_claimed = 'Claimed' WHERE insurance_id = :id");
    } elseif ($action === 'mark_unclaimed') {
        $stmt = $conn->prepare("UPDATE insurance_registration SET is_claimed = 'Unclaimed' WHERE insurance_id = :id");
    }
    $stmt->execute(['id' => $insurance_id]);

    $_SESSION['success_message'] = "Status updated successfully.";

    header("Location: ../../NMG INSURANCE AGENCY/CASHIER VIEW/cashier.php?id=" . base64_encode($insurance_id));
    exit;

} catch (PDOException $e) {
    exit("Database error: " . $e->getMessage());
}
