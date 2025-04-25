<?php
include 'sidebar.php';
require '../../../Logout_Login/Restricted.php';

// Connect to database and fetch user information
require_once '../../../DB_connection/db.php';
$database = new Database();
$pdo = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get client information
$stmt = $pdo->prepare("SELECT client_id, full_name, email, contact_number, address FROM clients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

$full_name = $client['full_name'] ?? 'User';
$email = $client['email'] ?? 'N/A';
$contact = $client['contact_number'] ?? 'N/A';
$address = $client['address'] ?? 'N/A';
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
    <title>User Profile</title>
    <link rel="stylesheet" href="../css/profile_view.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>
<body>
    <div class="container">
        
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

        <div class="insurance-section">
            <h3>Car Insurance Details</h3>
            <table>
                <thead>
                    <tr>
                        <th>Policy Number</th>
                        <th>Car Model</th>
                        <th>Insurance Type</th>
                        <th>Expiration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($insurance_data)) {
                        foreach ($insurance_data as $insurance) {
                          $registrationId = isset($insurance['registration_id']) ? $insurance['registration_id'] : 'N/A';
                          $brand = isset($insurance['brand']) ? $insurance['brand'] : 'N/A';
                          $model = isset($insurance['model']) ? $insurance['model'] : 'N/A';
                          $type = isset($insurance['type_of_insurance']) ? $insurance['type_of_insurance'] : 'N/A';

                            echo "<tr>
                                    <td>{$registrationId}</td>
                                    <td>{$brand} {$model}</td>
                                    <td>{$type}</td>
                                    <td>N/A</td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No insurance details found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>