<?php
include '../../DB_connection/db.php';
$db = new Database();
$conn = $db->getConnection();

function getSection($conn, $section) {
	$stmt = $conn->prepare("SELECT * FROM about_content WHERE section = ?");
	$stmt->execute([$section]);
	return $stmt->fetch(PDO::FETCH_ASSOC);
}

$future = getSection($conn, 'future');
$vision = getSection($conn, 'vision');
$mission = getSection($conn, 'mission');
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>About Us | NMG Insurance Agency</title>

	<!-- Favicon -->
	<link rel="icon" type="image/png" href="img/NMG3.png">

	<!-- External CSS link -->
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/about.css">


	<!-- Jquery CDN link -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
	<!-- Scroll to top starts -->
	<button id="topBtn">
	  	<ion-icon name="arrow-up-outline"></ion-icon>
	</button>
	<!-- Scroll to top ends -->

	 <!-- Navigation -->
	 <?php include 'nav.php'; ?>



	<!-------------------- Cookie section ------------------->
	<section class="cookies">
		<div class="cookie-container">
			<h2 class="landing-heading white">
				About Us
			</h2>
		</div>
	</section>
	<!-------------------- Breadcrumb section ------------------->



	<!-------------------- Future protection -------------------->
	<section class="future">
		<div class="container">
			<h4 class="sub-heading"></h4>
			<h2 class="heading">Your Future is Protected</h2>
<p class="para-line"><?= nl2br($future['content']) ?></p>
<div class="future-protection-imgs">
	<img src="<?= $future['image1'] ?>" alt="Future protection" class="future-protection-img">
	<img src="<?= $future['image2'] ?>" alt="Future protection" class="future-protection-img">
	<img src="<?= $future['image3'] ?>" alt="Future protection" class="future-protection-img">
</div>

		</div>
	</section>
	<!-------------------- Future protection -------------------->


	<!-- ----------------- Company Values --------------------------- -->
	<section class="values">
		<div class="container">
			<h2 class="heading">
				Company Values
			</h2>

			<div class="company-values">
			<div class="value">
	<img src="img/vision.png" alt="Custom Image" class="value-icon">
	<h4 class="value-heading">Vision</h4>
	<p class="para-line"><?= nl2br($vision['content']) ?></p>
</div>
<div class="value">
	<img src="img/mission.png" alt="Custom Image" class="value-icon">
	<h4 class="value-heading">Mission</h4>
	<p class="para-line"><?= nl2br($mission['content']) ?></p>
</div>

			</div>
		</div>
	</section>
	<!-- ----------------- Company Values --------------------------- -->





	
	<!-- ---------------------------- About CTA --------------------------- -->


	<!--------------- Footer ------------------>
	<footer>
		<div class="footer-container">
			<p class="para-line white">Copyright NMG Insurance Agency ©2025</p>
		</div>
	</footer>
	<!--------------- Footer ------------------>


	<!-------------- Importing JS file -------------->
	<script src="js/script.js"></script>

	
	<script>
		$(document).ready(function() {

    let currentPath = window.location.pathname.split("/").pop();

    if (currentPath === "" || currentPath === "index.php") {
        currentPath = "index.php";
    }

    $(".nav-list li").removeClass("active");

    $(".nav-list li a").each(function() {
        if ($(this).attr("href") === currentPath) {
            $(this).parent().addClass("active");
        }
    });
});

	</script>
</body>
</html>