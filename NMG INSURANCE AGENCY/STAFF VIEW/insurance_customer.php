<?php
require_once '../../DB_connection/db.php'; // Adjust path if needed
$allowed_roles = ['Staff'];
$db = new Database();
$conn = $db->getConnection();


// Fetch data including brand, model, color for vehicles
$query = "
    SELECT 
        ir.insurance_id,
        c.client_id,
        c.first_name,
        c.last_name,
        c.middle_name,
        c.birthday,
        v.plate_number,
        v.mv_file_number,
        v.chassis_number,
        v.brand,
        v.model,
        v.color,
        ir.type_of_insurance,
        ir.created_at AS applied_date,
        ir.status
    FROM insurance_registration ir
    INNER JOIN clients c ON c.client_id = ir.client_id
    INNER JOIN vehicles v ON v.vehicle_id = ir.vehicle_id
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
    <title>Insurance Applications - Edit Customer Details</title>

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

        /* Custom search box styling */
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
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Middle Name</th>
                    <th>Birthday</th>
                    <th>Plate Number</th>
                    <th>MV File Number</th>
                    <th>Chassis Number</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Color</th>
                    <th>Transaction Type</th>
                    <th>Applied Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td data-label="First Name"><?= htmlspecialchars($row['first_name']) ?></td>
                        <td data-label="Last Name"><?= htmlspecialchars($row['last_name']) ?></td>
                        <td data-label="Middle Name"><?= htmlspecialchars($row['middle_name']) ?></td>
                        <td data-label="Birthday"><?= htmlspecialchars($row['birthday']) ?></td>
                        <td data-label="Plate Number"><?= htmlspecialchars($row['plate_number']) ?></td>
                        <td data-label="MV File Number"><?= htmlspecialchars($row['mv_file_number']) ?></td>
                        <td data-label="Chassis Number"><?= htmlspecialchars($row['chassis_number']) ?></td>
                        <td data-label="Brand"><?= htmlspecialchars($row['brand']) ?></td>
                        <td data-label="Model"><?= htmlspecialchars($row['model']) ?></td>
                        <td data-label="Color"><?= htmlspecialchars($row['color']) ?></td>
                        <td data-label="Transaction Type"><?= htmlspecialchars($row['type_of_insurance']) ?></td>
                        <td data-label="Applied Date"><?= htmlspecialchars(date('M d, Y', strtotime($row['applied_date']))) ?></td>
                        <td data-label="Status"><?= htmlspecialchars($row['status']) ?></td>
                        <td data-label="Action">
                            <a href="insurance_details.php?id=<?= $row['insurance_id'] ?>" class="btn btn-primary btn-sm mb-1">View</a>
                            <button 
                                class="btn btn-warning btn-sm mb-1 editBtn"
                                data-insurance_id="<?= $row['insurance_id'] ?>"
                                data-client_id="<?= $row['client_id'] ?>"
                                data-first_name="<?= htmlspecialchars($row['first_name']) ?>"
                                data-last_name="<?= htmlspecialchars($row['last_name']) ?>"
                                data-middle_name="<?= htmlspecialchars($row['middle_name']) ?>"
                                data-birthday="<?= $row['birthday'] ?>"
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

    <!-- Edit Modal: Edit Customer Details -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form id="editForm" method="POST" action="edit_insurance.php">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editModalLabel">Edit Customer Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <!-- Hidden Fields -->
              <input type="hidden" name="insurance_id" id="edit_insurance_id" />
              <input type="hidden" name="client_id" id="edit_client_id" />

              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="edit_first_name" class="form-label">First Name</label>
                  <input type="text" class="form-control" id="edit_first_name" name="first_name" required />
                </div>
                <div class="col-md-4">
                  <label for="edit_middle_name" class="form-label">Middle Name</label>
                  <input type="text" class="form-control" id="edit_middle_name" name="middle_name" />
                </div>
                <div class="col-md-4">
                  <label for="edit_last_name" class="form-label">Last Name</label>
                  <input type="text" class="form-control" id="edit_last_name" name="last_name" required />
                </div>
              </div>

              <div class="mb-3">
                <label for="edit_birthday" class="form-label">Birthday</label>
                <input type="date" class="form-control" id="edit_birthday" name="birthday" />
              </div>

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
          // Initialize DataTables with all features
          $('#applicationsTable').DataTable({
              dom: '<"top"f>rt<"bottom"lip><"clear">', // Layout control
              pageLength: 10,
              lengthMenu: [10, 25, 50, 100],
              responsive: true
          });

          // Edit button handler
          $('.editBtn').on('click', function () {
              const btn = $(this);
              $('#edit_insurance_id').val(btn.data('insurance_id'));
              $('#edit_client_id').val(btn.data('client_id'));
              $('#edit_first_name').val(btn.data('first_name'));
              $('#edit_middle_name').val(btn.data('middle_name'));
              $('#edit_last_name').val(btn.data('last_name'));
              $('#edit_birthday').val(btn.data('birthday'));
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