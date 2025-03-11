<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../DB_connection/db.php';

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

// Redirect to login if not logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: /SBDE-CO/Logout_Login_USER/Login.php");
    exit();
}

// Prevent session hijacking
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    header("Location: /SBDE-CO/Logout_Login_USER/Login.php?error=security");
    exit();
}

// Set session timeout (30 minutes)
$timeout_duration = 1800;
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: /SBDE-CO/Logout_Login_USER/Login.php?error=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

try {
    // Query the database for the user's role
    $stmt = $pdo->prepare("SELECT role FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $_SESSION['user_email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the user is a Client
    if (!$user || $user['role'] !== 'Client') {
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Denied</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
            <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="errorModalLabel">Access Denied</h5>
                        </div>
                        <div class="modal-body">
                            You do not have permission to access this page.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="redirectBtn">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var myModal = new bootstrap.Modal(document.getElementById("errorModal"));
                    myModal.show();

                    document.getElementById("redirectBtn").addEventListener("click", function () {
                        window.location.href = "/SBDE-CO/index.php"; // Redirect to home page
                    });

                    // Auto redirect after 5 seconds
                    setTimeout(function() {
                        window.location.href = "/SBDE-CO/index.php";
                    }, 5000);
                });
            </script>
        </body>
        </html>
        ';
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
