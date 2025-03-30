<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Insurance Date | NMG Insurance Agency</title>
    <link rel="stylesheet" href="css/register_insurance.css">
</head>
<body>

    <header class="header">
        <img src="img/NMG3.png" alt="NMG Insurance Logo" class="logo">
        <h1 class="header-title">Apply for Insurance</h1>
    </header>

    <main class="form-section">
        <form id="insuranceForm" action="../../PHP_Files/User_View/register_insurance.php" method="POST" enctype="multipart/form-data" class="insurance-form" onsubmit="return validateForm()">
            
            <!-- Input Group (Date Selection) -->
            <div class="input-group">
                <label for="insurance_date">Select Insurance Start Date:</label>
                <input type="date" id="insurance_date" name="insurance_date" required>
            </div>

            <!-- Submit Button Positioned Below Date Input -->
            <div class="submit-container">
                <button type="submit" class="submit-btn">Submit</button>
            </div>

        </form>
    </main>

    <footer>
        <p>© 2025 NMG Insurance Agency. All Rights Reserved.</p>
    </footer>

</body>
</html>
