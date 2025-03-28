<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/nav.css">
</head>
<body>
<nav>
    <div class="menu-container nav-wrapper">
        <div class="brand">
            <a href="index.php">
                <img src="img/NMG22.png" alt="insurancy-logo" border="0">
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

        <!-- Register Button -->
        <button onclick="window.location.href='USER_PROFILE/profile.php'" class="register-btn">Register</button>
    </div>
</nav>

<script>
    function toggleMenu() {
        document.querySelector('.nav-list').classList.toggle('active');
    }
</script>

</body>
</html>
