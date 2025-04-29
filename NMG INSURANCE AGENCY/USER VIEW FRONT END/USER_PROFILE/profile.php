<?php 
include 'sidebar.php';

// Allow the Client role to access this page
$allowed_roles = ['Client'];
require '../../../Logout_Login/Restricted.php';

// Connect to database and fetch user information
require_once '../../../DB_connection/db.php';
$database = new Database();
$pdo = $database->getConnection();

$user_id = $_SESSION['user_id'] ?? null;

// Get client information
$stmt = $pdo->prepare("SELECT client_id, full_name, contact_number, email, address FROM clients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

$full_name = $client['full_name'] ?? 'User';
$email = $client['email'] ?? '';
$contact = $client['contact_number'] ?? '';
$address = $client['address'] ?? '';
$client_id = $client['client_id'] ?? null;

// Get insurance registration data for this client (Approved and Paid)
$insurance_data = [];
if ($client_id) {
    $stmt = $pdo->prepare("
        SELECT 
            ir.*, 
            v.plate_number, 
            v.vehicle_type, 
            v.chassis_number, 
            v.mv_file_number, 
            v.type_of_insurance,
            v.brand,
            v.model,
            v.year,
            v.color,
            DATE_ADD(ir.start_date, INTERVAL 1 YEAR) as expiry_date,
            DATEDIFF(DATE_ADD(ir.start_date, INTERVAL 1 YEAR), CURDATE()) as days_remaining,
            DATEDIFF(ir.start_date, CURDATE()) as start_in_days
        FROM insurance_registration ir
        JOIN vehicles v ON ir.vehicle_id = v.vehicle_id
        WHERE ir.client_id = :client_id
        AND ir.status = 'Approved' 
        AND ir.is_paid = 'Paid'
        ORDER BY 
            CASE 
                WHEN ir.start_date > CURDATE() THEN 1  # Not yet started first
                WHEN ir.expired_at < CURDATE() THEN 3  # Expired last
                ELSE 2                                # Active policies in middle
            END,
            ir.start_date DESC
    ");
    $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
    $stmt->execute();
    $insurance_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Policy Holder Profile</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/profile_view.css">
    <link rel="stylesheet" href="css/profile.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        .status-not-started {
            background-color: #FFA500; /* Orange */
            color: white;
        }
        .claim-badge {
            background-color: #6A5ACD; /* Slate blue */
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .btn-claim-benefits {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }
        .btn-claim-benefits:hover {
            background-color: #45a049;
        }
        .benefits-status {
            margin-top: 5px;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <main class="main-content">
            <header class="top-bar">
                <h2>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h2>
                <div class="profile-dropdown">
                    <div class="profile" onclick="toggleDropdown()">
                        <img src="../img/userprofile.png" alt="User">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <ul class="dropdown-menu" id="dropdownMenu">
                        <li><a href="#">Settings</a></li>
                        <li><a href="../../../Logout_Login_USER/Logout.php">Logout</a></li>
                    </ul>
                </div>
            </header>

            <div class="profile-section">
                <div class="profile-picture">
                    <img src="../img/userprofile.png" alt="User Picture">
                </div>
                <div class="user-info">
                    <h2><?php echo htmlspecialchars($full_name); ?></h2>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($contact); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($address); ?></p>
                </div>
            </div>
            
            <div class="dashboard-container">
                <h3 class="section-title">My Insurance Policies</h3>

                <?php if (empty($insurance_data)): ?>
                    <div class="no-insurance">
                        <i class="fas fa-folder-open"></i>
                        <p>You don't have any approved insurance policies at this time.</p>
                        <div class="action-buttons">
                            <a href="register_insurance.php" class="btn-primary">
                                <i class="fas fa-plus"></i> Register New Insurance
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="insurance-grid">
                        <?php foreach ($insurance_data as $policy): 
                            // Determine policy status
                            $days_remaining = $policy['days_remaining'];
                            $start_in_days = $policy['start_in_days'];
                            
                            if ($start_in_days > 0) {
                                $status = 'Not Yet Started';
                                $badge_class = 'status-not-started';
                            } elseif ($days_remaining <= 0) {
                                $status = 'Expired';
                                $badge_class = 'status-expired';
                            } elseif ($days_remaining <= 30) {
                                $status = 'Active (Expiring Soon)';
                                $badge_class = 'status-expiring';
                            } else {
                                $status = 'Active';
                                $badge_class = 'status-active';
                            }
                            
                            // Claim status
                            $is_claimed = $policy['is_claimed'] === 'Claimed';
                        ?>
                            <div class="insurance-card">
                                <div class="card-header">
                                    <span class="insurance-type">
                                        <?php 
                                            $typeText = '';
                                            if ($policy['type_of_insurance'] == 'TPL') {
                                                $typeText = 'Third Party Liability';
                                            } elseif ($policy['type_of_insurance'] == 'TPPD') {
                                                $typeText = 'Third Party Property Damage';
                                            } else {
                                                $typeText = htmlspecialchars($policy['type_of_insurance']);
                                            }
                                            echo $typeText;
                                        ?>
                                    </span>
                                    <span class="status-badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                    <?php if ($is_claimed): ?>
                                        <span class="claim-badge">Claimed</span>
                                    <?php endif; ?>
                                </div>

                                <div class="policy-period">
                                    <div class="detail-group">
                                        <span class="detail-label">Coverage Period:</span>
                                        <span class="detail-value">
                                            <?php 
                                                echo $policy['start_date'] ? date('M j, Y', strtotime($policy['start_date'])) : 'Not Set';
                                                echo ' to ';
                                                echo $policy['expiry_date'] ? date('M j, Y', strtotime($policy['expiry_date'])) : 'Not Set';
                                            ?>
                                        </span>
                                    </div>
                                    <?php if ($start_in_days > 0): ?>
                                        <div class="starts-in">
                                            Starts in <?php echo $start_in_days; ?> days
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body">
                                    <div class="detail-group">
                                        <span class="detail-label">Policy ID</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($policy['insurance_id']); ?></span>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Payment Status</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($policy['is_paid']); ?></span>
                                    </div>
                                </div>

                                <div class="vehicle-info">
                                    <div class="vehicle-title">Vehicle Details</div>
                                    <div class="detail-group">
                                        <span class="detail-label"><?php echo !empty($policy['plate_number']) ? 'Plate Number' : 'MV File Number'; ?></span>
                                        <span class="detail-value">
                                            <?php echo !empty($policy['plate_number']) 
                                                ? htmlspecialchars($policy['plate_number']) 
                                                : htmlspecialchars($policy['mv_file_number']); ?>
                                        </span>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Brand/Model</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($policy['brand']); ?> <?php echo htmlspecialchars($policy['model']); ?></span>
                                    </div>
                                    <div class="detail-group">
                                        <span class="detail-label">Year/Color</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($policy['year']); ?> • <?php echo htmlspecialchars($policy['color']); ?></span>
                                    </div>
                                </div>

                                <div class="action-buttons">
                                    <?php if ($status === 'Active' || $status === 'Active (Expiring Soon)'): ?>
                                        <?php if (!$is_claimed): ?>
                                            <button class="btn-claim" onclick="window.location.href='file_claim.php?id=<?php echo $policy['insurance_id']; ?>'">
                                                <i class="fas fa-file-claim"></i> File Claim
                                            </button>
                                        <?php else: ?>
                                            <div class="claim-details">
                                                <span class="benefits-status">Claim Benefits: <?php echo htmlspecialchars($policy['benefits_status'] ?? 'Processing'); ?></span>
                                                <?php if (($policy['benefits_status'] ?? '') === 'Unclaimed'): ?>
                                                    <button class="btn-claim-benefits" onclick="window.location.href='claim_benefits.php?id=<?php echo $policy['insurance_id']; ?>'">
                                                        <i class="fas fa-hand-holding-usd"></i> Claim Benefits
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <button class="btn-renew" onclick="window.location.href='renew_insurance.php?id=<?php echo $policy['insurance_id']; ?>'">
                                            <i class="fas fa-sync-alt"></i> Renew
                                        </button>
                                    <?php elseif ($status === 'Not Yet Started'): ?>
                                        <button class="btn-view" onclick="window.location.href='policy_details.php?id=<?php echo $policy['insurance_id']; ?>'">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                    <?php elseif ($status === 'Expired'): ?>
                                        <button class="btn-renew" onclick="window.location.href='renew_insurance.php?id=<?php echo $policy['insurance_id']; ?>'">
                                            <i class="fas fa-sync-alt"></i> Renew Now
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="action-buttons">
                        <button class="btn-primary" onclick="window.location.href='register_insurance.php';">
                            <i class="fas fa-plus"></i> Register Another Insurance
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function toggleDropdown() {
            document.getElementById("dropdownMenu").classList.toggle("show");
        }
        window.onclick = function(event) {
            if (!event.target.matches('.profile, .profile *')) {
                let dropdown = document.getElementById("dropdownMenu");
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show'); 
                }
            }
        };
    </script>
</body>
</html>