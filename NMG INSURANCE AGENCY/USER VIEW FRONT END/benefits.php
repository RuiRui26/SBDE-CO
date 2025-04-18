<?php
include '../../DB_connection/db.php';
$db = new Database();
$conn = $db->getConnection();

$benefits = $conn->query("SELECT * FROM benefits_contents")->fetchAll();
?>
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
                <?php foreach ($benefits as $benefit): ?>
                    <div class="benefits-card">
                        <img src="<?= $benefit['image'] ?>" alt="<?= $benefit['title'] ?>" class="featured-image">
                        <h3 class="bold"><?= $benefit['title'] ?></h3>
                        <p class="para-line"><?= $benefit['description'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

	<!-- --------------------------- Footer ---------------------------- -->
	<footer>
		<div class="footer-container">
			<p class="para-line white">Copyright NMG Insurance Agency ©2025</p>
		</div>
	</footer>

	<script src="js/script.js"></script>
</body>
</html>
