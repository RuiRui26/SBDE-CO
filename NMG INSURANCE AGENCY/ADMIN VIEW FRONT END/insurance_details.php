<?php
session_start();
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';

// Database connection using PDO
require '../../DB_connection/db.php'; // Your database connection file

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User is not logged in.");
}

// Get the insurance_id from the URL parameter
$insurance_id = isset($_GET['insurance_id']) ? $_GET['insurance_id'] : null;
if (!$insurance_id) {
    die("Insurance ID is missing.");
}

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection(); // Get the PDO connection

// SQL query to fetch client and insurance details
$query = "
    SELECT 
        c.full_name, c.email, c.contact_number, 
        ir.type_of_insurance, ir.status, ir.or_picture, ir.cr_picture, ir.created_at 
    FROM 
        insurance_registration ir
    INNER JOIN 
        clients c ON ir.client_id = c.client_id
    WHERE 
        ir.insurance_id = :insurance_id
";

// Prepare the query using PDO
$stmt = $pdo->prepare($query);
$stmt->bindParam(':insurance_id', $insurance_id, PDO::PARAM_INT);
$stmt->execute();

// Fetch data
$insurance_details = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$insurance_details) {
    die("No record found.");
}

if (!isset($_GET['insurance_id'])) {
    die("Insurance ID is missing from the URL.");
} else {
    echo "Insurance ID: " . $_GET['insurance_id']; // Debugging line to check if it's passed
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Details</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/insurance_details.css">
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
        <li class="has-submenu" onclick="toggleSubmenu(event)">
            <a href="#"><img src="img2/setting.png" alt="Setting Icon"> Settings</a>
            <ul class="submenu">
                <li><a href="page_management.php">Page Management</a></li>
            </ul>
        </li>
        <li><a href="../../Logout_Login/Logout.php"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <h1 class="page-title">Customer Details</h1>
    <div class="details-section">
        <p><strong>Name:</strong> <span id="customer-name"><?php echo htmlspecialchars($insurance_details['full_name']); ?></span></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($insurance_details['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($insurance_details['contact_number']); ?></p>
        <p><strong>Transaction Type:</strong> <?php echo htmlspecialchars($insurance_details['type_of_insurance']); ?></p>
        <p><strong>Applied Date:</strong> <?php echo htmlspecialchars($insurance_details['created_at']); ?></p>
        <p><strong>Status:</strong> <span id="current-status"><?php echo htmlspecialchars($insurance_details['status']); ?></span></p>

        <!-- OR and CR Images -->
        <div class="image-container">
            <div class="image-box">
                <p><strong>OR Image:</strong></p>
                <?php if (!empty($insurance_details['or_picture'])): ?>
                    <a href="img2/<?php echo htmlspecialchars($insurance_details['or_picture']); ?>" target="_blank">
                        <img src="img2/<?php echo htmlspecialchars($insurance_details['or_picture']); ?>" alt="OR Image">
                    </a>
                <?php else: ?>
                    <p>No OR image available</p>
                <?php endif; ?>
            </div>
            <div class="image-box">
                <p><strong>CR Image:</strong></p>
                <?php if (!empty($insurance_details['cr_picture'])): ?>
                    <a href="img2/<?php echo htmlspecialchars($insurance_details['cr_picture']); ?>" target="_blank">
                        <img src="img2/<?php echo htmlspecialchars($insurance_details['cr_picture']); ?>" alt="CR Image">
                    </a>
                <?php else: ?>
                    <p>No CR image available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="buttons">
        <button class="accept-btn" onclick="handleDecision('Approved')">Accept</button>
        <button class="decline-btn" onclick="handleDecision('Rejected')">Decline</button>
    </div>
</div>

<!-- JavaScript -->
<script>
    function handleDecision(status) {
        // Send the status update via AJAX to the backend
        const insurance_id = <?php echo $insurance_id; ?>;
        
        // Creating the request
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "update_status.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                if (xhr.responseText === "success") {
                    alert(`Transaction has been ${status}`);
                    window.location.reload(); // Reload the page to update the status
                } else {
                    alert("Failed to update status.");
                }
            }
        };

        // Sending the request with insurance_id and new status
        xhr.send("id=" + insurance_id + "&status=" + status);
    }

    // Toggle Submenu for Settings (Hover + Click Support)
    function toggleSubmenu(event) {
        event.stopPropagation(); // Prevent event from bubbling up
        const submenu = event.currentTarget.querySelector('.submenu');
        submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
    }
</script>

</body>
</html>

<?php
// Close the PDO connection
$pdo = null;
?>
