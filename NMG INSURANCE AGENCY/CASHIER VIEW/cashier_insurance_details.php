<?php
session_start();
$allowed_roles = ['Cashier'];
require('../../Logout_Login/Restricted.php');
require '../../DB_connection/db.php';

// Initialize Database and get PDO connection
$database = new Database();
$conn = $database->getConnection();

// Check and decode insurance_id from URL
if (!isset($_GET['id'])) {
    exit('Missing ID');
}

$encodedId = $_GET['id'];
$insurance_id = base64_decode($encodedId);

// Validate decoded ID (must be digits only)
if (!ctype_digit($insurance_id)) {
    exit('Invalid ID');
}

// Fetch insurance details from DB
$stmt = $conn->prepare("
    SELECT ir.*, CONCAT(c.first_name, ' ', c.last_name) AS fullname, c.contact_number, c.email
    FROM insurance_registration ir
    JOIN clients c ON ir.client_id = c.client_id
    WHERE ir.insurance_id = :id
    LIMIT 1
");
$stmt->execute(['id' => $insurance_id]);
$insurance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$insurance) {
    exit('Insurance record not found');
}

// Check if Paid and Claimed statuses for button visibility
$isPaid = strtolower($insurance['is_paid'] ?? '') === 'paid';
$isClaimed = strtolower($insurance['is_claimed'] ?? '') === 'claimed';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Insurance Details</title>
  <link rel="stylesheet" href="css/dashboard.css" />
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background-color: #f8f9fa;
    }
    .details-container {
      background: white;
      padding: 20px;
      border-radius: 6px;
      max-width: 700px;
      margin: auto;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h1 {
      margin-bottom: 20px;
      color: #333;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    td, th {
      padding: 10px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }
    th {
      background-color: #007bff;
      color: white;
    }
    .back-btn {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      margin-right: 10px;
    }
    .back-btn:hover {
      background-color: #0056b3;
    }
    button.action-btn {
      padding: 8px 15px;
      border: none;
      border-radius: 5px;
      color: white;
      cursor: pointer;
      font-size: 1rem;
    }
    button.paid-btn {
      background-color: royalblue;
      margin-right: 10px;
    }
    button.claimed-btn {
      background-color: darkgreen;
    }
    button.unpaid-btn {
  background-color: #cc0000; /* red */
  margin-right: 10px;
}
button.unclaimed-btn {
  background-color: #cc6600; /* orange-ish */
}

  </style>
</head>
<body>

<div class="details-container">
  <h1>Insurance Details for <?=htmlspecialchars($insurance['fullname'])?></h1>

  <table>
    <tr><th>Insurance Type</th><td><?=htmlspecialchars($insurance['type_of_insurance'])?></td></tr>
    <tr><th>Scheduled Pickup Date</th><td><?=htmlspecialchars($insurance['scheduled_date'] ?? '-')?></td></tr>
    <tr><th>Start Date</th><td><?=htmlspecialchars($insurance['start_date'] ?? '-')?></td></tr>
    <tr><th>Rescheduled Date</th><td><?=htmlspecialchars($insurance['rescheduled_date'] ?? '-')?></td></tr>
    <tr><th>Status</th><td><?=htmlspecialchars($insurance['status'])?></td></tr>
    <tr><th>Claimed</th><td><?=htmlspecialchars($insurance['is_claimed'] ?? 'Unclaimed')?></td></tr>
    <tr><th>Paid</th><td><?=htmlspecialchars($insurance['is_paid'] ?? 'Unpaid')?></td></tr>
    <tr><th>Client Mobile</th><td><?=htmlspecialchars($insurance['contact_number'] ?? '-')?></td></tr>
    <tr><th>Client Email</th><td><?=htmlspecialchars($insurance['email'] ?? '-')?></td></tr>
    <!-- Add more details as needed -->
  </table>

  <!-- Paid toggle -->
<form method="POST" action="../../PHP_Files/CRUD_Functions/cashier_update_status.php"  style="display:inline-block;">
  <input type="hidden" name="insurance_id" value="<?=htmlspecialchars($insurance['insurance_id'])?>">
  <input type="hidden" name="action" value="<?= $isPaid ? 'mark_unpaid' : 'mark_paid' ?>">
  <button type="submit" class="action-btn <?= $isPaid ? 'unpaid-btn' : 'paid-btn' ?>">
    <?= $isPaid ? 'Mark as Unpaid' : 'Mark as Paid' ?>
  </button>
</form>

<!-- Claimed toggle -->
<form method="POST" action="../../PHP_Files/CRUD_Functions/cashier_update_status.php" style="display:inline-block;">
  <input type="hidden" name="insurance_id" value="<?=htmlspecialchars($insurance['insurance_id'])?>">
  <input type="hidden" name="action" value="<?= $isClaimed ? 'mark_unclaimed' : 'mark_claimed' ?>">
  <button type="submit" class="action-btn <?= $isClaimed ? 'unclaimed-btn' : 'claimed-btn' ?>">
    <?= $isClaimed ? 'Mark as Unclaimed' : 'Mark as Claimed' ?>
  </button>
</form>

  <a href="cashier.php" class="back-btn">Back to List</a>
</div>

</body>
</html>
