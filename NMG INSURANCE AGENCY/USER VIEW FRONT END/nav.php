
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
    <link rel="stylesheet" href="css/nav.css">
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

        <div class="hamburger" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <ul class="nav-list">
            <li class="active"><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="benefits.php">Insurance</a></li>
            <li><a href="contact.php">Contacts</a></li>
        </ul>

        

        <!-- Check if the user is logged in and their role -->
    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Client'): ?>
    <!-- If logged in as Client, show a user icon button to dashboard -->
    <button onclick="window.location.href='USER_PROFILE/index.php'" class="profile-enhanced-btn">
    <img src="img/samplepic.png" alt="Profile" class="profile-avatar">
    <span>
                <?php echo $_SESSION['full_name']; ?> <!-- Display the user's name -->
            </span>
</button>

    <?php else: ?>
    <!-- If not logged in or not a Client, show "Register" -->
    <button onclick="window.location.href='../../Logout_Login_USER/register.php'" class="register-btn">Register</button>
    <?php endif; ?>
    </div>
</nav>

<script>
    function toggleMenu() {
        document.querySelector('.nav-list').classList.toggle('active');
    }
</script>

</body>
</html>
