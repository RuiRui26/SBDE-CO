<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Client</title>
    <!-- Favicon -->
    <link rel="icon" type="imag2/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_client.css">
</head>
<body>
    <div class="sidebar">
        <img src="img2/logo.png" alt="Logo" class="logo"> 
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img2/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.php"><img src="img2/adminprofile.png" alt="Admin Icon"> Admin Profile</a></li>
            <li><a href="customer.php"><img src="img2/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="search.php"><img src="img2/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="activitylog.php"><img src="img2/activitylog.png" alt="Activity Icon"> Activity Log</a></li>
            <li><a href="#"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>
    

    <!-- Apply Choices Section -->
    <section class="apply-section">
        <div class="apply-container">
        

            <!-- Box 1 -->
            <div class="apply-box">
                <img src="img2/logo.png" alt="Lost Document" class="apply-img">
                <h2>Lost Document</h2>
                    <a href="appoint_lost.php" class="apply-btn">Add Client</a>
            </div>


            <!-- Box 2 -->
            <div class="apply-box">
                <img src="img2/LTO2.png" alt="LTO Transaction" class="apply-img">
                <h2>LTO Transaction</h2>
                <a href="register_lto.php" class="apply-btn">Add Client</a>
            </div>


            <!-- Box 3 -->
            <div class="apply-box">
                <img src="img2/logo.png" alt="Apply Insurance" class="apply-img">
                <h2>Insurance</h2>
                    <a href="register_insurance.php" class="apply-btn">Add Client</a>
            </div>
        </div>
    </section>
</body>
</html>