<?php
session_start();
$allowed_roles = ['Staff'];
require '../../Logout_Login/Restricted.php';

// Database connection
require_once '../../DB_connection/db.php';
$database = new Database();
$conn = $database->getConnection();

// Fetch lost documents data with related vehicle and insurance details
$lost_documents = [];
try {
    $stmt = $conn->prepare("
        SELECT 
            ld.lost_document_id,
            ld.client_id,
            ld.vehicle_id,
            ld.insurance_id,
            ld.document_id,
            ld.application_date,
            ld.status,
            ld.certificate_of_coverage,
            ld.is_paid,
            CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) AS client_name,
            COALESCE(v.vehicle_type, 'N/A') AS vehicle_type,
            COALESCE(v.brand, 'N/A') AS brand,
            COALESCE(v.model, 'N/A') AS model,
            COALESCE(v.year, 'N/A') AS year,
            COALESCE(ir.type_of_insurance, 'N/A') AS insurance_type
        FROM lost_documents ld
        LEFT JOIN clients c ON ld.client_id = c.client_id
        LEFT JOIN users u ON c.user_id = u.user_id
        LEFT JOIN vehicles v ON ld.vehicle_id = v.vehicle_id
        LEFT JOIN insurance_registration ir ON ld.insurance_id = ir.insurance_id
        ORDER BY ld.application_date DESC
    ");
    $stmt->execute();
    $lost_documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug output (remove in production)
    error_log("Lost documents fetched: " . count($lost_documents));
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Error loading lost documents. Please try again later.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Documents</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/customer_table.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <style>
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            text-transform: capitalize;
        }
        .status.pending {
            background-color: #FFF3CD;
            color: #856404;
        }
        .status.approved {
            background-color: #D4EDDA;
            color: #155724;
        }
        .status.rejected {
            background-color: #F8D7DA;
            color: #721C24;
        }
        .view-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .view-btn:hover {
            background-color: #45a049;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Page Title -->
        <h1 class="activity-title">Lost Documents</h1>

        <!-- Search and Add Client Section -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search by name or status..." onkeyup="searchTable()">
            <button class="add-client-btn" onclick="addNewClient()">Add Client</button>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <table id="lostDocumentsTable" class="display">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>COC Number</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Vehicle</th>
                        <th>Insurance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lost_documents)): ?>
                        <?php foreach ($lost_documents as $document): ?>
                            <tr>
                                <td><?= htmlspecialchars($document['client_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($document['certificate_of_coverage'] ?? 'N/A') ?></td>
                                <td><?= date('Y-m-d', strtotime($document['application_date'])) ?></td>
                                <td>
                                    <span class="status <?= strtolower($document['status'] ?? '') ?>">
                                        <?= htmlspecialchars($document['status'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($document['vehicle_type'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($document['insurance_type'] ?? 'N/A') ?></td>
                                <td>
                                    <button class="view-btn" onclick="viewDocument(<?= $document['lost_document_id'] ?>)">
                                        View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">No lost document records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#lostDocumentsTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100]
            });
        });

        function viewDocument(documentId) {
            window.location.href = 'lost_details.php?id=' + documentId;
        }

        function addNewClient() {
            window.location.href = 'add_client.php';
        }

        function searchTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll("#lostDocumentsTable tbody tr");

            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const coc = row.cells[1].textContent.toLowerCase();
                const status = row.cells[3].textContent.toLowerCase();
                const searchText = name + ' ' + coc + ' ' + status;
                row.style.display = searchText.includes(input) ? "" : "none";
            });
        }
    </script>

</body>
</html>