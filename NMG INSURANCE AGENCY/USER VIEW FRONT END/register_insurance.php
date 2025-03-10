<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Insurance | NMG Insurance Agency</title>
    <link rel="stylesheet" href="css/register_insurance.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/NMG3.png">
</head>
<body>

    <h1 class="page-title">APPLY INSURANCE</h1> 

    <div class="container">
        <h2>Register Your Insurance</h2>
        
        <form action="register_insurance.html" method="POST" enctype="multipart/form-data">
            
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

            <label for="insurance_type">Type of Insurance:</label>
            <select id="insurance_type" name="insurance_type" required>
            <option value="">Select Insurance Type</option>
            <option value="tpl">Third Party Liability (TPL) Insurance</option>
            <option value="tppd">Third Party Property Damages (TPPD) Insurance</option>
            <option value="od">Own Damage (OD) Insurance</option>
            <option value="upa">Unnamed Personal Accident(UPA) Insurance</option>
            </select>

            <label for="or_picture">Upload OR Picture:</label>
            <input type="file" id="or_picture" name="or_picture" accept="image/*" required>

            <label for="cr_picture">Upload CR Picture:</label>
            <input type="file" id="cr_picture" name="cr_picture" accept="image/*" required>

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
<<<<<<< HEAD

<<<<<<< HEAD
=======
>>>>>>> parent of abc0581 (HELLO ANDY GOODNIGHT)
</html>
=======


<script>
document.addEventListener("DOMContentLoaded", function() {
    const plateInput = document.getElementById("plate_number");
    const mvInput = document.getElementById("mv_file_number");

    function validateFields() {
        if (plateInput.value.trim() === "" && mvInput.value.trim() === "") {
            plateInput.setCustomValidity("Please enter either Plate Number or MV File Number.");
            mvInput.setCustomValidity("Please enter either Plate Number or MV File Number.");
        } else {
            plateInput.setCustomValidity("");
            mvInput.setCustomValidity("");
        }
    }

    plateInput.addEventListener("input", validateFields);
    mvInput.addEventListener("input", validateFields);
});
</script>
</html>
>>>>>>> b702d06172db8c044065d55c2b6d1b2a104c2717
