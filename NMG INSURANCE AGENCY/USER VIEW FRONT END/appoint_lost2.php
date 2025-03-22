<?php
session_start();
require '../../DB_connection/db.php'; // Adjust the path if needed

$database = new Database();
$conn = $database->getConnection(); // Get the PDO connection

if (!$conn) {
    die("Database connection is missing.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = trim($_POST['client_id']);
    $certificate_of_coverage = trim($_POST['certificate_of_coverage']); // ✅ Corrected key

    // Validate required fields
    if (empty($client_id) || empty($certificate_of_coverage)) {
        echo "<script>alert('All fields are required!'); window.history.back();</script>";
        exit();
    }

    try {
        // Insert only client_id and certificate_of_coverage
        $stmt = $conn->prepare("INSERT INTO lost_documents (client_id, certificate_of_coverage) 
                                VALUES (:client_id, :certificate_of_coverage)");

        // Bind parameters
        $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->bindParam(':certificate_of_coverage', $certificate_of_coverage, PDO::PARAM_STR);

        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>alert('Lost document request submitted successfully.'); window.location.href='success_page.php';</script>";
        } else {
            echo "<script>alert('Error submitting request.'); window.history.back();</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . $e->getMessage() . "'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.history.back();</script>";
}
?>
