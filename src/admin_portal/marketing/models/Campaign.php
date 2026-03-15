<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\models\Campaign.php
require_once __DIR__ . '/../config/database.php';

class Campaign {
    private $conn;
    private $table_name = "marketing_campaigns";

    public $id;
    public $campaign_name;
    public $description;
    public $campaign_type;
    public $status;
    public $discount_type;
    public $discount_value;
    public $start_date;
    public $end_date;
    public $target_audience;
    public $target_redemptions;
    public $current_redemptions;
    public $revenue_generated;
    public $budget;
    public $roi;
    public $bg_color;
    public $text_color;

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // Get all campaigns
    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY 
                      CASE status
                          WHEN 'active' THEN 1
                          WHEN 'scheduled' THEN 2
                          WHEN 'draft' THEN 3
                          WHEN 'ended' THEN 4
                      END, start_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAll: " . $e->getMessage());
            return [];
        }
    }

    // Get campaign by ID
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getById: " . $e->getMessage());
            return false;
        }
    }

    // Get campaigns by status
    public function getByStatus($status) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE status = :status ORDER BY start_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getByStatus: " . $e->getMessage());
            return [];
        }
    }

    // Get dashboard stats
    public function getStats() {
        try {
            $query = "SELECT 
                        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_campaigns,
                        COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_campaigns,
                        COUNT(CASE WHEN status = 'ended' THEN 1 END) as ended_campaigns,
                        COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_campaigns,
                        COALESCE(SUM(current_redemptions), 0) as total_redemptions,
                        COALESCE(SUM(revenue_generated), 0) as total_revenue,
                        COALESCE(AVG(CASE WHEN status = 'active' THEN roi END), 0) as avg_roi
                      FROM " . $this->table_name;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getStats: " . $e->getMessage());
            return [
                'active_campaigns' => 0,
                'scheduled_campaigns' => 0,
                'ended_campaigns' => 0,
                'draft_campaigns' => 0,
                'total_redemptions' => 0,
                'total_revenue' => 0,
                'avg_roi' => 0
            ];
        }
    }

    // Create new campaign
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                      SET campaign_name=:campaign_name,
                          description=:description,
                          campaign_type=:campaign_type,
                          status=:status,
                          discount_type=:discount_type,
                          discount_value=:discount_value,
                          start_date=:start_date,
                          end_date=:end_date,
                          target_audience=:target_audience,
                          target_redemptions=:target_redemptions,
                          budget=:budget,
                          bg_color=:bg_color,
                          text_color=:text_color";

            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':campaign_name', $this->campaign_name);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':campaign_type', $this->campaign_type);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':discount_type', $this->discount_type);
            $stmt->bindParam(':discount_value', $this->discount_value);
            $stmt->bindParam(':start_date', $this->start_date);
            $stmt->bindParam(':end_date', $this->end_date);
            $stmt->bindParam(':target_audience', $this->target_audience);
            $stmt->bindParam(':target_redemptions', $this->target_redemptions);
            $stmt->bindParam(':budget', $this->budget);
            $stmt->bindParam(':bg_color', $this->bg_color);
            $stmt->bindParam(':text_color', $this->text_color);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in create: " . $e->getMessage());
            return false;
        }
    }

    // Update campaign
    public function update() {
        try {
            $query = "UPDATE " . $this->table_name . "
                      SET campaign_name=:campaign_name,
                          description=:description,
                          campaign_type=:campaign_type,
                          status=:status,
                          discount_type=:discount_type,
                          discount_value=:discount_value,
                          start_date=:start_date,
                          end_date=:end_date,
                          target_audience=:target_audience,
                          target_redemptions=:target_redemptions,
                          budget=:budget,
                          bg_color=:bg_color,
                          text_color=:text_color
                      WHERE id=:id";

            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':campaign_name', $this->campaign_name);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':campaign_type', $this->campaign_type);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':discount_type', $this->discount_type);
            $stmt->bindParam(':discount_value', $this->discount_value);
            $stmt->bindParam(':start_date', $this->start_date);
            $stmt->bindParam(':end_date', $this->end_date);
            $stmt->bindParam(':target_audience', $this->target_audience);
            $stmt->bindParam(':target_redemptions', $this->target_redemptions);
            $stmt->bindParam(':budget', $this->budget);
            $stmt->bindParam(':bg_color', $this->bg_color);
            $stmt->bindParam(':text_color', $this->text_color);
            $stmt->bindParam(':id', $this->id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in update: " . $e->getMessage());
            return false;
        }
    }

    // Delete campaign
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

    // Update campaign progress
    public function updateProgress($id, $redemptions, $revenue) {
        try {
            $query = "UPDATE " . $this->table_name . "
                      SET current_redemptions = current_redemptions + :redemptions,
                          revenue_generated = revenue_generated + :revenue,
                          roi = ((revenue_generated + :revenue) - budget) / budget * 100
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':redemptions', $redemptions);
            $stmt->bindParam(':revenue', $revenue);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in updateProgress: " . $e->getMessage());
            return false;
        }
    }
}
?>