<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMG Insurance Agency</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/NMG3.png">

    <!-- External CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/nav.css">

    <!-- jQuery CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" 
        integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" 
        crossorigin="anonymous" referrerpolicy="no-referrer">
    </script>
</head>

<body>

    <!-- Scroll to Top Button -->
    <button id="topBtn">
        <ion-icon name="arrow-up-outline"></ion-icon>
    </button>

    <!-- Navigation -->
    <?php include 'nav.php'; ?>

    <!-- Landing Page Section -->
    <section class="landing">
        <div class="landing-container">
            <div class="row">
                <div class="col landing-content">
                    <h2 class="landing-heading white">
                        Drive with Confidence, Ensure with Trust.
                    </h2>
                    <p class="para-line white">
                        A trusted non-life insurance to ensure the safety coverage of vehicle accidents.
                        To give a bright future ahead within the road.
                    </p>
                    <div class="inner-row">
                        <div class="inner-col">
                            <button class="btn btn-full-w view-requirement" onclick="location.href='view_requirements.php'">
                                View Requirements
                            </button>
                        </div>
                        <div class="inner-col">
                            <button class="btn btn-full-w apply-here" onclick="checkLoginStatus()">
                                Apply Here
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col landing-blank-col"></div>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p>You need to be logged in to apply.</p>
            <a href="../../Logout_Login_USER/Login.php" class="btn btn-primary">Login</a>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <p class="para-line white">Copyright NMG Insurance Agency ©2025</p>
        </div>
    </footer>

    <!-- CSS for Modal -->
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .close {
            cursor: pointer;
            float: right;
            font-size: 20px;
        }
    </style>

    <!-- JavaScript -->
    <script>
        function checkLoginStatus() {
            // Check if the user is logged in by checking the session
            var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            
            if (isLoggedIn) {
                window.location.href = 'apply_choices.php';  // Redirect to the registration page
            } else {
                document.getElementById('loginModal').style.display = 'flex';  // Show the login modal
            }
        }

        function closeModal() {
            document.getElementById('loginModal').style.display = 'none';
        }

        $(document).ready(function () {
            let currentPath = window.location.pathname.split("/").pop();
            if (currentPath === "" || currentPath === "index.php") {
                currentPath = "index.php";
            }

            $(".nav-list li").removeClass("active");

            $(".nav-list li a").each(function () {
                if ($(this).attr("href") === currentPath) {
                    $(this).parent().addClass("active");
                }
            });
        });
    </script>

</body>

</html>
