<?php
session_start();
include '../../DB_connection/db.php';

$database = new Database();
$conn = $database->getConnection();

function getContent($key, $section) {
    global $conn;
    $stmt = $conn->prepare("SELECT content_value FROM homepage_content WHERE content_key = :key AND section = :section");
    $stmt->bindParam(':key', $key);
    $stmt->bindParam(':section', $section);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['content_value'] : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMG Insurance Agency</title>
    <link rel="icon" type="image/png" href="img/NMG3.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/nav.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>

<button id="topBtn">
    <ion-icon name="arrow-up-outline"></ion-icon>
</button>

<?php include 'nav.php'; ?>

<!-- Landing Page Section -->
<section class="landing">
    <div class="landing-container">
        <div class="row">
            <div class="col landing-content">
                <h2 class="landing-heading white"><?= getContent('heading', 'landing'); ?></h2>
                <p class="para-line white"><?= getContent('paragraph', 'landing'); ?></p>
                <div class="inner-row">
                    <div class="inner-col">
                        <button class="btn btn-full-w view-requirement" onclick="location.href='view_requirements.php'">
                            <?= getContent('view_button', 'landing'); ?>
                        </button>
                    </div>
                    <div class="inner-col">
                        <button class="btn btn-full-w apply-here" onclick="checkLoginStatus()">
                            <?= getContent('apply_button', 'landing'); ?>
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
    <div class="modal-content enhanced-modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img src="img/NMG3.png" alt="Logo" class="modal-logo">
        <h3 class="modal-title">Login Required</h3>
        <p class="modal-text">You need to be logged in to apply for insurance.</p>
        <a href="../../Logout_Login_USER/Login.php" class="btn-login-enhanced">Login Now</a>
    </div>
</div>

<!-- Footer -->
<footer>
    <div class="footer-container">
        <p class="para-line white"><?= getContent('footer_text', 'footer'); ?></p>
    </div>
</footer>

<!-- Modal Style -->
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

<script>
function checkLoginStatus() {
    var isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    if (isLoggedIn) {
        window.location.href = 'apply_choices.php';
    } else {
        document.getElementById('loginModal').style.display = 'flex';
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
