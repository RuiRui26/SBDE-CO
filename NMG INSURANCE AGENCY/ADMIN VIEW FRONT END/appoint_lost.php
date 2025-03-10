<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appoint | NMG Insurance Agency</title>

    <link rel="stylesheet" href="css/appoint_loss.css">

    <!-- Favicon -->
    <link rel="icon" type="image2/png" href="img/NMG3.png">
</head>

<body>

    <!-- Header -->
    <div class="header">
        <img src="img2/logo.png" alt="NMG Insurance Logo" class="logo">
        <h1 class="header-title">Appoint for Lost Documents</h1>
    </div>

    <!-- Main Form Section -->
    <div class="form-section">
        <form action="appoint_lost.html" method="POST" enctype="multipart/form-data" class="insurance-form">

            <!-- Left Column -->
            <div class="form-column">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required placeholder="Enter your full name">

                <label for="coc">Certificate of Coverage (COC):</label>
                <input type="text" id="coc" name="coc" required placeholder="Enter your COC number">
            </div>


            <!-- Submit Button -->
            <div class="submit-container">
                <button type="submit" class="submit-btn">Appoint Lost Document</button>
            </div>

        </form>
    </div>

    <!-- Footer -->
    <footer>
        <p>Copyright NMG Insurance Agency ©2025</p>
    </footer>

</body>

</html>
