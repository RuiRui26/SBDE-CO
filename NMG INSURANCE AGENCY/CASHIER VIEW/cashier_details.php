<?php
session_start(); 

require('../../Logout_Login/Restricted.php');
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details</title>
    <link rel="icon" type="image/png" href="img3/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/cashier_details.css">
</head>

<body>

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>
  
    <div class="main-content">

        <h1 class="page-title">Customer Details</h1>

        <div class="details-section">
            <p><strong>Name:</strong> <span id="customer-name">John Doe</span></p>
            <p><strong>Email:</strong> customer@example.com</p>
            <p><strong>Phone:</strong> +123 456 7890</p>
            <p><strong>Transaction Type:</strong> Third Party Liability Insurance</p>
            <p><strong>Applied Date:</strong> 2024-02-15</p>
            <p><strong>Status:</strong> Pending</p>

            <!-- OR and CR Images -->
            <div class="image-container">
                <div class="image-box">
                    <p><strong>OR Image:</strong></p>
                    <a href="img3/or-example.jpg" target="_blank">
                        <img src="img2/or-example.jpg" alt="OR Image">
                    </a>
                </div>
                <div class="image-box">
                    <p><strong>CR Image:</strong></p>
                    <a href="img3/cr-example.jpg" target="_blank">
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
