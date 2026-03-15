<?php
// models/KitchenModel.php
require_once __DIR__ . '/../includes/helpers.php';

class KitchenModel {
    private $conn;
    private $tickets_table = "kitchen_tickets";
    private $ticket_items_table = "kitchen_ticket_items";
    private $stations_table = "kitchen_stations";
    private $orders_table = "orders";
    private $order_items_table = "order_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get kitchen stations
    public function getStations() {
        $query = "SELECT * FROM " . $this->stations_table . " WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get all kitchen tickets with filters
    public function getTickets($filters = []) {
        $query = "SELECT kt.*, ks.station_name, o.order_number,
                         o.table_id, t.table_number, o.guest_name
                  FROM " . $this->tickets_table . " kt
                  LEFT JOIN " . $this->stations_table . " ks ON kt.station_id = ks.id
                  LEFT JOIN " . $this->orders_table . " o ON kt.order_id = o.id
                  LEFT JOIN restaurant_tables t ON o.table_id = t.id
                  WHERE 1=1";
        
        $params = [];
        
        if(isset($filters['status'])) {
            $query .= " AND kt.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if(isset($filters['station'])) {
            $query .= " AND kt.station_id = :station";
            $params[':station'] = $filters['station'];
        }
        
        if(isset($filters['priority'])) {
            $query .= " AND kt.priority = :priority";
            $params[':priority'] = $filters['priority'];
        }
        
        $query .= " ORDER BY 
                    CASE kt.priority 
                        WHEN 'urgent' THEN 1 
                        WHEN 'high' THEN 2 
                        ELSE 3 
                    END,
                    kt.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get single ticket with items
    public function getTicket($id) {
        $query = "SELECT kt.*, ks.station_name, o.order_number,
                         o.table_id, t.table_number, o.guest_name
                  FROM " . $this->tickets_table . " kt
                  LEFT JOIN " . $this->stations_table . " ks ON kt.station_id = ks.id
                  LEFT JOIN " . $this->orders_table . " o ON kt.order_id = o.id
                  LEFT JOIN restaurant_tables t ON o.table_id = t.id
                  WHERE kt.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $ticket = $stmt->fetch();
        
        if($ticket) {
            $ticket['items'] = $this->getTicketItems($id);
        }
        
        return $ticket;
    }

    // Get ticket items
    public function getTicketItems($ticket_id) {
        $query = "SELECT * FROM " . $this->ticket_items_table . " 
                  WHERE kitchen_ticket_id = :ticket_id
                  ORDER BY id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':ticket_id', $ticket_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Create kitchen ticket from order
    public function createTicket($order_id, $station_id = null) {
        $this->conn->beginTransaction();
        
        try {
            // Get order details
            $query = "SELECT o.*, oi.id as order_item_id, oi.menu_item_id, 
                             oi.quantity, oi.special_instructions,
                             mi.item_name, mi.preparation_time
                      FROM " . $this->orders_table . " o
                      JOIN " . $this->order_items_table . " oi ON o.id = oi.order_id
                      JOIN menu_items mi ON oi.menu_item_id = mi.id
                      WHERE o.id = :order_id AND oi.item_status = 'pending'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();
            $items = $stmt->fetchAll();
            
            if(empty($items)) {
                return false;
            }
            
            // Generate ticket number
            $ticket_number = generateTicketNumber();
            
            // Determine priority based on order
            $priority = 'normal';
            if(isset($items[0]['special_instructions']) && 
               stripos($items[0]['special_instructions'], 'urgent') !== false) {
                $priority = 'urgent';
            }
            
            // Insert ticket
            $query = "INSERT INTO " . $this->tickets_table . " 
                      (ticket_number, order_id, station_id, priority, status)
                      VALUES (:ticket_number, :order_id, :station_id, :priority, 'pending')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':ticket_number', $ticket_number);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':station_id', $station_id);
            $stmt->bindParam(':priority', $priority);
            $stmt->execute();
            
            $ticket_id = $this->conn->lastInsertId();
            
            // Insert ticket items
            foreach($items as $item) {
                $query = "INSERT INTO " . $this->ticket_items_table . " 
                          (kitchen_ticket_id, order_item_id, menu_item_name, 
                           quantity, special_instructions, status)
                          VALUES 
                          (:ticket_id, :order_item_id, :item_name,
                           :quantity, :instructions, 'pending')";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':ticket_id', $ticket_id);
                $stmt->bindParam(':order_item_id', $item['order_item_id']);
                $stmt->bindParam(':item_name', $item['item_name']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':instructions', $item['special_instructions']);
                $stmt->execute();
            }
            
            // Update order kitchen status
            $query = "UPDATE " . $this->orders_table . " 
                      SET kitchen_status = 'preparing'
                      WHERE id = :order_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();
            
            $this->conn->commit();
            return $ticket_id;
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Ticket creation failed: " . $e->getMessage());
            return false;
        }
    }

    // Update ticket status
    public function updateTicketStatus($ticket_id, $status) {
        $this->conn->beginTransaction();
        
        try {
            $completion_time = null;
            if($status === 'ready' || $status === 'served') {
                $completion_time = date('Y-m-d H:i:s');
            }
            
            $query = "UPDATE " . $this->tickets_table . " 
                      SET status = :status,
                          completion_time = :completion_time,
                          updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':completion_time', $completion_time);
            $stmt->bindParam(':id', $ticket_id);
            $stmt->execute();
            
            // Update related order items status
            if($status === 'preparing') {
                $query = "UPDATE " . $this->order_items_table . " oi
                          JOIN " . $this->ticket_items_table . " ti ON oi.id = ti.order_item_id
                          SET oi.item_status = 'preparing'
                          WHERE ti.kitchen_ticket_id = :ticket_id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':ticket_id', $ticket_id);
                $stmt->execute();
                
            } elseif($status === 'ready') {
                $query = "UPDATE " . $this->order_items_table . " oi
                          JOIN " . $this->ticket_items_table . " ti ON oi.id = ti.order_item_id
                          SET oi.item_status = 'ready'
                          WHERE ti.kitchen_ticket_id = :ticket_id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':ticket_id', $ticket_id);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return true;
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Ticket status update failed: " . $e->getMessage());
            return false;
        }
    }

    // Update ticket item status
    public function updateTicketItemStatus($item_id, $status) {
        $query = "UPDATE " . $this->ticket_items_table . " 
                  SET status = :status,
                      completed_at = CASE 
                          WHEN :status IN ('ready', 'cancelled') THEN NOW()
                          ELSE NULL
                      END
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $item_id);
        return $stmt->execute();
    }

    // Get kitchen statistics
    public function getStatistics() {
        $stats = [];
        
        // New orders (pending tickets)
        $query = "SELECT COUNT(*) as total FROM " . $this->tickets_table . " 
                  WHERE status = 'pending'";
        $stmt = $this->conn->query($query);
        $stats['new_orders'] = $stmt->fetch()['total'];
        
        // Preparing orders
        $query = "SELECT COUNT(*) as total FROM " . $this->tickets_table . " 
                  WHERE status = 'preparing'";
        $stmt = $this->conn->query($query);
        $stats['preparing_orders'] = $stmt->fetch()['total'];
        
        // Ready orders
        $query = "SELECT COUNT(*) as total FROM " . $this->tickets_table . " 
                  WHERE status = 'ready'";
        $stmt = $this->conn->query($query);
        $stats['ready_orders'] = $stmt->fetch()['total'];
        
        // Urgent orders
        $query = "SELECT COUNT(*) as total FROM " . $this->tickets_table . " 
                  WHERE priority = 'urgent' AND status IN ('pending', 'preparing')";
        $stmt = $this->conn->query($query);
        $stats['urgent_orders'] = $stmt->fetch()['total'];
        
        // Average preparation time
        $query = "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, completion_time)) as avg_time
                  FROM " . $this->tickets_table . " 
                  WHERE completion_time IS NOT NULL
                  AND DATE(created_at) = CURDATE()";
        $stmt = $this->conn->query($query);
        $stats['avg_prep_time'] = round($stmt->fetch()['avg_time'] ?? 0);
        
        return $stats;
    }
}
?>