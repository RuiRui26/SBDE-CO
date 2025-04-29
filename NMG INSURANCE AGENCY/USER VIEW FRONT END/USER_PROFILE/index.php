<?php 
include 'sidebar.php';

// Allow only client role (or add more roles as needed)
$allowed_roles = ['Client']; 

require '../../../Logout_Login/Restricted.php';

// Connect to database and fetch user information
require_once '../../../DB_connection/db.php';
$database = new Database();
$pdo = $database->getConnection();

$user_id = $_SESSION['user_id'];

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

// Get all insurance registration data for this client
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
            v.color
        FROM insurance_registration ir
        JOIN vehicles v ON ir.vehicle_id = v.vehicle_id
        WHERE ir.client_id = :client_id
        ORDER BY ir.created_at DESC
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Holder Profile</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/profile_view.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        /* Insurance Dashboard Styling */
        /* Fix layout conflict with sidebar */
        .container {
            display: flex;
            margin-top: 0; 
            width: 4500px;
            height: 100px;
        }

        .main-content {
            margin-left: 80px; /* Matches sidebar width */
            width: calc(100% - 80px); /* Adjusted width */
            padding: 20px;
            box-sizing: border-box;
            background-color: #f4f6f8;
            margin-top: 60px; /* Match the height of your top-bar */
            min-height: calc(100vh - 60px); /* Full height minus top-bar */
            overflow-y: auto; /* Enable scrolling only for content */
        }
        /* Top bar */
        .top-bar {
            display: flex;
        align-items: center;
        justify-content: space-between;
        background: white;
        padding: 15px 20px;
        width: calc(100% - 80px); /* Adjusted for sidebar */
        position: fixed;
        top: 0;
        left: 80px;
        height: 60px;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        z-index: 1000; /* Ensure it stays above other content */
        }

        /* Profile dropdown styles */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-dropdown .profile {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-dropdown .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            display: none;
            z-index: 999;
            padding: 10px 0;
            min-width: 150px;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu li {
            list-style: none;
        }

        .dropdown-menu li a {
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: background 0.2s ease;
        }

        .dropdown-menu li a:hover {
            background-color: #f0f0f0;
        }

        /* Enhanced Dashboard Styles */
        .dashboard-container {
            padding: 0;
            margin-top: 0;
            width: 100%;
            position: relative;
        }
        
        .section-title {
            font-size: 1.5rem;
            color:rgb(10, 24, 37);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        .insurance-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        .insurance-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 25px;
            transition: all 0.3s ease;
            border-left: 4px solid #3498db;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .insurance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .card-header {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .insurance-type {
            font-size: 1.3rem;
            font-weight: bold;
            color: #3498db;
        }
        
        .status-badge {
            background: #2ecc71;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .card-body {
            margin-bottom: 15px;
        }
        
        .detail-group {
            margin-bottom: 12px;
        }
        
        .detail-label {
            font-weight: bold;
            color:rgb(35, 37, 37);
            display: block;
            margin-bottom: 3px;
            font-size: 0.9rem;
        }
        
        .detail-value {
            color:rgb(55, 63, 71);
            font-size: 1rem;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 4px;
            display: inline-block;
            width: 100%;
            box-sizing: border-box;
        }
        
        .vehicle-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .vehicle-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #3498db;
            grid-column: 1 / -1;
        }
        
        .documents-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            grid-column: 1 / -1;
        }
        
        .documents-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #3498db;
        }
        
        .document-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .document-link {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background: #f1f8fe;
            border-radius: 5px;
            color: #3498db;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .document-link:hover {
            background: #e1f0ff;
            transform: translateY(-2px);
        }
        
        .document-link i {
            margin-right: 8px;
        }
        
        .no-insurance {
            text-align: center;
            padding: 40px 20px;
            background: #f8f9fa;
            border-radius: 10px;
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .no-insurance i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #bdc3c7;
        }
        
        .action-buttons {
            margin-top: 30px;
            text-align: center;
        }
        
        .btn-primary {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .registration-date {
            font-size: 0.9rem;
            color: #7f8c8d;
            text-align: right;
            margin-top: 10px;
            grid-column: 1 / -1;
        }
        .renew-button {
        background-color: #e8f5e9;
        color: #27ae60;
    }
    .renew-button:hover {
        background-color: #d0f0d6;
        color: #1e8449;
    }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .insurance-card {
                grid-template-columns: 1fr;
            }
            
            .vehicle-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation Bar -->
            <header class="top-bar">
                <h2>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h2>
                
                <!-- Profile Section with Dropdown -->
                <div class="profile-dropdown">
                    <div class="profile" onclick="toggleDropdown()">
                        <img src="../img/userprofile.png" alt="User">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <ul class="dropdown-menu" id="dropdownMenu">
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
            
            <!-- Insurance Dashboard Section -->
            <div class="dashboard-container">
                <h3 class="section-title">My Insurance Policies</h3>
                
                <?php if (empty($insurance_data)): ?>
                    <div class="no-insurance">
                        <i class="fas fa-folder-open"></i>
                        <p>You don't have any registered insurance policies yet.</p>
                        <div class="action-buttons">
                            <a href="../register_insurance.php" class="btn-primary">
                                <i class="fas fa-plus"></i> Register New Insurance
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="insurance-grid">
                        <?php foreach ($insurance_data as $policy): ?>
                            <div class="insurance-card">
                                <div class="card-header">
                                    <span class="insurance-type">
                                        <?php 
                                            echo htmlspecialchars($policy['type_of_insurance'] == 'TPL' ? 
                                                'Third Party Liability' : 'Third Party Property Damage'); 
                                        ?>
                                    </span>
                                    <span class="status-badge">Active</span>
                                </div>
                               
                                <div class="applicant-info">
    
                                <div class="detail-group">
                                <span class="detail-label">Full Name</span>
                                <span class="detail-value"><?php echo htmlspecialchars($full_name); ?></span>
                                </div>
    
                                <div class="detail-group">
                                <span class="detail-label">Contact Number</span>
                                <span class="detail-value"><?php echo htmlspecialchars($client['contact_number'] ?? 'N/A'); ?></span>
                                </div>
                                </div>

                                <div class="card-body">
                                    <div class="detail-group">
                                        <span class="detail-label">Policy Reference</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($policy['registration_id'] ?? 'N/A'); ?></span>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <span class="detail-label">Registration Date</span>
                                        <span class="detail-value"><?php echo date('F j, Y', strtotime($policy['created_at'])); ?></span>
                                    </div>
                                </div>
                                
                                <div class="vehicle-info">
                                    <div class="vehicle-title">Vehicle Details</div>
                                    
                                    <?php if (!empty($policy['plate_number'])): ?>
                                        <div class="detail-group">
                                            <span class="detail-label">Plate Number</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($policy['plate_number']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($policy['mv_file_number'])): ?>
                                        <div class="detail-group">
                                            <span class="detail-label">MV File Number</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($policy['mv_file_number']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="detail-group">
                                        <span class="detail-label">Chassis Number</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($policy['chassis_number']); ?></span>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <span class="detail-label">Vehicle Type</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($policy['vehicle_type']); ?></span>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <span class="detail-label">Brand</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($policy['brand']); ?></span>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <span class="detail-label">Model</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($policy['model']); ?></span>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <span class="detail-label">Year</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($policy['year']); ?></span>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <span class="detail-label">Color</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($policy['color']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="documents-section">
                                    <div class="documents-title">Policy Documents</div>
                                    <div class="document-links">
                                        <?php if (!empty($policy['or_picture'])): ?>
                                            <a href="<?php echo htmlspecialchars($policy['or_picture']); ?>" class="document-link" target="_blank">
                                                <i class="fas fa-file-alt"></i> OR Copy
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($policy['cr_picture'])): ?>
                                            <a href="<?php echo htmlspecialchars($policy['cr_picture']); ?>" class="document-link" target="_blank">
                                                <i class="fas fa-file-alt"></i> CR Copy
                                            </a>
                                        <?php endif; ?>
                                        <!-- RENEW BUTTON -->
        <a href="#" class="document-link renew-button">
            <i class="fas fa-sync-alt"></i> Renew
        </a>
                                    </div>
                                </div>
                                
                                <div class="registration-date">
                                    Registered on <?php echo date('M d, Y \a\t H:i', strtotime($policy['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="action-buttons">
                     <button class="btn-primary" onclick="window.location.href='../register_insurance.php';">
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