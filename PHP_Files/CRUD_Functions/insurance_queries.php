<?php
require_once "../../DB_connection/db.php"; // Ensure this path is correct

class InsuranceTransactions {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getTransactions($search = '', $limit = 10, $offset = 0) {
        try {
            $query = "
    SELECT ir.insurance_id, c.full_name, v.plate_number, v.mv_file_number, 
           ir.type_of_insurance, ir.created_at, ir.status 
    FROM nmg_insurance.insurance_registration ir
    JOIN nmg_insurance.clients c ON ir.client_id = c.client_id
    JOIN nmg_insurance.vehicles v ON ir.vehicle_id = v.vehicle_id
";

            // Search Filter
            if (!empty($search)) {
                $query .= " WHERE c.full_name LIKE :search OR v.plate_number LIKE :search OR ir.type_of_insurance LIKE :search OR ir.status LIKE :search ";
            }

            $query .= " ORDER BY ir.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            if (!empty($search)) {
                $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching transactions: " . $e->getMessage());
            return [];
        }
    }

    public function updateTransactionStatus($insurance_id, $status) {
        try {
            $query = "UPDATE nmg_insurance.insurance_registration SET status = :status WHERE insurance_id = :insurance_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':insurance_id', $insurance_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating transaction status: " . $e->getMessage());
            return false;
        }
    }

    public function getExpiringInsurances($days = 30) {
        try {
            $sql = "
                SELECT i.insurance_id, c.full_name, i.type_of_insurance, i.status, i.expired_at AS expiry_date
                FROM nmg_insurance.insurance_registration i
                JOIN nmg_insurance.clients c ON i.client_id = c.client_id
                WHERE i.expired_at IS NOT NULL
                  AND i.expired_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ";
    
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $days, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching expiring insurances: " . $e->getMessage());
            return [];
        }
    }
    
    
    public function renewInsurance($insurance_id, $adminName = 'System') {
        try {
            // Get current expiry
            $selectQuery = "SELECT expired_at FROM nmg_insurance.insurance_registration WHERE insurance_id = :insurance_id";
            $selectStmt = $this->conn->prepare($selectQuery);
            $selectStmt->bindParam(':insurance_id', $insurance_id, PDO::PARAM_INT);
            $selectStmt->execute();
            $row = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$row) return false;
    
            $old_expiry = $row['expired_at'];
            $new_expiry = date('Y-m-d', strtotime($old_expiry . ' +1 year'));
    
            // Insert into renewal log
            $logQuery = "
                INSERT INTO nmg_insurance.insurance_renewals (insurance_id, old_expiry_date, new_expiry_date, renewed_by)
                VALUES (:insurance_id, :old_expiry, :new_expiry, :renewed_by)
            ";
            $logStmt = $this->conn->prepare($logQuery);
            $logStmt->bindParam(':insurance_id', $insurance_id, PDO::PARAM_INT);
            $logStmt->bindParam(':old_expiry', $old_expiry);
            $logStmt->bindParam(':new_expiry', $new_expiry);
            $logStmt->bindParam(':renewed_by', $adminName);
            $logStmt->execute();
    
            // Update insurance record
            $updateQuery = "
                UPDATE nmg_insurance.insurance_registration 
                SET expired_at = :new_expiry, status = 'active'
                WHERE insurance_id = :insurance_id
            ";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':new_expiry', $new_expiry);
            $updateStmt->bindParam(':insurance_id', $insurance_id, PDO::PARAM_INT);
            return $updateStmt->execute();
    
        } catch (PDOException $e) {
            error_log("Error renewing insurance: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllInsurances() {
    try {
        $query = "
            SELECT ir.insurance_id, c.full_name, ir.type_of_insurance, ir.expiry_date, ir.status
            FROM nmg_insurance.insurance_registration ir
            JOIN nmg_insurance.clients c ON ir.client_id = c.client_id
            ORDER BY ir.expiry_date ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching all insurances: " . $e->getMessage());
        return [];
    }
}

    
    
    
}
?>

