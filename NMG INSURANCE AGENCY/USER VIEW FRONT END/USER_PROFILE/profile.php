<?php 
include 'sidebar.php';
require '../../../Logout_Login_USER/Restricted.php';

// Connect to database and fetch user information
require_once '../../../DB_connection/db.php';
$database = new Database();
$pdo = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get client information
$stmt = $pdo->prepare("SELECT full_name FROM clients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

$full_name = $client['full_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Holder Profile</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
                        <li><a href="profile_view.php">View Profile</a></li>
                        <li><a href="#">Settings</a></li>
                        <li><a href="../../../Logout_Login_USER/Logout.php">Logout</a></li>
                    </ul>
                </div>
            </header>
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