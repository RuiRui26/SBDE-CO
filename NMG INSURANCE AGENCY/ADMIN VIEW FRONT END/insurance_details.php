<?php
require_once "../../DB_connection/db.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Request: No insurance ID provided.");
}

$insurance_id = (int)$_GET['id']; // Convert to integer for security

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Fetch the insurance details, including the birthday and proxy info
    $query = "
    SELECT 
        ir.insurance_id, c.full_name, c.email, c.contact_number, 
        c.birthday,
        v.plate_number, v.chassis_number, v.mv_file_number,
        v.brand, v.model, v.year, v.color,
        ir.type_of_insurance, ir.created_at, ir.status, 
        d_or.file_path AS or_picture, 
        d_cr.file_path AS cr_picture,
        p.first_name AS proxy_first_name,
        p.middle_name AS proxy_middle_name,
        p.last_name AS proxy_last_name,
        p.birthday AS proxy_birthday,
        p.relationship AS proxy_relationship,
        p.other_relationship AS proxy_other_relationship,
        p.contact_number AS proxy_contact_number,
        p.authorization_letter_path AS proxy_authorization_letter
    FROM nmg_insurance.insurance_registration ir
    JOIN nmg_insurance.clients c ON ir.client_id = c.client_id
    JOIN nmg_insurance.vehicles v ON ir.vehicle_id = v.vehicle_id
    LEFT JOIN nmg_insurance.proxies p ON p.client_id = ir.client_id
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

    // Check if proxy exists
    $has_proxy = !empty($insurance_data['proxy_first_name']);

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
    /* Main container styles */
    .main-content {
        padding: 20px;
        background-color: #f9f9f9;
        min-height: 100vh;
    }
    
    .page-title {
        color: #333;
        margin-bottom: 20px;
        font-size: 24px;
    }
    
    .details-section {
        background-color: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .details-section p {
        margin: 12px 0;
        font-size: 16px;
        line-height: 1.6;
    }
    
    .details-section strong {
        color: #444;
        min-width: 180px;
        display: inline-block;
    }
    
    /* Button and status styles */
    .status-buttons {
        margin: 25px 0;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .status-btn {
        padding: 10px 20px;
        border: none;
        color: white;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 4px;
        font-weight: 500;
    }
    
    .green-btn { 
        background-color: #4caf50; 
    }
    
    .yellow-btn { 
        background-color: #ffc107; 
        color: #333;
    }
    
    .red-btn { 
        background-color: #f44336; 
    }
    
    .status-btn:hover { 
        opacity: 0.9;
        transform: translateY(-2px);
    }
    
    .status-btn.active {
        font-weight: bold;
        box-shadow: 0 0 0 2px #333;
    }
    
    /* Image container styles */
    .image-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin: 25px 0;
    }
    
    .image-box {
        flex: 1 1 45%;
        min-width: 300px;
    }
    
    .image-box img {
        width: 100%;
        max-height: 400px;
        object-fit: contain;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        border: 1px solid #ddd;
    }
    
    .image-box p strong {
        display: block;
        margin-bottom: 8px;
        font-size: 18px;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0; 
        top: 0;
        width: 100%; 
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        width: 90%;
        max-width: 450px;
        position: relative;
        animation: modalopen 0.3s;
    }
    
    @keyframes modalopen {
        from {opacity: 0; transform: translateY(-50px);}
        to {opacity: 1; transform: translateY(0);}
    }
    
    .modal-content h2 {
        margin-top: 0;
        color: #333;
        font-size: 22px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .modal-content label {
        display: block;
        margin: 15px 0 8px;
        font-weight: 600;
        color: #555;
    }
    
    .modal-content input[type="date"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        border: 1px solid #ddd;
        font-size: 16px;
        transition: border 0.3s;
    }
    
    .modal-content input[type="date"]:focus {
        border-color: #4caf50;
        outline: none;
    }
    
    .modal-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
    
    .modal-buttons button {
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-submit {
        background-color: #4caf50;
        color: white;
    }
    
    .btn-submit:hover {
        background-color: #3d8b40;
    }
    
    .btn-cancel {
        background-color: #f44336;
        color: white;
    }
    
    .btn-cancel:hover {
        background-color: #d32f2f;
    }
    
    .error-message {
        color: #f44336;
        font-size: 14px;
        margin: -10px 0 15px;
        display: none;
    }
    
    /* Print buttons container */
    .buttons {
        margin: 20px 0;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .buttons button {
        padding: 10px 20px;
        font-size: 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .buttons button:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }
    
    .buttons button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .print-or-btn { 
        background-color: #2196F3; 
        color: white; 
    }
    
    .print-cr-btn { 
        background-color: #673AB7; 
        color: white; 
    }
    
    /* Proxy button style */
    .proxy-btn {
        background-color: #607d8b;
        color: white;
    }
    
    /* Proxy details styles */
    .proxy-details p {
        margin: 10px 0;
    }
    
    .proxy-details strong {
        display: inline-block;
        min-width: 160px;
        color: #555;
    }
    
    .proxy-details img {
        max-width: 100%;
        margin-top: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .image-box {
            flex: 1 1 100%;
        }
        
        .modal-content {
            width: 95%;
            margin: 20% auto;
        }
    }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h1 class="page-title">Customer Details</h1>
    
    <div class="details-section">
    <button type="button" class="btn btn-secondary" onclick="window.history.back()">← Back</button>

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
        <p><strong>Vehicle Brand:</strong> <?php echo htmlspecialchars($insurance_data['brand']); ?></p>
        <p><strong>Vehicle Model:</strong> <?php echo htmlspecialchars($insurance_data['model']); ?></p>
        <p><strong>Vehicle Year:</strong> <?php echo htmlspecialchars($insurance_data['year']); ?></p>
        <p><strong>Vehicle Color:</strong> <?php echo htmlspecialchars($insurance_data['color']); ?></p>
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
            <?php if ($has_proxy): ?>
                <button class="proxy-btn" id="viewProxyBtn">View Proxy Details</button>
            <?php endif; ?>
        </div>
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
                    <button type="button" class="btn-cancel" id="cancelModalBtn">Cancel</button>
                    <button type="submit" class="btn-submit">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Proxy Details Modal -->
    <div id="proxyModal" class="modal">
        <div class="modal-content">
            <h2>Proxy Details</h2>
            <div class="proxy-details">
                <?php if ($has_proxy): ?>
                    <p><strong>Proxy Name:</strong> 
    <?php 
        echo htmlspecialchars($insurance_data['proxy_first_name'] . ' ');
        if (!empty($insurance_data['proxy_middle_name'])) {
            echo htmlspecialchars($insurance_data['proxy_middle_name'] . ' ');
        }
        echo htmlspecialchars($insurance_data['proxy_last_name']);
    ?>
</p>

                    <p><strong>Birthday:</strong> 
                        <?php 
                            if (!empty($insurance_data['proxy_birthday'])) {
                                $proxyBirthday = new DateTime($insurance_data['proxy_birthday']);
                                echo $proxyBirthday->format('Y / m / d');
                            } else {
                                echo 'N/A';
                            }
                        ?>
                    </p>
                    <p><strong>Relationship:</strong> 
                        <?php 
                            echo htmlspecialchars($insurance_data['proxy_relationship']);
                            if (!empty($insurance_data['proxy_other_relationship'])) {
                                echo ' (' . htmlspecialchars($insurance_data['proxy_other_relationship']) . ')';
                            }
                        ?>
                    </p>
                    <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($insurance_data['proxy_contact_number']); ?></p>
                    
                    <?php if (!empty($insurance_data['proxy_authorization_letter'])): ?>
                        <p><strong>Authorization Letter:</strong></p>
                        <img src="../../secured_uploads/proxy_docs/<?php echo htmlspecialchars($insurance_data['proxy_authorization_letter']); ?>" 
                             alt="Authorization Letter" />
                    <?php endif; ?>
                <?php else: ?>
                    <p>No proxy details available for this transaction.</p>
                <?php endif; ?>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" id="closeProxyModal">Close</button>
            </div>
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

    // Disable past dates and set min date 3 days from today
    function setMinDate() {
        const today = new Date();
        const minDate = new Date(today.setDate(today.getDate() + 3));
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

        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 3); // 3 days from today
        const chosenDate = new Date(selectedDate);
        if (chosenDate < minDate) {
            errorMsg.textContent = "Appointment date must be at least 3 days from today.";
            errorMsg.style.display = 'block';
            return;
        }

        updateStatus('Approved', selectedDate);
    });

    // Proxy Modal Logic
    const viewProxyBtn = document.getElementById('viewProxyBtn');
    const proxyModal = document.getElementById('proxyModal');
    const closeProxyModal = document.getElementById('closeProxyModal');

    if (viewProxyBtn) {
        viewProxyBtn.addEventListener('click', () => {
            proxyModal.style.display = 'block';
        });
    }

    closeProxyModal.addEventListener('click', () => {
        proxyModal.style.display = 'none';
    });

    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
            approvalForm.reset();
            errorMsg.style.display = 'none';
            errorMsg.textContent = '';
        }
        if (e.target === proxyModal) {
            proxyModal.style.display = 'none';
        }
    });
</script>
</body>
</html>