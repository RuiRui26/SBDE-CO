<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/nav.css".css?v=1.2>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav>
    <div class="menu-container nav-wrapper">
        <div class="brand">
            <a href="index.php">
                <img src="img/NMG22.png" alt="insurance-logo" border="0">
            </a>
        </div>

        <!-- Hamburger Icon -->
        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>

        <div class="nav-content">
            <ul class="nav-list">
                <li class="active"><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="benefits.php">Insurance</a></li>
                <li><a href="contact.php">Contacts</a></li>
            </ul>

            <!-- Check if the user is logged in and their role -->
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])): ?>
                <!-- Check the role of the logged-in user -->
                <?php if ($_SESSION['user_role'] === 'Client'): ?>
                    <!-- If logged in as Client, show a user icon button to dashboard -->
                    <button onclick="window.location.href='USER_PROFILE/index.php'" class="profile-enhanced-btn">
                        <img src="img/samplepic.png" alt="Profile" class="profile-avatar">
                        <span>Client Dashboard</span>
                    </button>
                <?php elseif ($_SESSION['user_role'] === 'Admin'): ?>
                    <!-- If logged in as Admin, show Admin dashboard -->
                    <button onclick="window.location.href='../ADMIN VIEW FRONT END/index.php'" class="profile-enhanced-btn">
                        <img src="img/samplepic.png" alt="Profile" class="profile-avatar">
                        <span>Admin Dashboard</span>
                    </button>
                <?php elseif ($_SESSION['user_role'] === 'Secretary'): ?>
                    <!-- If logged in as Secretary, show Secretary dashboard -->
                    <button onclick="window.location.href='../SECRETARY VIEW/index.php'" class="profile-enhanced-btn">
                        <img src="img/samplepic.png" alt="Profile" class="profile-avatar">
                        <span>Secretary Dashboard</span>
                    </button>
                <?php elseif ($_SESSION['user_role'] === 'Staff'): ?>
                    <!-- If logged in as Staff, show Staff dashboard -->
                    <button onclick="window.location.href='../STAFF VIEW/index.php'" class="profile-enhanced-btn">
                        <img src="img/samplepic.png" alt="Profile" class="profile-avatar">
                        <span>Staff Dashboard</span>
                    </button>
                <?php elseif ($_SESSION['user_role'] === 'Agent'): ?>
                    <!-- If logged in as Agent, show Agent dashboard -->
                    <button onclick="window.location.href='../AGENT VIEW/index.php'" class="profile-enhanced-btn">
                        <img src="img/samplepic.png" alt="Profile" class="profile-avatar">
                        <span>Agent Dashboard</span>
                    </button>
                <?php elseif ($_SESSION['user_role'] === 'Cashier'): ?>
                    <!-- If logged in as Cashier, show Cashier dashboard -->
                    <button onclick="window.location.href='../CASHIER_VIEW/index.php'" class="profile-enhanced-btn">
                        <img src="img/samplepic.png" alt="Profile" class="profile-avatar">
                        <span>Cashier Dashboard</span>
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <!-- If not logged in, show "Register" button -->
                <button onclick="window.location.href='../../Logout_Login_USER/register.php'" class="register-btn">Register</button>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    // Toggle mobile menu
    document.querySelector('.hamburger').addEventListener('click', function() {
        document.querySelector('.nav-content').classList.toggle('active');
    });
</script>
</body>
</html>