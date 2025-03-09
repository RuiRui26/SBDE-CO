<?php
session_start();

if (!isset($_SESSION['user_role'])) {
    header("Location: Login.php");
    exit();
}

switch ($_SESSION['user_role']) {
    case 'Admin':
        header("Location: ../NMG Insurance Agency/ADMIN VIEW FRONT END/index.php");
        break;
    case 'Secretary':
        header("Location: ../NMG Insurance Agency/USER VIEW FRONT END/index.php");
        break;
    case 'Staff':
        header("Location: ../NMG Insurance Agency/STAFF VIEW FRONT END/index.php");
        break;
    case 'Agent':
        header("Location: ../NMG Insurance Agency/AGENT VIEW FRONT END/index.php");
        break;
    case 'Cashier':
        header("Location: ../NMG Insurance Agency/CASHIER VIEW FRONT END/index.php");
        break;
    default:
        header("Location: Login.php");
}

exit();
?>
