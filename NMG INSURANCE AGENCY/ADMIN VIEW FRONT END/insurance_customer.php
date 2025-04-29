<?php
require_once '../../DB_connection/db.php';

$db = new Database();
$conn = $db->getConnection();

// Fetch data including client and proxy details
$query = "
    SELECT 
        ir.insurance_id,
        c.client_id,
        c.first_name,
        c.last_name,
        c.middle_name,
        c.birthday,
        c.contact_number,
        c.email,
        c.street_address,
        c.barangay,
        c.city,
        c.zip_code,
        v.vehicle_id,
        v.plate_number,
        v.mv_file_number,
        v.vehicle_type,
        v.chassis_number,
        v.brand,
        v.model,
        v.year,
        v.color,
        ir.type_of_insurance,
        ir.created_at AS applied_date,
        ir.status,
        p.proxy_id,
        p.first_name AS proxy_first_name,
        p.middle_name AS proxy_middle_name,
        p.last_name AS proxy_last_name,
        p.birthday AS proxy_birthday,
        p.relationship,
        p.other_relationship,
        p.contact_number AS proxy_contact_number
    FROM insurance_registration ir
    INNER JOIN clients c ON c.client_id = ir.client_id
    INNER JOIN vehicles v ON v.vehicle_id = ir.vehicle_id
    LEFT JOIN proxies p ON p.proxy_id = ir.proxy_id
    ORDER BY ir.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Insurance Applications</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/dashboard.css" />
    <link rel="stylesheet" href="css/insurance_customers.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        #applicationsTable thead th {
            background-color: #023451 !important;
            color: #fff !important;
            font-weight: bold !important;
            font-size: 16px !important;
            border-bottom: none !important;
        }

        #applicationsTable tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        #applicationsTable {
            width: 100% !important;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(2, 52, 81, 0.3);
            overflow: hidden;
        }

        #applicationsTable th,
        #applicationsTable td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
            font-size: 15px;
        }

        .dataTables_filter {
            float: none !important;
            text-align: left !important;
            margin-bottom: 15px;
        }
        
        .dataTables_filter input {
            width: 100% !important;
            max-width: 400px;
            padding: 8px 15px !important;
            border-radius: 8px !important;
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }

        .client-details-btn, .vehicle-details-btn {
            background: none;
            border: none;
            color: #023451;
            text-decoration: underline;
            cursor: pointer;
            padding: 0;
        }

        .detail-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .detail-table th {
            width: 30%;
            text-align: left;
            padding: 8px;
            vertical-align: top;
        }
        .detail-table td {
            padding: 8px;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="content">
        <h2>Insurance Applications</h2>

        <table id="applicationsTable" class="display table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Vehicle</th>
                    <th>Transaction Type</th>
                    <th>Applied Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td data-label="Client">
                            <button class="client-details-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#clientDetailsModal"
                                data-first_name="<?= htmlspecialchars($row['first_name']) ?>"
                                data-last_name="<?= htmlspecialchars($row['last_name']) ?>"
                                data-middle_name="<?= htmlspecialchars($row['middle_name']) ?>"
                                data-birthday="<?= $row['birthday'] ?>"
                                data-contact="<?= htmlspecialchars($row['contact_number']) ?>"
                                data-email="<?= htmlspecialchars($row['email']) ?>"
                                data-address="<?= htmlspecialchars($row['street_address'] . ', ' . $row['barangay'] . ', ' . $row['city'] . ', ' . $row['zip_code']) ?>"
                                data-proxy_id="<?= $row['proxy_id'] ?>"
                                data-proxy_first_name="<?= htmlspecialchars($row['proxy_first_name'] ?? '') ?>"
                                data-proxy_middle_name="<?= htmlspecialchars($row['proxy_middle_name'] ?? '') ?>"
                                data-proxy_last_name="<?= htmlspecialchars($row['proxy_last_name'] ?? '') ?>"
                                data-proxy_birthday="<?= $row['proxy_birthday'] ?? '' ?>"
                                data-proxy_relationship="<?= htmlspecialchars($row['relationship'] ?? '') ?>"
                                data-proxy_other_relationship="<?= htmlspecialchars($row['other_relationship'] ?? '') ?>"
                                data-proxy_contact="<?= htmlspecialchars($row['proxy_contact_number'] ?? '') ?>"
                            >
                                <?= htmlspecialchars($row['last_name']) ?>, <?= htmlspecialchars($row['first_name']) ?>
                            </button>
                        </td>
                        <td data-label="Vehicle">
                            <button class="vehicle-details-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#vehicleDetailsModal"
                                data-plate_number="<?= htmlspecialchars($row['plate_number'] ?? '') ?>"
                                data-mv_file_number="<?= htmlspecialchars($row['mv_file_number'] ?? '') ?>"
                                data-vehicle_type="<?= htmlspecialchars($row['vehicle_type']) ?>"
                                data-chassis_number="<?= htmlspecialchars($row['chassis_number']) ?>"
                                data-brand="<?= htmlspecialchars($row['brand']) ?>"
                                data-model="<?= htmlspecialchars($row['model']) ?>"
                                data-year="<?= htmlspecialchars($row['year']) ?>"
                                data-color="<?= htmlspecialchars($row['color']) ?>"
                            >
                                <?php if (!empty($row['plate_number'])): ?>
                                    <?= htmlspecialchars($row['plate_number']) ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($row['mv_file_number']) ?>
                                <?php endif; ?>
                                <br>
                                <small><?= htmlspecialchars($row['brand']) ?> <?= htmlspecialchars($row['model']) ?></small>
                            </button>
                        </td>
                        <td data-label="Transaction Type"><?= htmlspecialchars($row['type_of_insurance']) ?></td>
                        <td data-label="Applied Date"><?= htmlspecialchars(date('M d, Y', strtotime($row['applied_date']))) ?></td>
                        <td data-label="Status">
                            <span class="badge 
                                <?= $row['status'] == 'Approved' ? 'bg-success' : 
                                   ($row['status'] == 'Rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td data-label="Action">
                            <a href="insurance_details.php?id=<?= $row['insurance_id'] ?>" class="btn btn-primary btn-sm mb-1">View</a>
                            <button 
                                class="btn btn-warning btn-sm mb-1 editBtn"
                                data-insurance_id="<?= $row['insurance_id'] ?>"
                                data-client_id="<?= $row['client_id'] ?>"
                                data-plate_number="<?= htmlspecialchars($row['plate_number']) ?>"
                                data-mv_file_number="<?= htmlspecialchars($row['mv_file_number']) ?>"
                                data-chassis_number="<?= htmlspecialchars($row['chassis_number']) ?>"
                                data-brand="<?= htmlspecialchars($row['brand']) ?>"
                                data-model="<?= htmlspecialchars($row['model']) ?>"
                                data-color="<?= htmlspecialchars($row['color']) ?>"
                                data-type_of_insurance="<?= htmlspecialchars($row['type_of_insurance']) ?>"
                                data-status="<?= htmlspecialchars($row['status']) ?>"
                                data-bs-toggle="modal" 
                                data-bs-target="#editModal"
                            >
                                Edit
                            </button>
                            <button 
                                class="btn btn-danger btn-sm mb-1 deleteBtn" 
                                data-id="<?= $row['insurance_id'] ?>" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <!-- Client Details Modal -->
    <div class="modal fade" id="clientDetailsModal" tabindex="-1" aria-labelledby="clientDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="clientDetailsModalLabel">Client Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <h6>Client Information</h6>
            <table class="detail-table">
                <tr>
                    <th>Full Name:</th>
                    <td id="client_full_name"></td>
                </tr>
                <tr>
                    <th>Birthday:</th>
                    <td id="client_birthday"></td>
                </tr>
                <tr>
                    <th>Contact Number:</th>
                    <td id="client_contact"></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td id="client_email"></td>
                </tr>
                <tr>
                    <th>Address:</th>
                    <td id="client_address"></td>
                </tr>
            </table>

            <h6 id="proxyHeader" style="display: none;">Proxy Information</h6>
            <table class="detail-table" id="proxyDetailsTable" style="display: none;">
                <tr>
                    <th>Full Name:</th>
                    <td id="proxy_full_name"></td>
                </tr>
                <tr>
                    <th>Birthday:</th>
                    <td id="proxy_birthday"></td>
                </tr>
                <tr>
                    <th>Relationship:</th>
                    <td id="proxy_relationship"></td>
                </tr>
                <tr>
                    <th>Contact Number:</th>
                    <td id="proxy_contact"></td>
                </tr>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Vehicle Details Modal -->
    <div class="modal fade" id="vehicleDetailsModal" tabindex="-1" aria-labelledby="vehicleDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="vehicleDetailsModalLabel">Vehicle Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <h6>Vehicle Information</h6>
            <table class="detail-table">
                <tr>
                    <th>Plate Number:</th>
                    <td id="vehicle_plate_number"></td>
                </tr>
                <tr>
                    <th>MV File Number:</th>
                    <td id="vehicle_mv_file_number"></td>
                </tr>
                <tr>
                    <th>Vehicle Type:</th>
                    <td id="vehicle_type"></td>
                </tr>
                <tr>
                    <th>Chassis Number:</th>
                    <td id="vehicle_chassis_number"></td>
                </tr>
                <tr>
                    <th>Brand:</th>
                    <td id="vehicle_brand"></td>
                </tr>
                <tr>
                    <th>Model:</th>
                    <td id="vehicle_model"></td>
                </tr>
                <tr>
                    <th>Year:</th>
                    <td id="vehicle_year"></td>
                </tr>
                <tr>
                    <th>Color:</th>
                    <td id="vehicle_color"></td>
                </tr>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form id="editForm" method="POST" action="edit_insurance.php">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editModalLabel">Edit Insurance Application</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="insurance_id" id="edit_insurance_id" />
              <input type="hidden" name="client_id" id="edit_client_id" />

              <div class="row mb-3">
                <div class="col-md-3">
                  <label for="edit_mv_file_number" class="form-label">MV File Number</label>
                  <input type="text" class="form-control" id="edit_mv_file_number" name="mv_file_number" />
                </div>
                <div class="col-md-3">
                  <label for="edit_plate_number" class="form-label">Plate Number</label>
                  <input type="text" class="form-control" id="edit_plate_number" name="plate_number" />
                </div>
                <div class="col-md-3">
                  <label for="edit_chassis_number" class="form-label">Chassis Number</label>
                  <input type="text" class="form-control" id="edit_chassis_number" name="chassis_number" />
                </div>
                <div class="col-md-3">
                  <label for="edit_brand" class="form-label">Brand</label>
                  <input type="text" class="form-control" id="edit_brand" name="brand" />
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-3">
                  <label for="edit_model" class="form-label">Model</label>
                  <input type="text" class="form-control" id="edit_model" name="model" />
                </div>
                <div class="col-md-3">
                  <label for="edit_color" class="form-label">Color</label>
                  <input type="text" class="form-control" id="edit_color" name="color" />
                </div>
                <div class="col-md-3">
                  <label for="edit_type_of_insurance" class="form-label">Transaction Type</label>
                  <select name="type_of_insurance" id="edit_type_of_insurance" class="form-select" required>
                    <option value="TPL">TPL</option>
                    <option value="TPPD">TPPD</option>
                    <option value="OD">OD</option>
                    <option value="UPA">UPA</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label for="edit_status" class="form-label">Status</label>
                  <select name="status" id="edit_status" class="form-select" required>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Save Changes</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form id="deleteForm" method="POST" action="delete_insurance.php">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to delete this insurance application?</p>
              <input type="hidden" name="insurance_id" id="delete_insurance_id" />
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-danger">Delete</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      $(document).ready(function () {
          // Initialize DataTables
          $('#applicationsTable').DataTable({
              dom: '<"top"f>rt<"bottom"lip><"clear">',
              pageLength: 10,
              lengthMenu: [10, 25, 50, 100],
              responsive: true
          });

          // Client details button handler
          $('.client-details-btn').on('click', function () {
              const btn = $(this);
              
              // Set client details
              const middleInitial = btn.data('middle_name') ? btn.data('middle_name').charAt(0) + '.' : '';
              $('#client_full_name').text(btn.data('first_name') + ' ' + middleInitial + ' ' + btn.data('last_name'));
              $('#client_birthday').text(btn.data('birthday') || 'N/A');
              $('#client_contact').text(btn.data('contact') || 'N/A');
              $('#client_email').text(btn.data('email') || 'N/A');
              $('#client_address').text(btn.data('address') || 'N/A');
              
              // Check if proxy exists
              if (btn.data('proxy_id')) {
                  $('#proxyHeader').show();
                  $('#proxyDetailsTable').show();
                  
                  // Set proxy details
                  const proxyMiddleInitial = btn.data('proxy_middle_name') ? btn.data('proxy_middle_name').charAt(0) + '.' : '';
                  $('#proxy_full_name').text(btn.data('proxy_first_name') + ' ' + proxyMiddleInitial + ' ' + btn.data('proxy_last_name'));
                  $('#proxy_birthday').text(btn.data('proxy_birthday') || 'N/A');
                  
                  let relationship = btn.data('proxy_relationship');
                  if (relationship === 'Other' && btn.data('proxy_other_relationship')) {
                      relationship = btn.data('proxy_other_relationship');
                  }
                  $('#proxy_relationship').text(relationship || 'N/A');
                  
                  $('#proxy_contact').text(btn.data('proxy_contact') || 'N/A');
              } else {
                  $('#proxyHeader').hide();
                  $('#proxyDetailsTable').hide();
              }
          });

          // Vehicle details button handler
          $('.vehicle-details-btn').on('click', function () {
              const btn = $(this);
              
              // Set vehicle details
              $('#vehicle_plate_number').text(btn.data('plate_number') || 'N/A');
              $('#vehicle_mv_file_number').text(btn.data('mv_file_number') || 'N/A');
              $('#vehicle_type').text(btn.data('vehicle_type') || 'N/A');
              $('#vehicle_chassis_number').text(btn.data('chassis_number') || 'N/A');
              $('#vehicle_brand').text(btn.data('brand') || 'N/A');
              $('#vehicle_model').text(btn.data('model') || 'N/A');
              $('#vehicle_year').text(btn.data('year') || 'N/A');
              $('#vehicle_color').text(btn.data('color') || 'N/A');
          });

          // Edit button handler
          $('.editBtn').on('click', function () {
              const btn = $(this);
              $('#edit_insurance_id').val(btn.data('insurance_id'));
              $('#edit_client_id').val(btn.data('client_id'));
              $('#edit_plate_number').val(btn.data('plate_number'));
              $('#edit_mv_file_number').val(btn.data('mv_file_number'));
              $('#edit_chassis_number').val(btn.data('chassis_number'));
              $('#edit_brand').val(btn.data('brand'));
              $('#edit_model').val(btn.data('model'));
              $('#edit_color').val(btn.data('color'));
              $('#edit_type_of_insurance').val(btn.data('type_of_insurance'));
              $('#edit_status').val(btn.data('status'));
          });

          // Delete button handler
          $('.deleteBtn').on('click', function () {
              $('#delete_insurance_id').val($(this).data('id'));
          });
      });
    </script>
</body>
</html>