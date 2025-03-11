<?php 
session_start();
$allowed_roles = ['Client']; // Only clients can access
require '../../Logout_Login_USER/Restricted.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appoint | NMG Insurance Agency</title>

    <link rel="stylesheet" href="css/appoint_loss.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/NMG3.png">
</head>

<body>
       <!-- Header with Logo -->
    <header class="header">
        <img src="img/NMG3.png" alt="NMG Insurance Logo" class="logo">
        <h1 class="header-title">Retrieve Your Lost Documents</h1>
    </header>
    <!-- Main Form Section -->
    <div class="form-section">
   
<<<<<<< Updated upstream
        <form action="../PHP_Files/User_View/appoint_lost.php" method="POST" enctype="multipart/form-data" class="insurance-form">
=======
            <form action="success_page.php" method="POST" enctype="multipart/form-data" class="insurance-form">

>>>>>>> Stashed changes

            <!-- Left Column -->
            <div class="form-column">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required placeholder="Enter your full name">

                <label for="coc">Certificate of Coverage (COC):</label>
                <input type="text" id="coc" name="coc" required placeholder="Enter your COC number">
            </div>


            <!-- Submit Button -->
            <div class="submit-container">
                <button type="submit" class="submit-btn">Appoint Lost Document</button>
            </div>

        </form>
    </div>

    <!-- Footer -->
    <footer>
        <p>Copyright NMG Insurance Agency ©2025</p>
    </footer>

</body>

</html>
