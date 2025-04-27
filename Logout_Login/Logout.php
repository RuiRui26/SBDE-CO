<?php
session_start();

$role = $_SESSION['role'] ?? null; // Get the role before destroying session

session_unset();
session_destroy();

if ($role === 'Client') {
    header("Location: ../NMG INSURANCE AGENCY/USER VIEW FRONT END/index.php");
} else {
    header("Location: Login.php");
}
exit();
?>
