<?php
session_start();
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
require '../../DB_connection/db.php';

// Database Connection
$database = new Database();
$conn = $database->getConnection();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid request.'); window.history.back();</script>";
    exit();
}

$lost_document_id = $_GET['id'];

// Fetch Lost Document Details
$query = "SELECT ld.lost_document_id, u.name AS client_name, ld.certificate_of_coverage, 
                 ld.application_date, ld.status 
          FROM lost_documents ld
          JOIN clients c ON ld.client_id = c.client_id
          JOIN users u ON c.user_id = u.user_id
          WHERE ld.lost_document_id = :lost_document_id";

$stmt = $conn->prepare($query);
$stmt->bindParam(':lost_document_id', $lost_document_id, PDO::PARAM_INT);
$stmt->execute();
$document = $stmt->fetch(PDO::FETCH_ASSOC);

// If no record is found
if (!$document) {
    echo "<script>alert('Lost document record not found.'); window.history.back();</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Document Details</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/lost_details.css">
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <img src="img2/logo.png" alt="Logo" class="logo">
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img2/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Admin Profile</a></li>
            <li><a href="customer.php"><img src="img2/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="staff_info.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Staff Information</a></li>
            <li><a href="search_main.php"><img src="img2/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="activitylog.php"><img src="img2/activitylog.png" alt="Activity Icon"> Activity Log</a></li>
            <li><a href="lost_documents.php"><img src="img2/lost.png" alt="Lost Icon"> Lost Documents</a></li>
            <li><a href="../../Logout_Login/Logout.php"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="activity-title">Lost Document Details</h1>

        <div class="details-container">
            <div class="details-row"><strong>Client Name:</strong> <?= htmlspecialchars($document['client_name']) ?></div>
            <div class="details-row"><strong>Certificate of Coverage:</strong> <?= htmlspecialchars($document['certificate_of_coverage']) ?></div>
            <div class="details-row"><strong>Application Date:</strong> <?= htmlspecialchars($document['application_date']) ?></div>
            <div class="details-row"><strong>Status:</strong> 
                <span class="status <?= strtolower($document['status']) ?>"><?= htmlspecialchars($document['status']) ?></span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="lost_customer.php" class="back-btn">Back</a>
            <form method="POST" action="../../PHP_Files/CRUD_Functions/update_lost_status.php">
                <input type="hidden" name="lost_document_id" value="<?= $document['lost_document_id'] ?>">
                <select name="status">
                    <option value="Pending" <?= $document['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Approved" <?= $document['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= $document['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
                <button type="submit" class="update-btn">Update Status</button>
            </form>
        </div>
    </div>

</body>
</html>
