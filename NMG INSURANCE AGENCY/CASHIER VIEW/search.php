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
  <title>Lost Document Applications</title>
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
    .badge.processing { background-color: #FFC107; }
    .badge.claimed { background-color: darkgreen; }

    table.dataTable tbody tr:hover {
      background-color: #f3f3f3;
    }
    .status-btn {
      padding: 5px 10px;
      border: none;
      background-color: #007bff;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }
    
    .dropdown {
      position: relative;
      display: inline-block;
    }
    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #f9f9f9;
      min-width: 120px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
      z-index: 1;
      border-radius: 4px;
    }
    .dropdown-content a {
      color: black;
      padding: 8px 12px;
      text-decoration: none;
      display: block;
    }
    .dropdown-content a:hover {
      background-color: #f1f1f1;
    }
    .dropdown:hover .dropdown-content {
      display: block;
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
  <h1 class="activity-title">Lost Document Applications</h1>
  <div class="table-container">
    <table id="lostDocumentsTable" class="display">
      <thead>
        <tr>
          <th>First Name</th>
          <th>Last Name</th>
          <th>COC Number</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $conn->query("
          SELECT 
            ld.lost_document_id,
            ld.certificate_of_coverage,
            ld.status,
            u.first_name,
            u.last_name
          FROM lost_documents ld
          JOIN clients c ON ld.client_id = c.client_id
          JOIN users u ON c.user_id = u.user_id
          WHERE ld.status IN ('Pending', 'Processing', 'Approved', 'Claimed')
          ORDER BY ld.application_date DESC
        ");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $statusClass = strtolower($row['status']);
          $encodedId = base64_encode($row['lost_document_id']);

          echo "<tr data-id='{$row['lost_document_id']}'>";
          echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
          echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
          echo "<td>" . htmlspecialchars($row['certificate_of_coverage']) . "</td>";
          echo "<td class='status-cell'><span class='badge {$statusClass}'>" . htmlspecialchars($row['status']) . "</span></td>";
          echo "<td class='action-cell'>";
          
          if ($row['status'] === 'Claimed') {
            echo "<span class='badge claimed'>Claimed</span>";
          } else {
            echo "<div class='dropdown'>";
            echo "<button class='status-btn'>Actions ▼</button>";
            echo "<div class='dropdown-content'>";
            echo "<a href='#' onclick=\"updateStatus(event, '{$encodedId}', 'Processing')\">Mark as Processing</a>";
            echo "<a href='#' onclick=\"updateStatus(event, '{$encodedId}', 'Approved')\">Approve</a>";
            echo "<a href='#' onclick=\"updateStatus(event, '{$encodedId}', 'Claimed')\">Mark as Claimed</a>";
            echo "</div></div>";
          }
          
          echo "</td>";
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
    $('#lostDocumentsTable').DataTable({
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

  function updateStatus(event, encodedId, newStatus) {
    event.preventDefault();
    if (!confirm('Are you sure you want to change status to ' + newStatus + '?')) return;
    
    $.post('update_lost_status.php', {
      id: encodedId,
      status: newStatus,
      action: 'update_status'
    }, function(response) {
      try {
        var result = JSON.parse(response);
        if (result.success) {
          // Update the UI immediately
          var row = $('tr[data-id="' + atob(encodedId) + '"]');
          var statusCell = row.find('.status-cell span');
          
          // Update status text and class
          statusCell.text(newStatus);
          statusCell.removeClass().addClass('badge ' + newStatus.toLowerCase());
          
          // If status is Claimed, replace dropdown with status badge
          if (newStatus === 'Claimed') {
            row.find('.action-cell').html('<span class="badge claimed">Claimed</span>');
          }
          
          // Show success message
          alert('Status updated successfully to ' + newStatus);
        } else {
          alert('Error: ' + result.message);
        }
      } catch(e) {
        alert('Error processing response');
        console.error('Error:', e, 'Response:', response);
      }
    }).fail(function() {
      alert('Failed to update status. Please try again.');
    });
  }
</script>

</body>
</html>