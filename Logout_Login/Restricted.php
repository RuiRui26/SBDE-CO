<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set allowed roles if not already defined
if (!isset($allowed_roles)) {
    $allowed_roles = [];
}

// Redirect to login if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: /SBDE-CO/Logout_Login/Login.php");
    exit();
}

if (!isset($_SESSION['session_token'])) {
    $_SESSION['session_token'] = bin2hex(random_bytes(32)); // Strong session token
}

// Prevent session hijacking
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'] || $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']){
    session_unset();
    session_destroy();
    header("Location: /SBDE-CO/Logout_Login/Login.php?error=security");
    exit();
}

// Set session timeout (30 minutes)
$timeout_duration = 1800;

if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

// Calculate remaining time
$time_since_last_activity = time() - $_SESSION['last_activity'];
$time_remaining = $timeout_duration - $time_since_last_activity;

if ($time_remaining <= 0) {
    session_unset();
    session_destroy();
    header("Location: /SBDE-CO/Logout_Login/Login.php?error=timeout");
    exit();
}

// Check role access
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
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
                    window.location.href = "/SBDE-CO/Logout_Login/Login.php";
                });

                // Auto redirect after 5 seconds if no action is taken
                setTimeout(function() {
                    window.location.href = "/SBDE-CO/Logout_Login/Login.php";
                }, 5000);
            });
        </script>
    </body>
    </html>';
    
    // **Remove PHP exit() here to let HTML load**
    die(); 
}
?>
