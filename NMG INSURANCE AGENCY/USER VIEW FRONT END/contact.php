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
				Get in Touch with us
			</h2>

			<p class="para-line">
			Whether you're looking for the best coverage, need assistance with your policy, or have questions about claims, our team is ready to provide you with expert guidance. Contact us today to learn more about our comprehensive car insurance plans and find the right protection for your vehicle.
			</p>

			<p class="para-line map-location">
				<ion-icon name="pin-outline"></ion-icon> 📍Legionnaire Street, Veterans Avenue, Zamboanga City Philippines.
			</p>

			<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d247.55549678655865!2d122.0805676467981!3d6.904001088175058!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x325041738e4696a1%3A0xffaef07b81eecbc2!2sFL%20DRIVING%20SCHOOL%20and%20Services%20Inc.!5e0!3m2!1sen!2sph!4v1739327590915!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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
					<h6 class="info-detail white">991-3910</h6>
				</div>
				<div class="col">
					<img src="img/call.png" alt="Contact Info" class="contact-icon">
					<h4 class="info-name white">Contact Info</h4>
					<h6 class="info-detail white">0997-566-0532</h6>
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


	<!-------------- Importing JS file -------------->
	<script src="js/script.js"></script>

	
	<!------------------------ Scroll to top button -------------------->
	<script>
		$(document).ready(function() {
    // Get the current URL path
    let currentPath = window.location.pathname.split("/").pop();

    // If no specific page is detected, default to 'index.php'
    if (currentPath === "" || currentPath === "index.php") {
        currentPath = "index.php";
    }

    // Remove 'active' class from all nav items
    $(".nav-list li").removeClass("active");

    // Find the matching link and add 'active' class
    $(".nav-list li a").each(function() {
        if ($(this).attr("href") === currentPath) {
            $(this).parent().addClass("active");
        }
    });
});

	</script>
</body>
</html>