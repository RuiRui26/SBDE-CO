<?php
include 'sidebar.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Please log in first.";
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=NMG_Insurance', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Fetch client_id linked to user_id
$stmt = $pdo->prepare("SELECT client_id FROM clients WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    echo "Client not found.";
    exit;
}

$client_id = $client['client_id'];

// Sanitize GET params for plate_number and mv_file_number
$plate_number = filter_input(INPUT_GET, 'plate_number', FILTER_SANITIZE_STRING);
$mv_file_number = filter_input(INPUT_GET, 'mv_file_number', FILTER_SANITIZE_STRING);

if (!$plate_number && !$mv_file_number) {
    echo "No plate number or MV file number provided.";
    exit;
}

// SQL condition depends on which identifier is provided
if ($plate_number) {
    $vehicleCondition = "v.plate_number = :identifier";
    $identifier = $plate_number;
} else {
    $vehicleCondition = "v.mv_file_number = :identifier";
    $identifier = $mv_file_number;
}

$stmt = $pdo->prepare("
    SELECT 
        ir.created_at AS registered_at,
        ir.start_date AS start_date,
        v.plate_number,
        v.mv_file_number,
        v.vehicle_type,
        ir.type_of_insurance,
        ir.status AS transaction_status,
        ir.expired_at AS expiration_date,
        ir.is_paid,
        ir.is_claimed,
        c.full_name,
        c.contact_number,
        CONCAT_WS(', ', c.street_address, c.barangay, c.city, c.zip_code) AS address
    FROM insurance_registration ir
    JOIN vehicles v ON ir.vehicle_id = v.vehicle_id
    JOIN clients c ON ir.client_id = c.client_id
    WHERE ir.client_id = :client_id AND $vehicleCondition
");
$stmt->execute([
    ':client_id' => $client_id,
    ':identifier' => $identifier
]);

$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    echo "No transaction found for this vehicle.";
    exit;
}

// Determine combined live status for display
function getCombinedStatus($status, $is_paid, $is_claimed) {
    if ($status === 'Rejected') {
        return 'Rejected';
    }

    if ($status === 'Pending') {
        return 'Pending';
    }

    if ($status === 'Approved') {
        if ($is_paid === 'Unpaid') {
            return 'To Pay';
        } else { // Paid
            if ($is_claimed === 'Unclaimed') {
                return 'To Claim';
            } else { // Claimed
                return 'Claimed';
            }
        }
    }

    // Default fallback
    return $status;
}

$live_status = getCombinedStatus($transaction['transaction_status'], $transaction['is_paid'], $transaction['is_claimed']);

