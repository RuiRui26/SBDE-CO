<?php
session_start();
require_once '../../DB_connection/db.php';

// Verify user role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Cashier') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Validate input
if (!isset($_POST['id'], $_POST['status'], $_POST['action']) || $_POST['action'] !== 'update_status') {
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

$documentId = base64_decode($_POST['id']);
$newStatus = $_POST['status'];

// Validate status
$allowedStatuses = ['Processing', 'Approved', 'Claimed'];
if (!in_array($newStatus, $allowedStatuses)) {
    die(json_encode(['success' => false, 'message' => 'Invalid status value']));
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("UPDATE lost_documents SET status = ? WHERE lost_document_id = ?");
    $stmt->execute([$newStatus, $documentId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>