<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../../DB_connection/db.php';

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coc'])) {
    header("Content-Type: application/json");
    
    try {
        // Anti-spam protection - 1 hour cooldown
        if (isset($_SESSION['last_submission'])) {
            $cooldown = 3600; // 1 hour in seconds
            $elapsed = time() - $_SESSION['last_submission'];
            if ($elapsed < $cooldown) {
                $remaining = $cooldown - $elapsed;
                throw new Exception("Please wait " . gmdate("i\m s\s", $remaining) . " before submitting another request.");
            }
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception("Security token mismatch. Please refresh the page.");
        }

        // Validate user session
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Session expired. Please login again.");
        }

        $database = new Database();
        $conn = $database->getConnection();

        // Get client_id in a single query with proper validation
        $stmt = $conn->prepare("
            SELECT c.client_id 
            FROM clients c
            INNER JOIN users u ON c.user_id = u.user_id
            WHERE u.user_id = :user_id
            LIMIT 1
        ");
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        if (!$stmt->execute() || !($client = $stmt->fetch(PDO::FETCH_ASSOC))) {
            throw new Exception("No client profile found for this user.");
        }
        $client_id = (int)$client['client_id'];

        // Validate and sanitize COC input
        $coc = trim($_POST['coc']);
        if (empty($coc)) {
            throw new Exception("Please enter a Certificate of Coverage (COC).");
        }
        
        if (!preg_match('/^[a-zA-Z0-9\-]{10,20}$/', $coc)) {
            throw new Exception("Invalid COC format. Only alphanumeric characters and hyphens are allowed.");
        }

        // Debug: Log input values
        error_log("DEBUG: COC submission attempt - User ID: {$_SESSION['user_id']}, Client ID: $client_id, COC: $coc");

        // Combined validation query - checks all conditions in one query
        $stmt = $conn->prepare("
            SELECT 
                ir.insurance_id, 
                ir.client_id, 
                ir.vehicle_id,
                CASE 
                    WHEN ir.certificate_of_coverage IS NULL THEN 'COC missing'
                    WHEN ir.client_id != :client_id THEN 'COC belongs to another client'
                    WHEN ir.is_paid != 'Paid' THEN 'Insurance not paid'
                    WHEN ir.status != 'Approved' THEN 'Insurance not approved'
                    WHEN ir.is_claimed != 'Claimed' THEN 'Documents not yet claimed'
                    WHEN EXISTS (
                        SELECT 1 FROM lost_documents 
                        WHERE certificate_of_coverage = :coc 
                        AND (status = 'Approved' OR status = 'Pending')
                        LIMIT 1
                    ) THEN 'Duplicate submission detected'
                    ELSE 'Valid'
                END AS validation_status
            FROM insurance_registration ir
            WHERE ir.certificate_of_coverage = :coc
            LIMIT 1
        ");
        $stmt->bindValue(':coc', $coc, PDO::PARAM_STR);
        $stmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("Invalid COC. Certificate of Coverage not found in system.");
        }

        if ($result['validation_status'] !== 'Valid') {
            throw new Exception("Invalid COC. " . str_replace('_', ' ', $result['validation_status']));
        }

        // Insert new lost document record
        $stmt = $conn->prepare("
            INSERT INTO lost_documents (
                client_id, vehicle_id, insurance_id,
                certificate_of_coverage, status, is_paid, application_date
            ) VALUES (:client_id, :vehicle_id, :insurance_id, :coc, 'Pending', 'Unpaid', NOW())
        ");
        $stmt->bindValue(':client_id', $result['client_id'], PDO::PARAM_INT);
        $stmt->bindValue(':vehicle_id', $result['vehicle_id'], PDO::PARAM_INT);
        $stmt->bindValue(':insurance_id', $result['insurance_id'], PDO::PARAM_INT);
        $stmt->bindValue(':coc', $coc, PDO::PARAM_STR);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to submit application. Please try again.");
        }

        // Set submission timestamp to prevent spam
        $_SESSION['last_submission'] = time();

        echo json_encode([
            'success' => true,
            'message' => 'Lost document application submitted successfully!'
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => escape($e->getMessage())
        ]);
    }
    exit;
}

