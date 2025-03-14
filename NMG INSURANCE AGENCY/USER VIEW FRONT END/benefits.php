<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance | NMG Insurance Agency</title>
    <meta name="description" content="Insurance agency website template designed for companies and startups.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/NMG3.png">

    <!-- External CSS link -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/benefits.css">

    <!-- Jquery CDN link -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
     <!-- Navigation -->
     <?php include 'nav.php'; ?>

    <section class="benefits">
        <div class="container">
            <h2 class="heading bold">Insurance</h2>
            <div class="benefits-catalogue">
                <div class="benefits-card">
                    <img src="img/benefits1.jpg" alt="Credit & Insurance" class="featured-image">
                    <h3 class="bold">Third Party Liability (TPL) Insurance</h3>
                    <p class="para-line">Third-Party Liability in car insurance covers damages or injuries caused to another person, vehicle, or property by the insured driver.</a></p>
                </div>
                <div class="benefits-card">
                    <img src="img/insurance3.jpg" alt="Insurance Mistakes" class="featured-image">
                    <h3 class="bold">Third Party Property Damages (TPPD) Insurance</h3>
                    <p class="para-line">Third-Party Property Damage coverage in car insurance pays for the cost of repairing or replacing someone else's property that you damage in an accident</a></p>
                </div>
                <div class="benefits-card">
                    <img src="img/benefits4.jpg" alt="Trends in Insurance" class="featured-image">
                    <h3 class="bold">Own Damage (OD) Insurance</h3>
                    <p class="para-line">Own Damage coverage in car insurance protects the policyholder’s vehicle against loss or damage due to accidents, theft, natural disasters, or vandalism. It specifically covers the insured's car and is typically part of a comprehensive insurance policy but can also be purchased as a standalone cover.</a></p>
                </div>
                <div class="benefits-card">
                    <img src="img/benefits2.jpg" alt="Health Insurance" class="featured-image">
                    <h3 class="bold">Unnamed Personal Accident(UPA) Insurance</h3>
                    <p class="para-line">Coverage in car insurance provides compensation for accidental injuries, disability, or death to passengers in the insured vehicle, without needing to list their names individually on the policy.</a></p>
                </div>
            </div>
        </div>
    </section>
	<!-- ---------------------------benefits ---------------------------- -->



	<!-- ---------------------------- Footer---------------------------- -->
	<footer>
		<div class="footer-container">
			<p class="para-line white">Copyright NMG Insurance Agency ©2025</p>
		</div>
	</footer>
	<!-- ---------------------------- Footer---------------------------- -->



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