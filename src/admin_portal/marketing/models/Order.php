<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\models\Order.php
require_once __DIR__ . '/../config/database.php';

class Order {
    private $conn;
    private $table_name = "orders";

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // Get recent orders
    public function getRecent($limit = 10) {
        try {
            $query = "SELECT o.*, p.platform_name 
                      FROM " . $this->table_name . " o
                      LEFT JOIN connected_platforms p ON o.platform_id = p.id
                      ORDER BY o.order_time DESC
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getRecent: " . $e->getMessage());
            return [];
        }
    }

    // Get today's stats
    public function getTodayStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_orders,
                        COALESCE(SUM(total_amount), 0) as total_revenue,
                        COALESCE(SUM(commission), 0) as total_commission,
                        COALESCE(AVG(total_amount), 0) as avg_order_value
                      FROM " . $this->table_name . "
                      WHERE DATE(order_time) = CURDATE()";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_orders' => (int)($result['total_orders'] ?? 0),
                'total_revenue' => (float)($result['total_revenue'] ?? 0),
                'total_commission' => (float)($result['total_commission'] ?? 0),
                'avg_order_value' => (float)($result['avg_order_value'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Error in getTodayStats: " . $e->getMessage());
            return [
                'total_orders' => 0,
                'total_revenue' => 0,
                'total_commission' => 0,
                'avg_order_value' => 0
            ];
        }
    }

    // Get order by ID
    public function getById($id) {
        try {
            $query = "SELECT o.*, p.platform_name 
                      FROM " . $this->table_name . " o
                      LEFT JOIN connected_platforms p ON o.platform_id = p.id
                      WHERE o.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getById: " . $e->getMessage());
            return false;
        }
    }

    // Get orders by platform
    public function getByPlatform($platform_id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE platform_id = :platform_id 
                      ORDER BY order_time DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':platform_id', $platform_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getByPlatform: " . $e->getMessage());
            return [];
        }
    }

    // Update order status
    public function updateStatus($order_id, $status) {
        try {
            $allowed_statuses = ['pending', 'preparing', 'picked_up', 'delivered', 'cancelled'];
            if (!in_array($status, $allowed_statuses)) {
                return false;
            }

            $query = "UPDATE " . $this->table_name . " 
                      SET status = :status 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $order_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in updateStatus: " . $e->getMessage());
            return false;
        }
    }

    // Create new order
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                      SET order_number=:order_number,
                          platform_id=:platform_id,
                          customer_name=:customer_name,
                          customer_phone=:customer_phone,
                          customer_email=:customer_email,
                          total_items=:total_items,
                          subtotal=:subtotal,
                          delivery_fee=:delivery_fee,
                          commission=:commission,
                          total_amount=:total_amount,
                          status=:status,
                          payment_status=:payment_status,
                          delivery_address=:delivery_address";
            
            $stmt = $this->conn->prepare($query);
            
            // Set default values
            $data['customer_email'] = $data['customer_email'] ?? '';
            $data['subtotal'] = $data['subtotal'] ?? $data['total_amount'];
            $data['delivery_fee'] = $data['delivery_fee'] ?? 0;
            $data['payment_status'] = $data['payment_status'] ?? 'unpaid';
            
            // Bind values
            $stmt->bindParam(':order_number', $data['order_number']);
            $stmt->bindParam(':platform_id', $data['platform_id']);
            $stmt->bindParam(':customer_name', $data['customer_name']);
            $stmt->bindParam(':customer_phone', $data['customer_phone']);
            $stmt->bindParam(':customer_email', $data['customer_email']);
            $stmt->bindParam(':total_items', $data['total_items']);
            $stmt->bindParam(':subtotal', $data['subtotal']);
            $stmt->bindParam(':delivery_fee', $data['delivery_fee']);
            $stmt->bindParam(':commission', $data['commission']);
            $stmt->bindParam(':total_amount', $data['total_amount']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':payment_status', $data['payment_status']);
            $stmt->bindParam(':delivery_address', $data['delivery_address']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in create: " . $e->getMessage());
            return false;
        }
    }

    // Get orders by date range
    public function getByDateRange($start_date, $end_date) {
        try {
            $query = "SELECT o.*, p.platform_name 
                      FROM " . $this->table_name . " o
                      LEFT JOIN connected_platforms p ON o.platform_id = p.id
                      WHERE DATE(o.order_time) BETWEEN :start_date AND :end_date
                      ORDER BY o.order_time DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getByDateRange: " . $e->getMessage());
            return [];
        }
    }

    // Delete order
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

    // Get order statistics for a period
    public function getStatsForPeriod($days = 30) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_orders,
                        COALESCE(SUM(total_amount), 0) as total_revenue,
                        COALESCE(SUM(commission), 0) as total_commission,
                        COALESCE(AVG(total_amount), 0) as avg_order_value,
                        COUNT(DISTINCT platform_id) as platforms_used
                      FROM " . $this->table_name . "
                      WHERE order_time >= DATE_SUB(CURDATE(), INTERVAL :days DAY)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getStatsForPeriod: " . $e->getMessage());
            return [
                'total_orders' => 0,
                'total_revenue' => 0,
                'total_commission' => 0,
                'avg_order_value' => 0,
                'platforms_used' => 0
            ];
        }
    }
}
?>