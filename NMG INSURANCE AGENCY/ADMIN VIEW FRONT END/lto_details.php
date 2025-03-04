<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/insurance_details.css">
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

    <div class="main-content">

        <h1 class="page-title">Customer Details</h1>

        <div class="details-section">
            <p><strong>Name:</strong> <span id="customer-name">John Doe</span></p>
            <p><strong>Address:</strong> customer@example.com</p>
            <p><strong>Phone:</strong> +123 456 7890</p>
            <p><strong>Plate Number:</strong> Third Party Liability Insurance</p>
            <p><strong>Chasis Number</strong> T1923891</p>
            <p><strong>Applied Date:</strong> 2024-02-15</p>
            <p><strong>Status:</strong> Pending</p>

            <!-- OR and CR Images -->
            <div class="image-container">
                <div class="image-box">
                    <p><strong>OR Image:</strong></p>
                    <a href="img2/or-example.jpg" target="_blank">
                        <img src="img2/or-example.jpg" alt="OR Image">
                    </a>
                </div>
                <div class="image-box">
                    <p><strong>CR Image:</strong></p>
                    <a href="img2/cr-example.jpg" target="_blank">
                        <img src="img2/cr-example.jpg" alt="CR Image">
                    </a>
                </div>
                <div class="image-box">
                    <p><strong>Emission Image:</strong></p>
                    <a href="img2/cr-example.jpg" target="_blank">
                        <img src="img2/cr-example.jpg" alt="CR Image">
                    </a>
                </div>
                <div class="image-box">
                    <p><strong>COC (Certificate of Coverage) Image:</strong></p>
                    <a href="img2/cr-example.jpg" target="_blank">
                        <img src="img2/cr-example.jpg" alt="CR Image">
                    </a>
                </div>
            </div>
        </div>

        <div class="buttons">
            <button class="accept-btn" onclick="handleDecision('Accepted')">Accept</button>
            <button class="decline-btn" onclick="handleDecision('Declined')">Decline</button>
        </div>

    </div>

    <!-- JavaScript -->
    <script>
        
        const params = new URLSearchParams(window.location.search);
        const customerName = params.get('name') || 'Unknown';
        document.getElementById('customer-name').textContent = customerName;

        function handleDecision(status) {
            alert(`Customer has been ${status}`);
            window.location.href = 'customer.php';
        }
    </script>

</body>

</html>
