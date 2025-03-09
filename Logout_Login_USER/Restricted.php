<?php
session_start();
require_once "../DB_connection/db.php";

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

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
    header("Location: ../../Logout_Login_USER/Login.php?error=security");
    exit();
}

// Session timeout (30 min)
$_SESSION['timeout_duration'] = $_SESSION['timeout_duration'] ?? 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['timeout_duration'])) {
    session_unset();
    session_destroy();
    header("Location: ../../Logout_Login_USER/Login.php?error=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

try {
    // Query the database for the user's role
    $stmt = $pdo->prepare("SELECT role FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $_SESSION['user_email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

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
                        window.location.href = "../../index.php"; // Redirect to home page
                    });
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