function statusClass($status) {
    return strtolower(str_replace(' ', '-', $status));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Transaction Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../css/sidebar.css" />
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
            display: inline-block;
            color: white;
        }
        .status-pending { background: #ffc107; }
        .status-approved { background: #28a745; }
        .status-to-pay { background: #006400; }
        .status-to-claim { background: #17a2b8; } /* info blue */
        .status-claimed { background: #007bff; }
        .status-rejected { background: #dc3545; }
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
            color: #666;
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
        .status-step.active {
            font-weight: bold;
            color: inherit;
        }
        .status-step.active .circle.pending { background: #ffc107; }
        .status-step.active .circle.approved { background: #28a745; }
        .status-step.active .circle.to-pay { background: #006400; }
        .status-step.active .circle.to-claim { background: #17a2b8; }
        .status-step.active .circle.claimed { background: #007bff; }
        .status-step.active .circle.rejected { background: #dc3545; }
        .status-flow .arrow {
            font-size: 20px;
            color: #666;
            margin: 0 5px;
        }
    </style>
</head>
<body>

<div class="main-content">
    <h2>Transaction Details</h2>

    <div class="client-info">
        <p><strong>Name:</strong> <?= htmlspecialchars($transaction['full_name']) ?></p>
        <p><strong>Contact Number:</strong> <?= htmlspecialchars($transaction['contact_number']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($transaction['address']) ?></p>
    </div>

    <div class="client-info">
        <p>
            <strong>Vehicle Identifier:</strong> 
            <?php 
            if (!empty($transaction['plate_number'])) {
                echo htmlspecialchars($transaction['plate_number']);
            } else {
                echo "MV File: " . htmlspecialchars($transaction['mv_file_number']);
            }
            ?>
        </p>
        <p><strong>Vehicle Type:</strong> <?= htmlspecialchars($transaction['vehicle_type']) ?></p>
        <p><strong>Insurance Type:</strong> <?= htmlspecialchars($transaction['type_of_insurance']) ?></p>
        <p><strong>Insurance Start Date:</strong> <?= date('Y-m-d', strtotime($transaction['start_date'])) ?></p>
        <p><strong>Registration Date (Applied):</strong> <?= date('Y-m-d', strtotime($transaction['registered_at'])) ?></p>
        <p><strong>Expiration Date:</strong> 
            <?php 
            if ($transaction['expiration_date']) {
                $expirationDate = date('Y-m-d', strtotime($transaction['expiration_date']));
                echo $expirationDate;
                
                // Calculate days until expiration
                $today = new DateTime();
                $expiryDate = new DateTime($transaction['expiration_date']);
                $interval = $today->diff($expiryDate);
                $daysLeft = $interval->days;
                
                // Check if expiration is within 30 days and in the future
                if ($daysLeft <= 30 && $interval->invert == 0) {
                    echo ' <span class="expiration-warning" style="color: #d9534f; font-weight: bold;">(Expires in ' . $daysLeft . ' days)</span>';
                } elseif ($interval->invert == 1) {
                    echo ' <span class="expiration-warning" style="color: #d9534f; font-weight: bold;">(Expired)</span>';
                }
            } else {
                echo 'N/A';
            }
            ?>
        </p>
        <p>
            <strong>Status:</strong> 
            <span class="status-badge status-<?= statusClass($live_status) ?>">
                <?= htmlspecialchars($live_status) ?>
            </span>
        </p>
    </div>

    <div id="live-feed">
        <h4 style="margin-bottom: 15px;">Live Status Tracker:</h4>
        <div class="status-flow" aria-label="Transaction status flow">
            <div class="status-step <?= in_array($live_status, ['Pending', 'To Pay', 'To Claim', 'Claimed']) ? 'active' : '' ?>">
                <span class="circle pending" aria-label="Step 1: Pending">1</span>
                <span class="label">Pending</span>
            </div>
            <div class="arrow" aria-hidden="true">→</div>
            <div class="status-step <?= in_array($live_status, ['To Pay', 'To Claim', 'Claimed']) ? 'active' : '' ?>">
                <span class="circle approved" aria-label="Step 2: Approved">2</span>
                <span class="label">Approved</span>
            </div>
            <div class="arrow" aria-hidden="true">→</div>
            <div class="status-step <?= in_array($live_status, ['To Pay', 'To Claim', 'Claimed']) ? 'active' : '' ?>">
                <span class="circle to-pay" aria-label="Step 3: To Pay">3</span>
                <span class="label">To Pay</span>
            </div>
            <div class="arrow" aria-hidden="true">→</div>
            <div class="status-step <?= in_array($live_status, ['To Claim', 'Claimed']) ? 'active' : '' ?>">
                <span class="circle to-claim" aria-label="Step 4: To Claim">4</span>
                <span class="label">To Claim</span>
            </div>
            <div class="arrow" aria-hidden="true">→</div>
            <div class="status-step <?= $live_status === 'Claimed' ? 'active' : '' ?>">
                <span class="circle claimed" aria-label="Step 5: Claimed">5</span>
                <span class="label">Claimed</span>
            </div>
        </div>
    </div>

    <?php if ($live_status === 'Rejected'): ?>
        <p style="color:red; font-weight:bold; margin-top: 20px;">Your insurance application has been rejected.</p>
    <?php endif; ?>

</div>

<script>
    function updateTransactionStatus() {
        let urlParams = new URLSearchParams(window.location.search);
        let plate_number = urlParams.get('plate_number');
        let mv_file_number = urlParams.get('mv_file_number');

        let queryParam = '';
        if (plate_number) {
            queryParam = 'plate_number=' + encodeURIComponent(plate_number);
        } else if (mv_file_number) {
            queryParam = 'mv_file_number=' + encodeURIComponent(mv_file_number);
        } else {
            console.error('No identifier found for AJAX request.');
            return;
        }

        $.ajax({
            url: 'get_transaction_status.php?' + queryParam,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.status === 'success') {
                    const statusText = data.live_status;
                    const badge = $('.status-badge');
                    badge.text(statusText);

                    // Update badge classes
                    badge.removeClass();
                    badge.addClass('status-badge status-' + statusText.toLowerCase().replace(/ /g, '-'));

                    // Update live status flow
                    $('.status-step').removeClass('active');
                    if (['Pending', 'To Pay', 'To Claim', 'Claimed'].includes(statusText)) {
                        $('.status-step').eq(0).addClass('active'); // Pending
                    }
                    if (['To Pay', 'To Claim', 'Claimed'].includes(statusText)) {
                        $('.status-step').eq(1).addClass('active'); // Approved
                        $('.status-step').eq(2).addClass('active'); // To Pay
                    }
                    if (['To Claim', 'Claimed'].includes(statusText)) {
                        $('.status-step').eq(3).addClass('active'); // To Claim
                    }
                    if (statusText === 'Claimed') {
                        $('.status-step').eq(4).addClass('active'); // Claimed
                    }

                    if (statusText === 'Rejected') {
                        alert('Your insurance application has been rejected.');
                    }
                } else {
                    console.error('Error fetching status:', data.message);
                }
            },
            error: function() {
                console.error('AJAX error when fetching transaction status.');
            }
        });
    }

    // Poll status every 10 seconds
    setInterval(updateTransactionStatus, 10000);
</script>

</body>
</html>
