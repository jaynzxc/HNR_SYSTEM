<?php
// models/ReservationModel.php
require_once __DIR__ . '/../includes/helpers.php'; 

class ReservationModel {
    private $conn;
    private $reservations_table = "table_reservations";
    private $tables_table = "restaurant_tables";
    private $waitlist_table = "waitlist";
    private $customers_table = "customers";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all tables
    public function getTables($filters = []) {
        $query = "SELECT * FROM " . $this->tables_table . " WHERE is_active = 1";
        
        if(isset($filters['section'])) {
            $query .= " AND section = :section";
        }
        
        if(isset($filters['status'])) {
            $query .= " AND status = :status";
        }
        
        if(isset($filters['capacity'])) {
            $query .= " AND capacity >= :capacity";
        }
        
        $query .= " ORDER BY table_number";
        
        $stmt = $this->conn->prepare($query);
        
        if(isset($filters['section'])) {
            $stmt->bindParam(':section', $filters['section']);
        }
        if(isset($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        if(isset($filters['capacity'])) {
            $stmt->bindParam(':capacity', $filters['capacity']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get single table
    public function getTable($id) {
        $query = "SELECT * FROM " . $this->tables_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Update table status
    public function updateTableStatus($table_id, $status) {
        $query = "UPDATE " . $this->tables_table . " 
                  SET status = :status,
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $table_id);
        return $stmt->execute();
    }

    // Get all reservations with filters
    public function getReservations($filters = []) {
        $query = "SELECT r.*, t.table_number, t.capacity,
                         CONCAT(c.first_name, ' ', c.last_name) as customer_name
                  FROM " . $this->reservations_table . " r
                  LEFT JOIN " . $this->tables_table . " t ON r.table_id = t.id
                  LEFT JOIN " . $this->customers_table . " c ON r.customer_id = c.id
                  WHERE 1=1";
        
        $params = [];
        
        if(isset($filters['date'])) {
            $query .= " AND r.reservation_date = :date";
            $params[':date'] = $filters['date'];
        }
        
        if(isset($filters['status'])) {
            $query .= " AND r.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if(isset($filters['search'])) {
            $query .= " AND (r.guest_name LIKE :search OR r.guest_phone LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $query .= " ORDER BY r.reservation_date, r.reservation_time";
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get single reservation
    public function getReservation($id) {
        $query = "SELECT r.*, t.table_number, t.capacity,
                         CONCAT(c.first_name, ' ', c.last_name) as customer_name
                  FROM " . $this->reservations_table . " r
                  LEFT JOIN " . $this->tables_table . " t ON r.table_id = t.id
                  LEFT JOIN " . $this->customers_table . " c ON r.customer_id = c.id
                  WHERE r.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Create new reservation
    public function createReservation($data) {
        $this->conn->beginTransaction();
        
        try {
            // Check table availability
            if(isset($data['table_id']) && $data['table_id']) {
                $available = $this->checkTableAvailability(
                    $data['table_id'],
                    $data['reservation_date'],
                    $data['reservation_time'],
                    $data['end_time'] ?? null
                );
                
                if(!$available) {
                    throw new Exception("Table is not available at the selected time");
                }
            }
            
            // Generate reservation number
            $reservation_number = generateReservationNumber();
            
            // Handle customer
            $customer_id = $data['customer_id'] ?? null;
            if(!$customer_id && !empty($data['guest_phone'])) {
                $customer_id = $this->findOrCreateCustomer($data);
            }
            
            // Insert reservation
            $query = "INSERT INTO " . $this->reservations_table . " 
                      (reservation_number, customer_id, guest_name, guest_phone, 
                       guest_email, table_id, reservation_date, reservation_time,
                       end_time, number_of_guests, status, source, special_requests,
                       occasion, is_walk_in, estimated_wait_time)
                      VALUES 
                      (:res_number, :customer_id, :guest_name, :guest_phone,
                       :guest_email, :table_id, :res_date, :res_time,
                       :end_time, :guests, :status, :source, :requests,
                       :occasion, :walk_in, :wait_time)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':res_number', $reservation_number);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':guest_name', $data['guest_name']);
            $stmt->bindParam(':guest_phone', $data['guest_phone']);
            $stmt->bindParam(':guest_email', $data['guest_email']);
            $stmt->bindParam(':table_id', $data['table_id']);
            $stmt->bindParam(':res_date', $data['reservation_date']);
            $stmt->bindParam(':res_time', $data['reservation_time']);
            $stmt->bindParam(':end_time', $data['end_time']);
            $stmt->bindParam(':guests', $data['number_of_guests']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':source', $data['source']);
            $stmt->bindParam(':requests', $data['special_requests']);
            $stmt->bindParam(':occasion', $data['occasion']);
            $stmt->bindParam(':walk_in', $data['is_walk_in']);
            $stmt->bindParam(':wait_time', $data['estimated_wait_time']);
            $stmt->execute();
            
            $reservation_id = $this->conn->lastInsertId();
            
            // If table assigned, update table status
            if(isset($data['table_id']) && $data['table_id']) {
                $this->updateTableStatus($data['table_id'], 'reserved');
            }
            
            $this->conn->commit();
            return $reservation_id;
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Reservation creation failed: " . $e->getMessage());
            return false;
        }
    }

    // Update reservation status
    public function updateReservationStatus($reservation_id, $status) {
        $this->conn->beginTransaction();
        
        try {
            // Get current reservation
            $query = "SELECT * FROM " . $this->reservations_table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $reservation_id);
            $stmt->execute();
            $reservation = $stmt->fetch();
            
            if(!$reservation) {
                throw new Exception("Reservation not found");
            }
            
            // Update reservation status
            $query = "UPDATE " . $this->reservations_table . " 
                      SET status = :status,
                          updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $reservation_id);
            $stmt->execute();
            
            // Update table status if needed
            if($reservation['table_id']) {
                if($status === 'seated') {
                    $this->updateTableStatus($reservation['table_id'], 'occupied');
                } elseif($status === 'completed' || $status === 'cancelled' || $status === 'no_show') {
                    $this->updateTableStatus($reservation['table_id'], 'available');
                }
            }
            
            $this->conn->commit();
            return true;
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Reservation status update failed: " . $e->getMessage());
            return false;
        }
    }

    // Check table availability
    public function checkTableAvailability($table_id, $date, $time, $end_time = null) {
        if(!$end_time) {
            // Default 2-hour reservation
            $end_time = date('H:i:s', strtotime($time) + 7200);
        }
        
        $query = "SELECT COUNT(*) as conflicts FROM " . $this->reservations_table . " 
                  WHERE table_id = :table_id 
                  AND reservation_date = :date
                  AND status IN ('confirmed', 'seated')
                  AND (
                      (reservation_time <= :end_time AND 
                       COALESCE(end_time, DATE_ADD(reservation_time, INTERVAL 2 HOUR)) >= :time)
                  )";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':table_id', $table_id);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->execute();
        
        return $stmt->fetch()['conflicts'] == 0;
    }

    // Get available tables for a time slot
    public function getAvailableTables($date, $time, $guests) {
        $end_time = date('H:i:s', strtotime($time) + 7200);
        
        $query = "SELECT t.* FROM " . $this->tables_table . " t
                  WHERE t.is_active = 1
                  AND t.capacity >= :guests
                  AND t.id NOT IN (
                      SELECT table_id FROM " . $this->reservations_table . " 
                      WHERE reservation_date = :date
                      AND status IN ('confirmed', 'seated')
                      AND (
                          (reservation_time <= :end_time AND 
                           COALESCE(end_time, DATE_ADD(reservation_time, INTERVAL 2 HOUR)) >= :time)
                      )
                  )
                  ORDER BY t.capacity";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->bindParam(':guests', $guests);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Waitlist management
    public function addToWaitlist($data) {
        $query = "INSERT INTO " . $this->waitlist_table . " 
                  (customer_id, guest_name, guest_phone, number_of_guests,
                   requested_section, check_in_time, estimated_wait_time, status)
                  VALUES 
                  (:customer_id, :guest_name, :guest_phone, :guests,
                   :section, NOW(), :wait_time, 'waiting')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':customer_id', $data['customer_id']);
        $stmt->bindParam(':guest_name', $data['guest_name']);
        $stmt->bindParam(':guest_phone', $data['guest_phone']);
        $stmt->bindParam(':guests', $data['number_of_guests']);
        $stmt->bindParam(':section', $data['requested_section']);
        $stmt->bindParam(':wait_time', $data['estimated_wait_time']);
        
        return $stmt->execute();
    }

    public function getWaitlist() {
        $query = "SELECT w.*, 
                         CONCAT(c.first_name, ' ', c.last_name) as customer_name
                  FROM " . $this->waitlist_table . " w
                  LEFT JOIN " . $this->customers_table . " c ON w.customer_id = c.id
                  WHERE w.status = 'waiting'
                  ORDER BY w.check_in_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Helper: Find or create customer
    private function findOrCreateCustomer($data) {
        // Check if customer exists by phone
        $query = "SELECT id FROM " . $this->customers_table . " 
                  WHERE phone = :phone";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':phone', $data['guest_phone']);
        $stmt->execute();
        $customer = $stmt->fetch();
        
        if($customer) {
            return $customer['id'];
        }
        
        // Create new customer
        $name_parts = explode(' ', $data['guest_name'], 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';
        
        $query = "INSERT INTO " . $this->customers_table . " 
                  (customer_code, first_name, last_name, email, phone)
                  VALUES 
                  (:code, :first_name, :last_name, :email, :phone)";
        
        $code = 'CUST' . date('Ymd') . rand(100, 999);
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $data['guest_email']);
        $stmt->bindParam(':phone', $data['guest_phone']);
        $stmt->execute();
        
        return $this->conn->lastInsertId();
    }

    // Get reservation statistics
    public function getStatistics() {
        $stats = [];
        
        // Today's reservations
        $query = "SELECT COUNT(*) as total FROM " . $this->reservations_table . " 
                  WHERE reservation_date = CURDATE()";
        $stmt = $this->conn->query($query);
        $stats['today_reservations'] = $stmt->fetch()['total'];
        
        // Total guests today
        $query = "SELECT COALESCE(SUM(number_of_guests), 0) as total 
                  FROM " . $this->reservations_table . " 
                  WHERE reservation_date = CURDATE()";
        $stmt = $this->conn->query($query);
        $stats['total_guests'] = $stmt->fetch()['total'];
        
        // Available tables
        $query = "SELECT COUNT(*) as total FROM " . $this->tables_table . " 
                  WHERE status = 'available' AND is_active = 1";
        $stmt = $this->conn->query($query);
        $stats['available_tables'] = $stmt->fetch()['total'];
        
        // Walk-ins today
        $query = "SELECT COUNT(*) as total FROM " . $this->reservations_table . " 
                  WHERE reservation_date = CURDATE() AND is_walk_in = 1";
        $stmt = $this->conn->query($query);
        $stats['walk_ins'] = $stmt->fetch()['total'];
        
        // Waitlist count
        $query = "SELECT COUNT(*) as total FROM " . $this->waitlist_table . " 
                  WHERE status = 'waiting'";
        $stmt = $this->conn->query($query);
        $stats['waitlist_count'] = $stmt->fetch()['total'];
        
        return $stats;
    }
}
?>