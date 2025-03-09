<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/NMG3.png">

    <!-- External CSS link -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/client.css">
</head>

<body>
    <!-- Navigation starts -->
    <nav>
        <div class="menu-container nav-wrapper">
            <div class="brand">
                <a href="index.php">
                    <img src="img/NMG22.png" alt="insurancy-logo" border="0">
                </a>
            </div>

            <ul class="nav-list">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="benefits.php">Insurance</a></li>
                <li><a href="contact.php">Contacts</a></li>

                <!-- User Profile -->
                <li class="user-profile">
                    <a href="client.php">
                        <img src="img/userprofile.png" alt="User Profile">
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <!-- Navigation ends -->

    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-picture">
                <img src="img/userprofile.png" alt="Profile Picture">
            </div>

            <div class="user-info">
                <h2>John Doe</h2>
                <p>Email: john.doe@example.com</p>
                <p>Phone: +123 456 7890</p>
                <p>Address: 123 Main Street, City, Country</p>

                <!-- Logout Button -->
                <button class="logout-btn" onclick="logout()">Logout</button>

                <!-- Send Message Button -->
                <button class="send-message-btn" onclick="sendmessage()">Send Message</button>
            </div>
        </div>

        <div class="content-wrapper">
            <!-- Activity History -->
            <div class="activity-history">
                <h3>Activity History</h3>
                <ul>
                    <li>
                        <span class="step completed">🟢</span>
                        Application Sent <span class="date">March 5, 2025</span>
                    </li>
                    <li>
                        <span class="step completed">🟢</span>
                        Waiting for Admin Approval <span class="date">March 6, 2025</span>
                    </li>
                    <li>
                        <span class="step ongoing">🟠</span>
                        Policy Under Review
                    </li>
                    <li>
                        <span class="step pending">⚪</span>
                        Final Approval Pending
                    </li>
                </ul>
            </div>

            <!-- Live Transaction Status -->
            <div class="transaction-status">
                <h3>Live Transaction Status</h3>
                <div class="status-flow">
                    <div class="status-item completed">
                        <div class="circle">1</div>
                        <p>Application Received</p>
                    </div>
                    <div class="arrow completed"></div>

                    <div class="status-item completed">
                        <div class="circle">2</div>
                        <p>Document Verification</p>
                    </div>
                    <div class="arrow completed"></div>

                    <div class="status-item ongoing">
                        <div class="circle">3</div>
                        <p>Approval Process</p>
                    </div>
                    <div class="arrow"></div>

                    <div class="status-item pending">
                        <div class="circle">4</div>
                        <p>Payment</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function logout() {
            // Redirect to the logout page or handle logout logic
            window.location.href = 'logout.html';
        }
        function sendmessage() {
            // Redirect to the logout page or handle logout logic
            window.location.href = 'message.html';
        }

    </script>

</body>

</html>
