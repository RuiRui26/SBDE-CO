<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register LTO | NMG Insurance Agency</title>
    <link rel="stylesheet" href="css/register_lto.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/NMG3.png">
</head>
<body>

    <h1 class="page-title">APPLY LTO TRANSACTION</h1> 

    <div class="container">
        <h2>Register Your LTO Transaction</h2>
        
        <form action="register_lto.php" method="POST" enctype="multipart/form-data">
            
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="mobile">Mobile Number:</label>
            <input type="tel" id="mobile" name="mobile" required pattern="[0-9]{11}" placeholder="Enter your 11-digit mobile number" 
            oninvalid="this.setCustomValidity('Please enter exactly 11 digits.')"
            oninput="this.setCustomValidity('')">

            <label for="plate_number">Plate Number:</label>
            <input type="text" id="plate_number" name="plate_number" required>

            <label for="chassis_number">Chassis Number:</label>
            <input type="text" id="chassis_number" name="chassis_number" required>

            <label for="or_picture">Upload OR Picture:</label>
            <input type="file" id="or_picture" name="or_picture" accept="image/*" required>

            <label for="cr_picture">Upload CR Picture:</label>
            <input type="file" id="cr_picture" name="cr_picture" accept="image/*" required>

            <label for="emission_picture">Upload Emission Picture:</label>
            <input type="file" id="emission_picture" name="emission_picture" accept="image/*" required>

            <label for="coc_picture">Upload COC Picture:</label>
            <input type="file" id="coc_picture" name="coc_picture" accept="image/*" required>



            <button type="submit">Submit</button>
        </form>
    </div>

    <!--------------- Footer ------------------>
	<footer>
		<div class="footer-container">
			<p class="para-line white">Copyright NMG Insurance Agency ©2025</p>
		</div>
	</footer>
	<!--------------- Footer ------------------>

</body>
</html>
