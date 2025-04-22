<?php
// Start session to access user data
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in first.";
    exit;
}

// Connect to the database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=NMG_Insurance', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Get the current logged-in user's ID from session
$user_id = $_SESSION['user_id'];

// Fetch the client_id from the users table using the user_id
$stmt = $pdo->prepare("SELECT client_id FROM clients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    echo "Client not found.";
    exit;
}

$client_id = $client['client_id'];

// Now retrieve the transaction details using client_id and plate_number (from the GET parameter)
$plate_number = isset($_GET['id']) ? $_GET['id'] : '';

if (!$plate_number) {
    echo "No plate number provided.";
    exit;
}

// Fetch the transaction details
$stmt = $pdo->prepare("
    SELECT 
        ir.created_at AS register_date,
        v.plate_number,
        v.vehicle_type,
        ir.type_of_insurance,
        ir.status AS transaction_status,
        ir.created_at AS insurance_date,
        ir.expired_at AS expiration_date,
        c.full_name,
        c.contact_number,
        CONCAT(c.street_address, ', ', c.barangay, ', ', c.city, ' ', c.zip_code) AS address
    FROM insurance_registration ir
    JOIN vehicles v ON ir.vehicle_id = v.vehicle_id
    JOIN clients c ON ir.client_id = c.client_id
    WHERE ir.client_id = :client_id AND v.plate_number = :plate_number
");

$stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
$stmt->bindParam(':plate_number', $plate_number, PDO::PARAM_STR);
$stmt->execute();

// Fetch transaction data
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

// Set live status from transaction status
$live_status = isset($transaction['transaction_status']) ? $transaction['transaction_status'] : 'Unknown';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="../css/sidebar.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
            display: flex;
        }

        .main-content {
            margin-left: 100px;
            padding: 30px;
            flex-grow: 1;
            width: calc(100% - 250px);
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .client-info {
            background: #fff;
            padding: 20px;
            border-left: 5px solid #007bff;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .client-info p {
            margin: 6px 0;
            font-size: 15px;
        }

        .status-badge {
            padding: 5px 10px;
            font-weight: bold;
            border-radius: 20px;
            font-size: 13px;
        }

        .status-pending { background: #ffc107; color: #fff; }
        .status-approved { background: #28a745; color: #fff; }
        .status-to-pay { background: #006400; color: #fff; }
        .status-claim-benefits { background: #007bff; color: #fff; }

        #live-feed {
            margin-top: 20px;
        }

        .status-flow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            padding: 15px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        .status-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 13px;
        }

        .status-step .circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #fff;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .status-step.active .circle.pending { background: #ffc107; }
        .status-step.active .circle.approved { background: #28a745; }
        .status-step.active .circle.to-pay { background: #006400; }
        .status-step.active .circle.claim { background: #007bff; }

        .status-step.active { font-weight: bold; color: inherit; }
        .status-flow .arrow { font-size: 20px; color: #666; margin: 0 5px; }
    </style>
</head>
<body>

<div class="main-content">
    <h2>Transaction Details</h2>

    <?php if ($transaction): ?>
        <div class="client-info">
            <p><strong>Name:</strong> <?= htmlspecialchars($transaction['full_name']) ?></p>
            <p><strong>Contact Number:</strong> <?= htmlspecialchars($transaction['contact_number']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($transaction['address']) ?></p>
        </div>

        <div class="client-info">
            <p><strong>Plate Number:</strong> <?= htmlspecialchars($transaction['plate_number']) ?></p>
            <p><strong>Vehicle Type:</strong> <?= htmlspecialchars($transaction['vehicle_type']) ?></p>
            <p><strong>Insurance Type:</strong> <?= htmlspecialchars($transaction['type_of_insurance']) ?></p>
            <p><strong>Insurance Date:</strong> <?= date('Y-m-d', strtotime($transaction['insurance_date'])) ?></p>
            <p><strong>Expiration Date:</strong> <?= date('Y-m-d', strtotime($transaction['expiration_date'])) ?></p>
            <p><strong>Status:</strong> <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $live_status)) ?>"><?= htmlspecialchars($live_status) ?></span></p>
        </div>

        <div id="live-feed">
            <h4 style="margin-bottom: 15px;">Live Status Tracker:</h4>
            <div class="status-flow">
                <div class="status-step <?= in_array($live_status, ['Pending', 'Approved', 'To Pay', 'Claim Benefits']) ? 'active' : '' ?>">
                    <span class="circle pending">1</span>
                    <span class="label">Pending</span>
                </div>
                <div class="arrow">→</div>
                <div class="status-step <?= in_array($live_status, ['Approved', 'To Pay', 'Claim Benefits']) ? 'active' : '' ?>">
                    <span class="circle approved">2</span>
                    <span class="label">Approved</span>
                </div>
                <div class="arrow">→</div>
                <div class="status-step <?= in_array($live_status, ['To Pay', 'Claim Benefits']) ? 'active' : '' ?>">
                    <span class="circle to-pay">3</span>
                    <span class="label">To Pay</span>
                </div>
                <div class="arrow">→</div>
                <div class="status-step <?= $live_status === 'Claim Benefits' ? 'active' : '' ?>">
                    <span class="circle claim">4</span>
                    <span class="label">Claim</span>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p>No transaction found for this vehicle.</p>
    <?php endif; ?>
</div>

<script>
    function updateTransactionStatus() {
        $.get('vehicle_status.php?action=fetch_status&plate_number=<?= urlencode($transaction['plate_number']) ?>', function(data) {
            var response = JSON.parse(data);
            var status = response.status;

            // Update status badge
            var badgeClass = '';
            if (status === 'Pending') badgeClass = 'status-pending';
            else if (status === 'Approved') badgeClass = 'status-approved';
            else if (status === 'To Pay') badgeClass = 'status-to-pay';
            else if (status === 'Claim Benefits') badgeClass = 'status-claim-benefits';

            $('.status-badge')
                .removeClass()
                .addClass('status-badge ' + badgeClass)
                .text(status);

            // Update progress tracker
            $('.status-step').removeClass('active');
            if (status === 'Pending') $('.status-step').eq(0).addClass('active');
            else if (status === 'Approved') $('.status-step').slice(0, 2).addClass('active');
            else if (status === 'To Pay') $('.status-step').slice(0, 3).addClass('active');
            else if (status === 'Claim Benefits') $('.status-step').slice(0, 4).addClass('active');
        });
    }

    updateTransactionStatus();
    setInterval(updateTransactionStatus, 30000);
</script>

</body>
</html>
