<?php 
session_start();
$allowed_roles = ['Client']; // Only clients can access
require '../../Logout_Login_USER/Restricted.php';
//l
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply | NMG Insurance Agency</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/NMG3.png">

    <!-- External CSS link -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/apply_choices.css">
</head>
<body>

     <!-- Navigation -->
     <?php include 'nav.php'; ?>

    <!-- Apply Choices Section -->
    <section class="apply-section">
        <div class="apply-container">
        

            <!-- Box 1 -->
            <div class="apply-box">
                <img src="img/NMG3.png" alt="Lost Document" class="apply-img">
                <h2>Appoint for Lost Document</h2>
                <p>Access and retrieve your personal files securely and conveniently.</p>
                    <a href="appoint_lost.php" class="apply-btn">Appoint</a>
            </div>


            <!-- Box 2 -->
            <div class="apply-box">
                <img src="img/NMG3.png" alt="Apply Insurance" class="apply-img">
                <h2>Apply Insurance</h2>
                <p>Get the coverage you need! Apply for insurance with us—quick, easy, and hassle-free.</p>
                    <a href="register_insurance.php" class="apply-btn">Apply Now</a>
            </div>
        </div>
    </section>

    <!--------------- Footer ------------------>
	<footer>
		<div class="footer-container">
			<p class="para-line white">Copyright NMG Insurance Agency ©2025</p>
		</div>
	</footer>
	<!--------------- Footer ------------------>
    
</body>
</html>