// Fetch valid COCs for current user
$cocs = [];
if (isset($_SESSION['user_id'])) {
    try {
        $database = new Database();
        $conn = $database->getConnection();

        // Get client_id and COCs in optimized query
        $stmt = $conn->prepare("
            SELECT ir.certificate_of_coverage 
            FROM insurance_registration ir
            INNER JOIN clients c ON ir.client_id = c.client_id
            INNER JOIN users u ON c.user_id = u.user_id
            WHERE u.user_id = :user_id
            AND ir.is_paid = 'Paid'
            AND ir.status = 'Approved'
            AND ir.is_claimed = 'Claimed'
            AND ir.certificate_of_coverage IS NOT NULL
            AND NOT EXISTS (
                SELECT 1 FROM lost_documents ld
                WHERE ld.certificate_of_coverage = ir.certificate_of_coverage
                AND (ld.status = 'Approved' OR ld.status = 'Pending')
            )
        ");
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $cocs = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        error_log("DEBUG: Available COCs for user {$_SESSION['user_id']}: " . print_r($cocs, true));
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Lost Document</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="css/lost_document.css">
    <style>
        .loading { color: blue; }
        .success { color: green; }
        .error { color: red; }
        #coc-list { display: none; }
        .form-group { margin-bottom: 1rem; }
        small { display: block; color: #666; margin-top: 0.25rem; }
        .debug-info { 
            background-color: #f8f9fa; 
            padding: 10px; 
            margin: 10px 0; 
            border: 1px solid #ddd;
            font-family: monospace;
        }
        #submitBtn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <h2>Apply for Lost Document</h2>
        
        <?php if (isset($_SESSION['user_id']) && !empty($cocs)): ?>
        <div class="debug-info">
            <h4>Debug Information</h4>
            <p>Your User ID: <?php echo $_SESSION['user_id']; ?></p>
            <p>Your Valid COCs:</p>
            <ul>
                <?php foreach ($cocs as $c): ?>
                <li><?php echo escape($c); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form id="applyLostDocumentForm" method="POST">
            <div class="form-group">
                <label for="coc">Certificate of Coverage (COC):</label>
                <input type="text" id="coc" name="coc" list="coc-list" 
                       placeholder="Enter your COC" required
                       autocomplete="off" pattern="[a-zA-Z0-9\-]{10,20}"
                       title="COC should be 10-20 alphanumeric characters">
                <datalist id="coc-list">
                    <?php foreach ($cocs as $coc): ?>
                        <option value="<?php echo escape($coc); ?>">
                    <?php endforeach; ?>
                </datalist>
                <small>Enter a valid COC from your approved insurances that you've already claimed</small>
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo escape($_SESSION['csrf_token']); ?>">

            <button type="submit" id="submitBtn">Submit Application</button>
        </form>

        <div id="response" class="response"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('applyLostDocumentForm');
            const responseDiv = document.getElementById('response');
            const submitBtn = document.getElementById('submitBtn');
            const cocInput = document.getElementById('coc');
            let cooldownTimer = null;
            
            // Client-side validation
            cocInput.addEventListener('input', function() {
                this.setCustomValidity(/^[a-zA-Z0-9\-]{10,20}$/.test(this.value) 
                    ? '' 
                    : 'COC should be 10-20 alphanumeric characters and hyphens only');
            });
            
            // Form submission handler
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Disable button immediately
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                responseDiv.innerHTML = '<p class="loading">Processing request...</p>';
                
                try {
                    const response = await fetch('', {
                        method: 'POST',
                        body: new URLSearchParams(new FormData(form)),
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        responseDiv.innerHTML = `<p class="success">✓ ${data.message}</p>`;
                        form.reset();
                        
                        // Start cooldown timer
                        startCooldown(3600); // 1 hour cooldown
                    } else {
                        responseDiv.innerHTML = `<p class="error">✗ ${data.message}</p>`;
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit Application';
                    }
                } catch (error) {
                    responseDiv.innerHTML = `<p class="error">Network Error: ${error.message}</p>`;
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Application';
                }
            });
            
            // Cooldown timer function
            function startCooldown(seconds) {
                if (cooldownTimer) clearInterval(cooldownTimer);
                
                let remaining = seconds;
                submitBtn.disabled = true;
                
                function updateButton() {
                    const minutes = Math.floor(remaining / 60);
                    const secs = remaining % 60;
                    submitBtn.textContent = `Please wait (${minutes}m ${secs}s)`;
                    
                    if (remaining <= 0) {
                        clearInterval(cooldownTimer);
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit Application';
                    } else {
                        remaining--;
                    }
                }
                
                updateButton();
                cooldownTimer = setInterval(updateButton, 1000);
            }
            
            // Check for existing cooldown on page load
            <?php if (isset($_SESSION['last_submission'])): ?>
                const lastSubmission = <?php echo $_SESSION['last_submission'] ?? 0; ?>;
                const now = Math.floor(Date.now() / 1000);
                const cooldown = 3600; // 1 hour in seconds
                
                if (lastSubmission > 0 && (now - lastSubmission) < cooldown) {
                    const remaining = cooldown - (now - lastSubmission);
                    startCooldown(remaining);
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>