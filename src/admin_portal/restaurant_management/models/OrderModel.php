<?php
// models/OrderModel.php
require_once __DIR__ . '/../includes/helpers.php';

class OrderModel {
    private $conn;
    private $orders_table = "orders";
    private $order_items_table = "order_items";
    private $order_types_table = "order_types";
    private $payments_table = "payments";
    private $invoices_table = "invoices";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get order types
    public function getOrderTypes() {
        $query = "SELECT * FROM " . $this->order_types_table . " WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get all orders with filters
    public function getOrders($filters = []) {
        $query = "SELECT o.*, ot.type_name, t.table_number,
                         CONCAT(c.first_name, ' ', c.last_name) as customer_name
                  FROM " . $this->orders_table . " o
                  LEFT JOIN " . $this->order_types_table . " ot ON o.order_type_id = ot.id
                  LEFT JOIN restaurant_tables t ON o.table_id = t.id
                  LEFT JOIN customers c ON o.customer_id = c.id
                  WHERE 1=1";
        
        $params = [];
        
        if(isset($filters['status'])) {
            $query .= " AND o.order_status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if(isset($filters['date'])) {
            $query .= " AND DATE(o.created_at) = :date";
            $params[':date'] = $filters['date'];
        }
        
        if(isset($filters['type'])) {
            $query .= " AND o.order_type_id = :type";
            $params[':type'] = $filters['type'];
        }
        
        $query .= " ORDER BY o.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get single order
    public function getOrder($id) {
        $query = "SELECT o.*, ot.type_name, t.table_number,
                         CONCAT(c.first_name, ' ', c.last_name) as customer_name
                  FROM " . $this->orders_table . " o
                  LEFT JOIN " . $this->order_types_table . " ot ON o.order_type_id = ot.id
                  LEFT JOIN restaurant_tables t ON o.table_id = t.id
                  LEFT JOIN customers c ON o.customer_id = c.id
                  WHERE o.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Get order items
    public function getOrderItems($order_id) {
        $query = "SELECT oi.*, mi.item_name, mi.preparation_time,
                         GROUP_CONCAT(
                             JSON_OBJECT('name', m.modifier_name, 'price', m.modifier_price)
                         ) as modifiers
                  FROM " . $this->order_items_table . " oi
                  LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
                  LEFT JOIN order_item_modifiers m ON oi.id = m.order_item_id
                  WHERE oi.order_id = :order_id
                  GROUP BY oi.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Create new order
    public function createOrder($data) {
        $this->conn->beginTransaction();
        
        try {
            // Generate order number
            $order_number = generateOrderNumber();
            
            // Insert order
            $query = "INSERT INTO " . $this->orders_table . " 
                      (order_number, order_type_id, customer_id, table_id, guest_name, 
                       guest_count, server_id, subtotal, tax_amount, total_amount, 
                       order_status, special_instructions, source)
                      VALUES 
                      (:order_number, :type_id, :customer_id, :table_id, :guest_name,
                       :guest_count, :server_id, :subtotal, :tax, :total,
                       :status, :instructions, :source)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_number', $order_number);
            $stmt->bindParam(':type_id', $data['order_type_id']);
            $stmt->bindParam(':customer_id', $data['customer_id']);
            $stmt->bindParam(':table_id', $data['table_id']);
            $stmt->bindParam(':guest_name', $data['guest_name']);
            $stmt->bindParam(':guest_count', $data['guest_count']);
            $stmt->bindParam(':server_id', $data['server_id']);
            $stmt->bindParam(':subtotal', $data['subtotal']);
            $stmt->bindParam(':tax', $data['tax_amount']);
            $stmt->bindParam(':total', $data['total_amount']);
            $stmt->bindParam(':status', $data['order_status']);
            $stmt->bindParam(':instructions', $data['special_instructions']);
            $stmt->bindParam(':source', $data['source']);
            $stmt->execute();
            
            $order_id = $this->conn->lastInsertId();
            
            // Insert order items
            foreach($data['items'] as $item) {
                $query = "INSERT INTO " . $this->order_items_table . " 
                          (order_id, menu_item_id, quantity, unit_price, subtotal, 
                           special_instructions, item_status)
                          VALUES 
                          (:order_id, :item_id, :qty, :price, :subtotal,
                           :instructions, :status)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':item_id', $item['menu_item_id']);
                $stmt->bindParam(':qty', $item['quantity']);
                $stmt->bindParam(':price', $item['unit_price']);
                $stmt->bindParam(':subtotal', $item['subtotal']);
                $stmt->bindParam(':instructions', $item['special_instructions']);
                $stmt->bindParam(':status', $item['item_status']);
                $stmt->execute();
                
                $item_id = $this->conn->lastInsertId();
                
                // Insert modifiers if any
                if(isset($item['modifiers']) && is_array($item['modifiers'])) {
                    foreach($item['modifiers'] as $modifier) {
                        $query = "INSERT INTO order_item_modifiers 
                                  (order_item_id, modifier_name, modifier_price)
                                  VALUES (:item_id, :name, :price)";
                        $stmt = $this->conn->prepare($query);
                        $stmt->bindParam(':item_id', $item_id);
                        $stmt->bindParam(':name', $modifier['name']);
                        $stmt->bindParam(':price', $modifier['price']);
                        $stmt->execute();
                    }
                }
            }
            
            $this->conn->commit();
            return $order_id;
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Order creation failed: " . $e->getMessage());
            return false;
        }
    }

    // Update order status
    public function updateOrderStatus($order_id, $status) {
        $query = "UPDATE " . $this->orders_table . " 
                  SET order_status = :status,
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $order_id);
        return $stmt->execute();
    }

    // Update item status
    public function updateItemStatus($item_id, $status) {
        $query = "UPDATE " . $this->order_items_table . " 
                  SET item_status = :status,
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $item_id);
        return $stmt->execute();
    }

    // Process payment
    public function processPayment($order_id, $payment_data) {
        $this->conn->beginTransaction();
        
        try {
            // Insert payment
            $payment_number = 'PAY' . date('Ymd') . rand(1000, 9999);
            
            $query = "INSERT INTO " . $this->payments_table . " 
                      (payment_number, order_id, payment_method_id, amount, 
                       tip_amount, status, reference_number, processed_by)
                      VALUES 
                      (:payment_number, :order_id, :method_id, :amount,
                       :tip, :status, :reference, :processed_by)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':payment_number', $payment_number);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':method_id', $payment_data['payment_method_id']);
            $stmt->bindParam(':amount', $payment_data['amount']);
            $stmt->bindParam(':tip', $payment_data['tip_amount']);
            $stmt->bindParam(':status', $payment_data['status']);
            $stmt->bindParam(':reference', $payment_data['reference_number']);
            $stmt->bindParam(':processed_by', $payment_data['processed_by']);
            $stmt->execute();
            
            // Update order payment status
            $query = "UPDATE " . $this->orders_table . " 
                      SET payment_status = 'paid',
                          order_status = 'completed',
                          completed_at = NOW()
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $order_id);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Payment processing failed: " . $e->getMessage());
            return false;
        }
    }

    // Get order statistics
    public function getStatistics() {
        $stats = [];
        
        // Active orders
        $query = "SELECT COUNT(*) as total FROM " . $this->orders_table . " 
                  WHERE order_status IN ('pending', 'confirmed', 'preparing')";
        $stmt = $this->conn->query($query);
        $stats['active_orders'] = $stmt->fetch()['total'];
        
        // Today's revenue
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total 
                  FROM " . $this->orders_table . " 
                  WHERE DATE(created_at) = CURDATE() 
                  AND payment_status = 'paid'";
        $stmt = $this->conn->query($query);
        $stats['today_revenue'] = $stmt->fetch()['total'];
        
        // Orders by type
        $query = "SELECT ot.type_name, COUNT(o.id) as count 
                  FROM " . $this->order_types_table . " ot
                  LEFT JOIN " . $this->orders_table . " o ON ot.id = o.order_type_id
                  WHERE DATE(o.created_at) = CURDATE()
                  GROUP BY ot.id";
        $stmt = $this->conn->query($query);
        $stats['orders_by_type'] = $stmt->fetchAll();
        
        return $stats;
    }
}
?>