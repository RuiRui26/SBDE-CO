<?php 
session_start();
session_unset();
session_destroy();
echo json_encode(["success" => "Logged out successfully."]);
header("Location: ../NMG INSURANCE AGENCY/USER VIEW FRONT END/index.php");
exit();


?>