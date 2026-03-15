<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\models\PromoCode.php
require_once __DIR__ . '/../config/database.php';

class PromoCode {
    private $conn;
    private $table_name = "promo_codes";

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // Get all promo codes with campaign details
    public function getAll() {
        try {
            $query = "SELECT p.*, c.campaign_name, c.discount_type, c.discount_value 
                      FROM " . $this->table_name . " p
                      LEFT JOIN marketing_campaigns c ON p.campaign_id = c.id
                      ORDER BY p.is_active DESC, p.current_uses DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAll: " . $e->getMessage());
            return [];
        }
    }

    // Get active promo codes
    public function getActive() {
        try {
            $query = "SELECT p.*, c.campaign_name, c.discount_type, c.discount_value,
                             c.end_date, c.status as campaign_status
                      FROM " . $this->table_name . " p
                      LEFT JOIN marketing_campaigns c ON p.campaign_id = c.id
                      WHERE p.is_active = 1 AND c.status IN ('active', 'scheduled')
                      ORDER BY c.end_date ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getActive: " . $e->getMessage());
            return [];
        }
    }

    // Get promo codes by campaign
    public function getByCampaign($campaign_id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE campaign_id = :campaign_id 
                      ORDER BY code";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':campaign_id', $campaign_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getByCampaign: " . $e->getMessage());
            return [];
        }
    }

    // Create promo code
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                      SET campaign_id=:campaign_id,
                          code=:code,
                          description=:description,
                          max_uses=:max_uses,
                          is_active=:is_active";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':campaign_id', $data['campaign_id']);
            $stmt->bindParam(':code', $data['code']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':max_uses', $data['max_uses']);
            $stmt->bindParam(':is_active', $data['is_active']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in create: " . $e->getMessage());
            return false;
        }
    }

    // Update promo code usage
    public function incrementUsage($code) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET current_uses = current_uses + 1 
                      WHERE code = :code AND current_uses < max_uses";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':code', $code);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in incrementUsage: " . $e->getMessage());
            return false;
        }
    }

    // Delete promo code
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }
}
?>