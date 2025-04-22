<?php
session_start();
$allowed_roles = ['Cashier'];
require('../../Logout_Login/Restricted.php');
require '../../DB_connection/db.php';

// Initialize Database and get PDO connection
$database = new Database();
$conn = $database->getConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Insurance Transactions</title>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <link rel="stylesheet" href="css/customer_table.css" />
  <style>
    .badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
      color: white;
      display: inline-block;
    }
    .badge.pending { background-color: orange; }
    .badge.approved { background-color: green; }
    .badge.rejected { background-color: red; }
    .badge.unpaid { background-color: crimson; }
    .badge.paid { background-color: royalblue; }
    .badge.claimed { background-color: darkgreen; }
    .badge.unclaimed { background-color: brown; }

    table.dataTable tbody tr:hover {
      background-color: #f3f3f3;
    }
    .view-btn {
      padding: 5px 10px;
      border: none;
      background-color: #007bff;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }

    /* Modal Styles */
    .modal {
      display: none; 
      position: fixed; 
      z-index: 1000; 
      padding-top: 100px; 
      left: 0; top: 0; width: 100%; height: 100%;
      overflow: auto; 
      background-color: rgba(0,0,0,0.4); 
    }
    .modal-content {
      background-color: #fefefe;
      margin: auto; padding: 20px;
      border: 1px solid #888;
      width: 80%; max-width: 400px;
      border-radius: 8px;
      text-align: center;
      font-family: Arial, sans-serif;
    }
    .close-btn {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 15px;
      font-size: 16px;
    }
    .close-btn:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <h1 class="activity-title">Insurance Invoice Sales</h1>
  <div class="table-container">
    <table id="insuranceTable" class="display">
      <thead>
        <tr>
          <th>Name</th>
          <th>Insurance Type</th>
          <th>Scheduled Date</th>
          <th>Start Date</th>
          <th>Rescheduled Date</th>
          <th>Status</th>
          <th>Claimed</th>
          <th>Paid</th>
          <th>View</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $conn->query("
          SELECT ir.*, CONCAT(c.first_name, ' ', c.last_name) AS fullname
          FROM insurance_registration ir
          JOIN clients c ON ir.client_id = c.client_id
          WHERE ir.status = 'Approved'
          ORDER BY ir.start_date DESC
        ");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $statusClass = strtolower($row['status']);
          $claimedClass = strtolower($row['is_claimed']);
          $paidClass = strtolower($row['is_paid']);

          $claimedText = !empty($row['is_claimed']) ? $row['is_claimed'] : 'Unclaimed';
          $paidText = !empty($row['is_paid']) ? $row['is_paid'] : 'Unpaid';

          // Base64 encode the insurance_id to hide raw ID in URL
          $encodedId = base64_encode($row['insurance_id']);

          echo "<tr>";
          echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
          echo "<td>" . htmlspecialchars($row['type_of_insurance']) . "</td>";
          echo "<td>" . (!empty($row['scheduled_date']) ? htmlspecialchars($row['scheduled_date']) : '-') . "</td>";
          echo "<td>" . (!empty($row['start_date']) ? htmlspecialchars($row['start_date']) : '-') . "</td>";
          echo "<td>" . (!empty($row['rescheduled_date']) ? htmlspecialchars($row['rescheduled_date']) : '-') . "</td>";
          echo "<td><span class='badge {$statusClass}'>" . htmlspecialchars($row['status']) . "</span></td>";
          echo "<td><span class='badge {$claimedClass}'>" . htmlspecialchars($claimedText) . "</span></td>";
          echo "<td><span class='badge {$paidClass}'>" . htmlspecialchars($paidText) . "</span></td>";
          echo "<td><button class='view-btn' onclick=\"viewCustomer('{$encodedId}')\">View</button></td>";
          echo "</tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
  <div id="successModal" class="modal">
    <div class="modal-content">
      <p><?= htmlspecialchars($_SESSION['success_message']) ?></p>
      <button class="close-btn" onclick="closeModal()">OK</button>
    </div>
  </div>
  <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function () {
    $('#insuranceTable').DataTable({
      responsive: true,
      pageLength: 10
    });

    // Show modal if exists
    var modal = document.getElementById('successModal');
    if (modal) {
      modal.style.display = 'block';
    }
  });

  function closeModal() {
    var modal = document.getElementById('successModal');
    if (modal) {
      modal.style.display = 'none';
    }
  }

  function viewCustomer(encodedId) {
    window.location.href = 'cashier_insurance_details.php?id=' + encodeURIComponent(encodedId);
  }
</script>

</body>
</html>
