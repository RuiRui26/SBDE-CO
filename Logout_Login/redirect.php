<?php
session_start();

if (!isset($_SESSION['user_role'])) {
    header("Location: Login.php");
    exit();
}

// Prevent infinite loop by checking if we're already on the correct page
$current_page = basename($_SERVER['PHP_SELF']);

$redirects = [
    'Admin' => "ADMIN VIEW FRONT END/index.php",
    'Secretary' => "SECRETARY VIEW/index.php",
    'Staff' => "STAFF VIEW/index.php",
    'Agent' => "AGENT VIEW/index.php",
    'Cashier' => "CASHIER VIEW/index.php",
    'Client' => "USER VIEW FRONT END/index.php"
];

// Get the correct redirect path
$role = $_SESSION['user_role'];
$target_page = $redirects[$role] ?? "Login.php";

// Avoid redirecting if already on the correct page
if ($current_page !== basename($target_page)) {
    header("Location: ../NMG Insurance Agency/$target_page");
    exit();
}
?>

