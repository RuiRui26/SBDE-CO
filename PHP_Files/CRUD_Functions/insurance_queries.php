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
}
?>

