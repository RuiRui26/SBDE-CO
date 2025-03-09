<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>NMG Insurance Agency</title>

	<!-- Favicon -->
	<link rel="icon" type="image/png" href="img/NMG3.png">


	<!-- External CSS link -->
	<link rel="stylesheet" href="css/style.css">


	<!-- Jquery CDN link -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body class="">
	<button id="topBtn">
	  	<ion-icon name="arrow-up-outline"></ion-icon>
	</button>


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
<<<<<<< HEAD
				<li class="active"><a href="index.html">Home</a></li>
=======
				<li class="active"><a href="index.php">Home</a></li>
>>>>>>> b702d06172db8c044065d55c2b6d1b2a104c2717
				<li><a href="about.php">About</a></li>
				<li><a href="benefits.php">Insurance</a></li>
				<li><a href="contact.php">Contacts</a></li>
			
				<!-- User Profile -->
				<li class="user-profile">
					<a href="client.php" class="profile-link"></a>
				</li>															
			</ul>
			
		</div>
	</nav>
	<!-- Navigation ends -->


	<!-- Landing Page section starts -->
	<section class="landing">
		<div class="landing-container">
			<div class="row">
				<div class="col landing-content">
					<h2 class="landing-heading white">
						Drive with Confidence, Ensure with Trust.
					</h2>
					<p class="para-line white">
					A trusted non-life insurance to ensure the safety coverage of vehicle accidents. To give a bright future ahead within the road
					</p>
					<div class="inner-row">
						<div class="inner-col">
							<button class="btn btn-full-w view-requirement">
								<a href="view_requirements.php">View Requirements</a>
							</button>
						</div>
						<div class="inner-col">
							<button class="btn btn-full-w apply-here">
								<a href="apply_choices.php">Apply Here</a>
							</button>
						</div>
					</div>
				</div>
				<div class="col landing-blank-col"></div>
			</div>
		</div>
	</section>
	<!-- Landing Page section ends -->


	

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