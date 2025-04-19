<?php

require_once "../../DB_connection/db.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Request: No insurance ID provided.");
}

$insurance_id = (int)$_GET['id']; // Convert to integer for security

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Fetch the insurance details, including the birthday
    $query = "
    SELECT ir.insurance_id, c.full_name, c.email, c.contact_number, 
           c.birthday,  -- Add birthday here
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
    <style>
        /* Add these styles for buttons and colors */
        .status-buttons {
            margin-top: 20px;
        }

        .status-btn {
            padding: 10px 20px;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-right: 10px;
        }

        .green-btn {
            background-color: #4CAF50; /* Green for Approved */
        }

        .yellow-btn {
            background-color: #FFEB3B; /* Yellow for Pending */
        }

        .red-btn {
            background-color: #F44336; /* Red for Rejected */
        }

        .status-btn:hover {
            opacity: 0.8;
        }

        .status-btn.active {
            font-weight: bold;
            border: 2px solid black; /* Highlight active button */
        }

        .image-container {
            display: flex;
            justify-content: space-between;
        }

        .image-box {
            width: 45%;
        }

        .image-box img {
            width: 100%;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <h1 class="page-title">Customer Details</h1>
    <div class="details-section">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($insurance_data['full_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($insurance_data['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($insurance_data['contact_number']); ?></p>
      


        <!-- Birthday -->
        <p><strong>Birthday:</strong> 
    <?php 
        if (!empty($insurance_data['birthday'])) {
            $birthday = new DateTime($insurance_data['birthday']);
            echo $birthday->format('Y / m / d');  // Format as YYYY / MM / DD
        } else {
            echo 'N/A';
        }
    ?>
    <span style="font-size: 0.9em; color: grey;">(Year / Month / Day)</span>
</p>

<p><strong>Age:</strong> 
    <?php 
        if (!empty($insurance_data['birthday'])) {
            $birthday = new DateTime($insurance_data['birthday']);
            $today = new DateTime();  // Current date
            $age = $birthday->diff($today);  // Calculate difference between birthday and today
            echo $age->y;  // Display the age in years
        } else {
            echo 'N/A';
        }
    ?>
</p>


        <p><strong>MV File Number:</strong> <?php echo !empty($insurance_data['mv_file_number']) ? htmlspecialchars($insurance_data['mv_file_number']) : 'N/A'; ?></p>
        <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($insurance_data['plate_number']); ?></p>
        <p><strong>Chassis Number:</strong> <?php echo htmlspecialchars($insurance_data['chassis_number']); ?></p>
        <p><strong>Transaction Type:</strong> <?php echo htmlspecialchars($insurance_data['type_of_insurance']); ?></p>
        <p><strong>Applied Date:</strong> <?php echo htmlspecialchars($insurance_data['created_at']); ?></p>
        <p><strong>Status:</strong> <span id="current-status"><?php echo htmlspecialchars($insurance_data['status']); ?></span></p>

        <div class="status-buttons">
            <button 
                class="status-btn green-btn <?php echo ($insurance_data['status'] == 'Approved') ? 'active' : ''; ?>" 
                onclick="updateStatus('Approved')">Approved</button>
            <button 
                class="status-btn yellow-btn <?php echo ($insurance_data['status'] == 'Pending') ? 'active' : ''; ?>" 
                onclick="updateStatus('Pending')">Pending</button>
            <button 
                class="status-btn red-btn <?php echo ($insurance_data['status'] == 'Rejected') ? 'active' : ''; ?>" 
                onclick="updateStatus('Rejected')">Rejected</button>
        </div>

        <div class="image-container">
            <div class="image-box">
                <p><strong>OR Image:</strong></p>
                <?php if (!empty($insurance_data['or_picture'])): ?>
                    <a href="../../secured_uploads/or/<?php echo htmlspecialchars($insurance_data['or_picture']); ?>" target="_blank">
                        <img src="../../secured_uploads/or/<?php echo htmlspecialchars($insurance_data['or_picture']); ?>" alt="OR Image">
                    </a>
                <?php else: ?>
                    <p>No OR image available</p>
                <?php endif; ?>
            </div>
            <div class="image-box">
                <p><strong>CR Image:</strong></p>
                <?php if (!empty($insurance_data['cr_picture'])): ?>
                    <a href="../../secured_uploads/cr/<?php echo htmlspecialchars($insurance_data['cr_picture']); ?>" target="_blank">
                        <img src="../../secured_uploads/cr/<?php echo htmlspecialchars($insurance_data['cr_picture']); ?>" alt="CR Image">
                    </a>
                <?php else: ?>
                    <p>No CR image available</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="buttons">
            <button class="print-or-btn" onclick="printImage('OR')">Print OR</button>
            <button class="print-cr-btn" onclick="printImage('CR')">Print CR</button>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// JavaScript to handle status update
function updateStatus(newStatus) {
    const insurance_id = <?php echo $insurance_id; ?>; // Get ID from PHP

    if (!insurance_id) {
        alert("Invalid transaction ID.");
        return;
    }

    fetch('../../PHP_FILES/CRUD_Functions/update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(insurance_id)}&status=${encodeURIComponent(newStatus)}`
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "success") {
            alert('Status updated successfully!');
            location.reload(); // Reload page to show the new status
        } else {
            alert("Failed to update status. Please try again.");
        }
    })
    .catch(error => console.error('Error:', error));
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
