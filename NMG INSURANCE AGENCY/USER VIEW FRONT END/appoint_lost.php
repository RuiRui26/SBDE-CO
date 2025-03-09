<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appoint | NMG Insurance Agency</title>

    <link rel="stylesheet" href="css/appoint_loss.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/NMG3.png">
</head>

<body>

    <div class="container">
        <h2>Retrieve your Lost  Documents</h2>
        
        <form action="appoint_lost.php" method="POST" enctype="multipart/form-data">
            
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>

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
