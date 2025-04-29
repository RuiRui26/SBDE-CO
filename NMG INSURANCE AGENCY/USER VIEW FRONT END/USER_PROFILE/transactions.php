<?php
include 'sidebar.php';

// Allow only Client role (or add more roles as needed)
$allowed_roles = ['Client'];

require '../../../Logout_Login/Restricted.php';
require_once '../../../DB_connection/db.php';

$database = new Database();
$pdo = $database->getConnection();

$user_id = $_SESSION['user_id'] ?? null;

$client = [];
if ($user_id) {
    // Select all address-related fields including the combined 'address' field
    $stmt = $pdo->prepare("SELECT client_id, full_name, contact_number, 
                          street_address, barangay, city, zip_code, address 
                          FROM clients WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
}

$client_id = $client['client_id'] ?? null;
$full_name = $client['full_name'] ?? 'User';
$contact_number = $client['contact_number'] ?? '';

// Improved address handling - check both the combined address and components
$address = 'N/A';

// First try to use the pre-formatted address if it exists and isn't empty/null
if (!empty($client['address']) && strtolower($client['address']) !== 'null') {
    $address = $client['address'];
} 
// Otherwise build from components
else {
    $address_parts = [
        trim($client['street_address'] ?? ''),
        trim($client['barangay'] ?? ''),
        trim($client['city'] ?? ''),
        trim($client['zip_code'] ?? '')
    ];
    
    // Filter out empty parts and 'null' strings
    $address_parts = array_filter($address_parts, function($part) {
        return $part !== '' && strtolower($part) !== 'null';
    });
    
    if (count($address_parts) > 0) {
        $address = implode(', ', $address_parts);
    }
}

$insurance_data = [];
if ($client_id) {
    $stmt = $pdo->prepare("
        SELECT 
            ir.insurance_id,
            ir.created_at AS register_date,
            v.plate_number,
            v.mv_file_number,
            v.vehicle_type,
            v.brand,
            v.model,
            v.year,
            v.color,
            ir.type_of_insurance,
            ir.status,
            ir.is_paid,
            ir.is_claimed,
            ir.start_date,
            ir.expired_at
        FROM insurance_registration ir
        JOIN vehicles v ON ir.vehicle_id = v.vehicle_id
        WHERE ir.client_id = :client_id
        ORDER BY ir.created_at DESC
    ");
    $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
    $stmt->execute();
    $insurance_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Records</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

    <!-- jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <link rel="stylesheet" href="css/transactions.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>
<body>

    <div class="main-content">
        <h2>Transaction Records</h2>

        <div class="client-info" id="print-client-info">
            <p><strong>Name:</strong> <?= htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Contact Number:</strong> <?= htmlspecialchars($contact_number, ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="date-filter">
            <label for="start-date">From:</label>
            <input type="date" id="start-date" name="start-date">
            <label for="end-date">To:</label>
            <input type="date" id="end-date" name="end-date">
            <button id="filter-btn">Filter</button>
            <button id="clear-filter-btn">Clear Filter</button>
        </div>

        <table id="transactionsTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Date Registered</th>
                    <th>Plate No. / MV File No.</th>
                    <th>Vehicle Type</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Color</th>
                    <th>Insurance Type</th>
                    <th>Status</th>
                    <th>Paid Status</th>
                    <th>Claimed Status</th>
                    <th>Start Date</th>
                    <th>Expiration Date</th>
                    <th>View Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($insurance_data as $row): 
                    $register_date = date('F j, Y', strtotime($row['register_date']));
                    $start_date = $row['start_date'] ? date('F j, Y', strtotime($row['start_date'])) : 'N/A';
                    $expiration_date = $row['expired_at'] ? date('F j, Y', strtotime($row['expired_at'])) : 'N/A';

                    $plate_or_mv = !empty($row['plate_number']) ? htmlspecialchars($row['plate_number'], ENT_QUOTES, 'UTF-8') : 
                                   (!empty($row['mv_file_number']) ? htmlspecialchars($row['mv_file_number'], ENT_QUOTES, 'UTF-8') : 'N/A');

                    $plate_number_encoded = !empty($row['plate_number']) ? urlencode($row['plate_number']) : '';
                    $mv_file_number_encoded = !empty($row['mv_file_number']) ? urlencode($row['mv_file_number']) : '';
                ?>
                <tr>
                    <td><?= htmlspecialchars($register_date, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $plate_or_mv ?></td>
                    <td><?= htmlspecialchars($row['vehicle_type'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['brand'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['model'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['year'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['color'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['type_of_insurance'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['is_paid'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['is_claimed'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($expiration_date, ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <a href="view_transaction.php?insurance_id=<?= urlencode($row['insurance_id']) ?>&plate_number=<?= $plate_number_encoded ?>&mv_file_number=<?= $mv_file_number_encoded ?>" class="btn btn-info">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            const table = $('#transactionsTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'print',
                        title: 'Transaction Records',
                        customize: function (win) {
                            $(win.document.body).css('font-size', '14px');
                            const clientInfoHtml = $('#print-client-info').clone().css({
                                marginBottom: '20px',
                                padding: '15px',
                                border: '1px solid #ccc'
                            }).wrap('<div>').parent().html();
                            $(win.document.body).prepend(clientInfoHtml);
                        }
                    },
                    'copy', 'csv', 'excel', 'pdf'
                ],
                order: [[0, 'desc']],
                scrollX: true  // Added to handle horizontal scrolling if needed
            });

            $('#filter-btn').on('click', function () {
                let start = $('#start-date').val();
                let end = $('#end-date').val();

                // Clear all previous filters
                $.fn.dataTable.ext.search = [];

                if (start && end) {
                    const startDate = new Date(start).getTime();
                    const endDate = new Date(end).getTime();

                    $.fn.dataTable.ext.search.push(function(settings, data) {
                        const rowDate = new Date(data[0]).getTime();  // Date Registered column
                        return rowDate >= startDate && rowDate <= endDate;
                    });
                }

                table.draw();
            });

            $('#clear-filter-btn').on('click', function() {
                $('#start-date').val('');
                $('#end-date').val('');

                $.fn.dataTable.ext.search = [];
                table.draw();
            });
        });
    </script>

</body>
</html>