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
           c.birthday,
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
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Insurance Details</title>
<link rel="icon" type="image/png" href="img2/logo.png" />
<link rel="stylesheet" href="css/dashboard.css" />
<link rel="stylesheet" href="css/insurance_details.css" />
<style>
    /* Button and status styles */
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
        border-radius: 4px;
    }
    .green-btn { background-color: #4caf50; }
    .yellow-btn { background-color: #ffeb3b; color: black; }
    .red-btn { background-color: #f44336; }
    .status-btn:hover { opacity: 0.8; }
    .status-btn.active {
        font-weight: bold;
        border: 2px solid black;
    }
    .image-container {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }
    .image-box {
        width: 45%;
    }
    .image-box img {
        width: 100%;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0; top: 0;
        width: 100%; height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 20px 30px;
        border: 1px solid #888;
        width: 400px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        position: relative;
    }
    .modal-content h2 {
        margin-top: 0;
    }
    .modal-content label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .modal-content input[type="date"] {
        width: 100%;
        padding: 8px 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 16px;
    }
    .modal-buttons {
        text-align: right;
    }
    .modal-buttons button {
        padding: 8px 16px;
        font-size: 16px;
        border: none;
        border-radius: 4px;
        margin-left: 10px;
        cursor: pointer;
    }
    .btn-submit {
        background-color: #4caf50;
        color: white;
    }
    .btn-cancel {
        background-color: #f44336;
        color: white;
    }
    .error-message {
        color: red;
        font-size: 14px;
        margin-bottom: 10px;
        display: none;
    }
    /* Print buttons container */
    .buttons {
        margin-top: 20px;
    }
    .buttons button {
        padding: 8px 16px;
        margin-right: 10px;
        font-size: 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .print-or-btn { background-color: #2196F3; color: white; }
    .print-cr-btn { background-color: #673AB7; color: white; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h1 class="page-title">Customer Details</h1>
    <div class="details-section">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($insurance_data['full_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($insurance_data['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($insurance_data['contact_number']); ?></p>
        <p><strong>Birthday:</strong> 
        <?php 
            if (!empty($insurance_data['birthday'])) {
                $birthday = new DateTime($insurance_data['birthday']);
                echo $birthday->format('Y / m / d'); 
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
                $today = new DateTime();
                $age = $birthday->diff($today);
                echo $age->y;
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
            <button id="approveBtn" class="status-btn green-btn <?php echo ($insurance_data['status'] == 'Approved') ? 'active' : ''; ?>">Approved</button>
            <button id="pendingBtn" class="status-btn yellow-btn <?php echo ($insurance_data['status'] == 'Pending') ? 'active' : ''; ?>">Pending</button>
            <button id="rejectBtn" class="status-btn red-btn <?php echo ($insurance_data['status'] == 'Rejected') ? 'active' : ''; ?>">Rejected</button>
        </div>

        <div class="image-container">
            <div class="image-box">
                <p><strong>OR Image:</strong></p>
                <?php if (!empty($insurance_data['or_picture'])): ?>
                    <a href="../../secured_uploads/or/<?php echo htmlspecialchars($insurance_data['or_picture']); ?>" target="_blank">
                        <img src="../../secured_uploads/or/<?php echo htmlspecialchars($insurance_data['or_picture']); ?>" alt="OR Image" />
                    </a>
                <?php else: ?>
                    <p>No OR image available</p>
                <?php endif; ?>
            </div>
            <div class="image-box">
            <p><strong>CR Image:</strong></p>
            <?php if (!empty($insurance_data['cr_picture'])): ?>
                <a href="../../secured_uploads/cr/<?php echo htmlspecialchars($insurance_data['cr_picture']); ?>" target="_blank">
                    <img src="../../secured_uploads/cr/<?php echo htmlspecialchars($insurance_data['cr_picture']); ?>" alt="CR Image" />
                </a>
            <?php else: ?>
                <p>No CR image available</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="buttons">
    <button class="print-or-btn" id="printOrBtn" <?php echo empty($insurance_data['or_picture']) ? 'disabled' : ''; ?>>Print OR Image</button>
    <button class="print-cr-btn" id="printCrBtn" <?php echo empty($insurance_data['cr_picture']) ? 'disabled' : ''; ?>>Print CR Image</button>
</div>

<!-- Appointment Schedule Modal -->
<div id="approvalModal" class="modal">
    <div class="modal-content">
        <h2>Schedule Appointment Date</h2>
        <div class="error-message" id="errorMsg"></div>
        <form id="approvalForm">
            <label for="scheduleDate">Appointment Date:</label>
            <input type="date" id="scheduleDate" name="scheduleDate" required />
            <div class="modal-buttons">
                <button type="submit" class="btn-submit">Submit</button>
                <button type="button" class="btn-cancel" id="cancelModalBtn">Cancel</button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
    // Print Image functionality
    document.getElementById('printOrBtn').addEventListener('click', function() {
        <?php if (!empty($insurance_data['or_picture'])): ?>
            const orImageUrl = '../../secured_uploads/or/<?php echo addslashes($insurance_data['or_picture']); ?>';
            const orWindow = window.open(orImageUrl, '_blank');
            orWindow.focus();
            orWindow.print();
        <?php else: ?>
            alert('No OR image available to print.');
        <?php endif; ?>
    });

    document.getElementById('printCrBtn').addEventListener('click', function() {
        <?php if (!empty($insurance_data['cr_picture'])): ?>
            const crImageUrl = '../../secured_uploads/cr/<?php echo addslashes($insurance_data['cr_picture']); ?>';
            const crWindow = window.open(crImageUrl, '_blank');
            crWindow.focus();
            crWindow.print();
        <?php else: ?>
            alert('No CR image available to print.');
        <?php endif; ?>
    });

    // Status buttons and appointment modal
    const approveBtn = document.getElementById('approveBtn');
    const pendingBtn = document.getElementById('pendingBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    const currentStatusSpan = document.getElementById('current-status');
    const modal = document.getElementById('approvalModal');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const approvalForm = document.getElementById('approvalForm');
    const scheduleDateInput = document.getElementById('scheduleDate');
    const errorMsg = document.getElementById('errorMsg');

    // Disable past dates and set min date 7 days from today
    function setMinDate() {
        const today = new Date();
        const minDate = new Date(today.setDate(today.getDate() + 7));
        const yyyy = minDate.getFullYear();
        const mm = String(minDate.getMonth() + 1).padStart(2, '0');
        const dd = String(minDate.getDate()).padStart(2, '0');
        scheduleDateInput.min = `${yyyy}-${mm}-${dd}`;
    }
    setMinDate();

    // Remove 'active' class from all buttons
    function resetActiveButtons() {
        approveBtn.classList.remove('active');
        pendingBtn.classList.remove('active');
        rejectBtn.classList.remove('active');
    }

    // AJAX function to update status
    function updateStatus(newStatus, appointmentDate = null) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../../PHP_Files/CRUD_Functions/update_status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        let params = `insurance_id=<?php echo $insurance_id; ?>&new_status=${encodeURIComponent(newStatus)}`;
        if (appointmentDate) {
            params += `&schedule_date=${encodeURIComponent(appointmentDate)}`;
        }

        xhr.onload = function() {
            if (xhr.status === 200) {
                currentStatusSpan.textContent = newStatus;
                resetActiveButtons();
                if (newStatus === 'Approved') {
                    approveBtn.classList.add('active');
                } else if (newStatus === 'Pending') {
                    pendingBtn.classList.add('active');
                } else if (newStatus === 'Rejected') {
                    rejectBtn.classList.add('active');
                }
                if (modal.style.display === 'block') {
                    modal.style.display = 'none';
                    approvalForm.reset();
                    errorMsg.style.display = 'none';
                    errorMsg.textContent = '';
                }
            } else {
                alert('Failed to update status: ' + xhr.responseText);
            }
        };

        xhr.onerror = function() {
            alert('Request failed.');
        };

        xhr.send(params);
    }

    // When Approve button clicked, open modal for scheduling appointment date
    approveBtn.addEventListener('click', function() {
        modal.style.display = 'block';
        setMinDate();
        errorMsg.style.display = 'none';
        errorMsg.textContent = '';
    });

    // When Pending or Reject clicked, update directly without modal
    pendingBtn.addEventListener('click', function() {
        if (currentStatusSpan.textContent !== 'Pending') {
            if (confirm('Change status to Pending?')) {
                updateStatus('Pending');
            }
        }
    });

    rejectBtn.addEventListener('click', function() {
        if (currentStatusSpan.textContent !== 'Rejected') {
            if (confirm('Change status to Rejected?')) {
                updateStatus('Rejected');
            }
        }
    });

    // Cancel modal button closes the modal
    cancelModalBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        approvalForm.reset();
        errorMsg.style.display = 'none';
        errorMsg.textContent = '';
    });

    // Approval form submission for appointment scheduling
    approvalForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const selectedDate = scheduleDateInput.value;
        if (!selectedDate) {
            errorMsg.textContent = "Please select a date.";
            errorMsg.style.display = 'block';
            return;
        }

        const minDate = new Date(scheduleDateInput.min);
        const chosenDate = new Date(selectedDate);
        if (chosenDate < minDate) {
            errorMsg.textContent = "Appointment date must be at least 7 days from today.";
            errorMsg.style.display = 'block';
            return;
        }

        updateStatus('Approved', selectedDate);
    });

    // Close modal if clicking outside modal content
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            approvalForm.reset();
            errorMsg.style.display = 'none';
            errorMsg.textContent = '';
        }
    });
</script>
</body>
</html>
