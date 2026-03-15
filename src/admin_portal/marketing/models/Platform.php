<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\models\Platform.php
require_once __DIR__ . '/../config/database.php';

class Platform {
    private $conn;
    private $table_name = "connected_platforms";

    public $id;
    public $platform_name;
    public $platform_type;
    public $status;
    public $commission_rate;
    public $api_key;
    public $api_secret;
    public $webhook_url;
    public $icon_class;
    public $bg_color;

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // Get all connected platforms
    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY platform_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAll: " . $e->getMessage());
            return [];
        }
    }

    // Get platform by ID
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

    // Get platform stats
    public function getStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_platforms,
                        SUM(CASE WHEN status = 'connected' THEN 1 ELSE 0 END) as connected_platforms,
                        AVG(commission_rate) as avg_commission
                      FROM " . $this->table_name;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getStats: " . $e->getMessage());
            return [
                'total_platforms' => 0,
                'connected_platforms' => 0,
                'avg_commission' => 0
            ];
        }
    }

    // Create new platform (using object properties)
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                      SET platform_name=:platform_name,
                          platform_type=:platform_type,
                          status=:status,
                          commission_rate=:commission_rate,
                          api_key=:api_key,
                          api_secret=:api_secret,
                          webhook_url=:webhook_url,
                          icon_class=:icon_class,
                          bg_color=:bg_color";

            $stmt = $this->conn->prepare($query);
            
            // Bind values from object properties
            $stmt->bindParam(':platform_name', $this->platform_name);
            $stmt->bindParam(':platform_type', $this->platform_type);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':commission_rate', $this->commission_rate);
            $stmt->bindParam(':api_key', $this->api_key);
            $stmt->bindParam(':api_secret', $this->api_secret);
            $stmt->bindParam(':webhook_url', $this->webhook_url);
            $stmt->bindParam(':icon_class', $this->icon_class);
            $stmt->bindParam(':bg_color', $this->bg_color);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in create: " . $e->getMessage());
            return false;
        }
    }

    // Update platform (using object properties)
    public function update() {
        try {
            $query = "UPDATE " . $this->table_name . "
                      SET platform_name=:platform_name,
                          platform_type=:platform_type,
                          status=:status,
                          commission_rate=:commission_rate,
                          api_key=:api_key,
                          api_secret=:api_secret,
                          webhook_url=:webhook_url,
                          icon_class=:icon_class,
                          bg_color=:bg_color
                      WHERE id=:id";

            $stmt = $this->conn->prepare($query);
            
            // Bind values from object properties
            $stmt->bindParam(':platform_name', $this->platform_name);
            $stmt->bindParam(':platform_type', $this->platform_type);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':commission_rate', $this->commission_rate);
            $stmt->bindParam(':api_key', $this->api_key);
            $stmt->bindParam(':api_secret', $this->api_secret);
            $stmt->bindParam(':webhook_url', $this->webhook_url);
            $stmt->bindParam(':icon_class', $this->icon_class);
            $stmt->bindParam(':bg_color', $this->bg_color);
            $stmt->bindParam(':id', $this->id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in update: " . $e->getMessage());
            return false;
        }
    }

    // Alternative update method that takes parameters (if you prefer)
    public function updateWithParams($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                      SET platform_name=:platform_name,
                          platform_type=:platform_type,
                          status=:status,
                          commission_rate=:commission_rate,
                          api_key=:api_key,
                          api_secret=:api_secret,
                          webhook_url=:webhook_url,
                          icon_class=:icon_class,
                          bg_color=:bg_color
                      WHERE id=:id";

            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':platform_name', $data['platform_name']);
            $stmt->bindParam(':platform_type', $data['platform_type']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':commission_rate', $data['commission_rate']);
            $stmt->bindParam(':api_key', $data['api_key']);
            $stmt->bindParam(':api_secret', $data['api_secret']);
            $stmt->bindParam(':webhook_url', $data['webhook_url']);
            $stmt->bindParam(':icon_class', $data['icon_class'] ?? 'globe');
            $stmt->bindParam(':bg_color', $data['bg_color'] ?? 'amber-100');
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in updateWithParams: " . $e->getMessage());
            return false;
        }
    }

    // Delete platform
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