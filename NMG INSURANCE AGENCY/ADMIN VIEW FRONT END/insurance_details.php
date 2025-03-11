<?php

require_once "../../DB_connection/db.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Request: No insurance ID provided.");
}

$insurance_id = (int)$_GET['id']; // Convert to integer for security

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Fetch the insurance details
    $query = "
    SELECT ir.insurance_id, c.full_name, c.email, c.contact_number, 
       v.plate_number, v.chassis_number, v.mv_file_number,
       ir.type_of_insurance, ir.created_at, ir.status, 
       d_or.file_path AS or_picture, 
       d_cr.file_path AS cr_picture
FROM nmg_insurance.insurance_registration ir
JOIN nmg_insurance.clients c ON ir.client_id = c.client_id
JOIN nmg_insurance.vehicles v ON ir.vehicle_id = v.vehicle_id
LEFT JOIN nmg_insurance.documents d_or ON d_or.client_id = ir.client_id 
    AND d_or.vehicle_id = ir.vehicle_id 
    AND d_or.document_type = 'OR'
LEFT JOIN nmg_insurance.documents d_cr ON d_cr.client_id = ir.client_id 
    AND d_cr.vehicle_id = ir.vehicle_id 
    AND d_cr.document_type = 'CR'
WHERE ir.insurance_id = :insurance_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':insurance_id', $insurance_id, PDO::PARAM_INT);
    $stmt->execute();

    $insurance_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$insurance_data) {
        die("No insurance record found for this ID.");
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
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
        <li><a href="search.php"><img src="img2/search.png" alt="Search Icon"> Search Policy</a></li>
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
        <p><strong>Name:</strong> <?php echo htmlspecialchars($insurance_data['full_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($insurance_data['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($insurance_data['contact_number']); ?></p>
        <p><strong>MV File Number:</strong> <?php echo !empty($insurance_data['mv_file_number']) ? htmlspecialchars($insurance_data['mv_file_number']) : 'N/A'; ?></p>
        <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($insurance_data['plate_number']); ?></p>
        <p><strong>Chassis Number:</strong> <?php echo htmlspecialchars($insurance_data['chassis_number']); ?></p>
        <p><strong>Transaction Type:</strong> <?php echo htmlspecialchars($insurance_data['type_of_insurance']); ?></p>
        <p><strong>Applied Date:</strong> <?php echo htmlspecialchars($insurance_data['created_at']); ?></p>
        <p><strong>Status:</strong> <span id="current-status"><?php echo htmlspecialchars($insurance_data['status']); ?></span></p>

        <div class="image-container">
            <div class="image-box">
                <p><strong>OR Image:</strong></p>
                <?php if (!empty($insurance_data['or_picture'])): ?>
                    <a href="../../secured_uploads/<?php echo htmlspecialchars($insurance_data['or_picture']); ?>" target="_blank">
                        <img src="../../secured_uploads/<?php echo htmlspecialchars($insurance_data['or_picture']); ?>" alt="OR Image">
                    </a>
                <?php else: ?>
                    <p>No OR image available</p>
                <?php endif; ?>
            </div>
            <div class="image-box">
                <p><strong>CR Image:</strong></p>
                <?php if (!empty($insurance_data['cr_picture'])): ?>
                    <a href="../../secured_uploads/<?php echo htmlspecialchars($insurance_data['cr_picture']); ?>" target="_blank">
                        <img src="../../secured_uploads/<?php echo htmlspecialchars($insurance_data['cr_picture']); ?>" alt="CR Image">
                    </a>
                <?php else: ?>
                    <p>No CR image available</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="buttons">

        <label for="status-select"><strong>Update Status:</strong></label>
        <select id="status-select">
        <option value="Pending" <?php echo ($insurance_data['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
        <option value="Approved" <?php echo ($insurance_data['status'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
        <option value="Rejected" <?php echo ($insurance_data['status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
    </select>
    <button class="update-btn" onclick="updateStatus()">Update</button>
            <button class="print-or-btn" onclick="printImage('OR')">Print OR</button>
            <button class="print-cr-btn" onclick="printImage('CR')">Print CR</button>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>

function updateStatus() {
    const status = document.getElementById('status-select').value;  // Corrected ID
    const insurance_id = <?php echo $insurance_id; ?>; // Get ID from PHP

    if (!insurance_id) {
        alert("Invalid transaction ID.");
        return;
    }

    console.log("Updating insurance_id:", insurance_id, "with status:", status); // Debugging

    fetch('../../PHP_FILES/CRUD_Functions/update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(insurance_id)}&status=${encodeURIComponent(status)}`
    })
    .then(response => response.text())
    .then(data => {
        console.log("Server Response:", data); // Debug response

        if (data.trim() === "success") {
            alert('Status updated successfully!');
            location.reload();
        } else if (data.trim() === "invalid") {
            alert("Invalid request.");
        } else {
            alert("Failed to update status. Please try again.");
        }
    })
    .catch(error => console.error('Error:', error));
}



    function handleDecision(status) {
        const insurance_id = <?php echo $insurance_id; ?>;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "update_status.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                if (xhr.responseText === "success") {
                    alert(`Transaction has been ${status}`);
                    window.location.reload();
                } else {
                    alert("Failed to update status.");
                }
            }
        };

        xhr.send("id=" + insurance_id + "&status=" + status);
    }

    function toggleSubmenu(event) {
        event.stopPropagation();
        const submenu = event.currentTarget.querySelector('.submenu');
        submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
    }

    function printImage(type) {
        var imageElement = document.querySelector(`.image-box a img[src*='${type}']`);

        if (!imageElement) {
            alert(`No ${type} image available to print.`);
            return;
        }

        var printWindow = window.open("", "_blank");
        printWindow.document.write(`
            <html>
            <head>
                <title>Print ${type}</title>
                <style>
                    body { text-align: center; font-family: Arial, sans-serif; }
                    img { width: 80%; max-width: 600px; margin: 10px; border: 1px solid black; }
                </style>
            </head>
            <body>
                <h2>${type} Image</h2>
                <img src="${imageElement.src}" alt="${type} Image">
                <br><button onclick="window.print();">Print</button>
            </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>

</body>
</html>
