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

	<!-- Navigation starts -->
	<nav>
		<div class="menu-container nav-wrapper">
			<div class="brand">
				<a href="index.php">
					<img src="img/NMG22.png" alt="insurancy-logo" border="0">
				</a>
			</div>

			<div class="hamberger">
				<span></span>
				<span></span>
				<span></span>
			</div>

			<ul class="nav-list">
				<li class="active"><a href="index.html">Home</a></li>
				<li><a href="about.html">About</a></li>
				<li><a href="benefits.html">Insurance</a></li>
				<li><a href="contact.html">Contacts</a></li>
				<li>
					<button class="btn btn-apply-here">
						<a href="apply_choices.html">Apply</a>
					</button>
				</li>
			</ul>
		</div>
	</nav>
	<!-- Navigation ends -->



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
			<h2 class="heading">
				Your Future is Protected
			</h2>
			<p class="para-line">
				NMG Insurance Agency, originally named VJB Insurance, was founded in 1999. The company changed its name to NJM in 2001 following the passing of one of its owners, with Mrs. Nelly M. Gabas taking over management. Under her leadership, NJM earned the loyalty of its staff, maintaining its position in the insurance sector for 26 years, specifically within the Land Transportation Office (LTO) insurance branch. NJM has expanded with branches to better serve customers in need of insurance and vehicle management services. Despite facing competition from other companies, NMG has continued to thrive and reach significant milestones. Recognizing its potential, 
				SDBE Co. expressed interest in contributing to the company's future success.
			</p>

			<div class="future-protection-imgs">
				<img src="img/aboutus1.jpg" alt="Future protection" title="Future protection" class="future-protection-img">
				<img src="img/aboutus3.jpg" alt="Future protection" title="Future protection" class="future-protection-img">
				<img src="img/aboutus2.jpg" alt="Future protection" title="Future protection" class="future-protection-img">
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
					<p class="para-line">
						"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, 
						quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
					</p>
				</div>
				<div class="value">
					<img src="img/mission.png" alt="Custom Image" class="value-icon">
					<h4 class="value-heading">Mission</h4>
					<p class="para-line">
						"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, 
						quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
					</p>
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