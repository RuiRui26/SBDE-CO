<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../../DB_connection/db.php';

// Function to escape output safely
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Check if this is an API request (AJAX fetch)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['lost_document_id'])) {
    header("Content-Type: application/json");

    try {
        $database = new Database();
        $conn = $database->getConnection();

        if (!isset($_SESSION['user_id'])) {
            throw new Exception("User not logged in.");
        }

        $user_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
        if ($user_id === false) {
            throw new Exception("Invalid user ID.");
        }

        $lost_document_id = filter_var($_GET['lost_document_id'], FILTER_VALIDATE_INT);

        $stmt = $conn->prepare("
            SELECT ld.certificate_of_coverage
            FROM lost_documents ld
            INNER JOIN clients c ON ld.client_id = c.client_id
            WHERE ld.lost_document_id = ? AND c.user_id = ?
        ");
        $stmt->execute([$lost_document_id, $user_id]);
        $document = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$document) {
            throw new Exception("Certificate of Coverage not found or unauthorized access.");
        }

        echo json_encode([
            'success' => true,
            'certificate_of_coverage' => $document['certificate_of_coverage']
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// If not an API call, render the page
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Coverage</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/lost_document.css"> <!-- Link your custom CSS -->
</head>
<body>
    <h2>Certificate of Coverage</h2>
    <form id="getCOCForm">
        <div class="form-group">
            <input type="number" id="lost_document_id" name="lost_document_id" required>
        </div>

        <button type="submit">Get Certificate of Coverage</button>
    </form>

    <div id="response" class="response"></div>
    <div id="cocDisplay" class="coc-display"></div>

    <script>
        document.getElementById('getCOCForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const lostDocumentId = document.getElementById('lost_document_id').value;
            const responseDiv = document.getElementById('response');
            const cocDisplayDiv = document.getElementById('cocDisplay');

            fetch(window.location.pathname + "?lost_document_id=" + encodeURIComponent(lostDocumentId))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    responseDiv.innerHTML = `<p class="success">Certificate retrieved successfully.</p>`;

                    if (data.certificate_of_coverage) {
                        const filePath = escapeHtml(data.certificate_of_coverage); // Escape output
                        const ext = filePath.split('.').pop().toLowerCase();

                        if (ext === 'pdf') {
                            cocDisplayDiv.innerHTML = `<iframe src="${filePath}" width="100%" height="600px"></iframe>`;
                        } else if (['jpg', 'jpeg', 'png'].includes(ext)) {
                            cocDisplayDiv.innerHTML = `<img src="${filePath}" alt="Certificate of Coverage">`;
                        } else {
                            cocDisplayDiv.innerHTML = `<p>Unsupported file type.</p>`;
                        }
                    } else {
                        cocDisplayDiv.innerHTML = `<p>No Certificate of Coverage uploaded.</p>`;
                    }

                } else {
                    responseDiv.innerHTML = `<p class="error">${escapeHtml(data.message)}</p>`;
                    cocDisplayDiv.innerHTML = '';
                }
            })
            .catch(error => {
                responseDiv.innerHTML = `<p class="error">An error occurred: ${escapeHtml(error.message)}</p>`;
                cocDisplayDiv.innerHTML = '';
            });
        });

        // Basic escape function in JS
        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
