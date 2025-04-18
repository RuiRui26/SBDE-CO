<?php
include '../../DB_connection/db.php';

// Get the database connection using PDO
$database = new Database();
$conn = $database->getConnection();

// Fetch content from the database using PDO
$query = $conn->query("SELECT * FROM contact_content LIMIT 1");
$content = $query->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Contact | NMG Insurance Agency</title>
	
	<!-- Favicon -->
	<link rel="icon" type="image/png" href="img/NMG3.png">

	<!-- External CSS link -->
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/contact.css">

	<!-- Jquery CDN link -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
	<!-- Scroll to top starts -->
	<button id="topBtn">
	  	<ion-icon name="arrow-up-outline"></ion-icon>
	</button>
	<!-- Scroll to top ends -->

	<!-- Navigation -->
	<?php include 'nav.php'; ?>

	<!-- ------------------------- Cookie ---------------------- -->
	<section class="cookie">
		<div class="cookie-container">
			<h2 class="landing-heading white">
				Contact Us
			</h2>
		</div>
	</section>
	<!-- ------------------------- Cookie ---------------------- -->

	<!-- ------------------------- map ------------------------------ -->
	<section class="map">
		<div class="container">
			<h2 class="heading">
				<?= htmlspecialchars($content['heading']) ?>
			</h2>

			<p class="para-line">
				<?= nl2br(htmlspecialchars($content['description'])) ?>
			</p>

			<p class="para-line map-location">
            <ion-icon name="pin-outline"></ion-icon> 📍Legionnaire Street, Veterans Avenue, Zamboanga City Philippines.
        </p>

        <!-- Embedded Google Map (hardcoded) -->
        <div class="map-embed">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d247.55549678655865!2d122.0805676467981!3d6.904001088175058!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x325041738e4696a1%3A0xffaef07b81eecbc2!2sFL%20DRIVING%20SCHOOL%20and%20Services%20Inc.!5e0!3m2!1sen!2sph!4v1739327590915!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
		</div>
	</section>
	<!-- ------------------------- map ------------------------------ -->

	<!-- ------------------------------- Contact Info --------------------------- -->
	<section class="contact-info">
		<div class="container">
			<div class="row">
				<div class="col">
					<img src="img/call.png" alt="Support Hotline" class="contact-icon">
					<h4 class="info-name white">Support Hotline</h4>
					<h6 class="info-detail white"><?= htmlspecialchars($content['hotline']) ?></h6>
				</div>
				<div class="col">
					<img src="img/call.png" alt="Contact Info" class="contact-icon">
					<h4 class="info-name white">Contact Info</h4>
					<h6 class="info-detail white"><?= htmlspecialchars($content['contact']) ?></h6>
				</div>
			</div>
		</div>
	</section>
	<!-- ------------------------------- Contact Info --------------------------- -->

	<!-- ---------------------- Footer ------------------ -->
	<footer>
		<div class="footer-container">
			<p class="para-line white">Copyright NMG Insurance Agency ©2025</p>
		</div>
	</footer>
	<!-- ----------------------- Footer ------------------- -->

	<!-- Importing JS -->
	<script src="js/script.js"></script>

	<!-- Scroll to top functionality -->
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
