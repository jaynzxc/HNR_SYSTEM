<?php
// models/StaffModel.php
require_once __DIR__ . '/../includes/helpers.php';

class StaffModel {
    private $conn;
    private $staff_table = "staff_members";
    private $roles_table = "staff_roles";
    private $shifts_table = "staff_shifts";
    private $schedule_table = "staff_schedule";
    private $assignments_table = "table_assignments";
    private $performance_table = "staff_performance";
    private $attendance_table = "staff_attendance";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all staff roles
    public function getRoles() {
        $query = "SELECT * FROM " . $this->roles_table . " WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get all staff members with filters
    public function getStaffMembers($filters = []) {
        $query = "SELECT s.*, r.role_name,
                         (SELECT COUNT(*) FROM " . $this->assignments_table . " 
                          WHERE staff_id = s.id AND assignment_date = CURDATE()) as assigned_tables
                  FROM " . $this->staff_table . " s
                  LEFT JOIN " . $this->roles_table . " r ON s.role_id = r.id
                  WHERE 1=1";
        
        $params = [];
        
        if(isset($filters['status'])) {
            $query .= " AND s.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if(isset($filters['role'])) {
            $query .= " AND s.role_id = :role";
            $params[':role'] = $filters['role'];
        }
        
        if(isset($filters['search'])) {
            $query .= " AND (s.first_name LIKE :search OR s.last_name LIKE :search 
                           OR s.email LIKE :search OR s.phone LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $query .= " ORDER BY s.first_name, s.last_name";
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get single staff member
    public function getStaffMember($id) {
        $query = "SELECT s.*, r.role_name
                  FROM " . $this->staff_table . " s
                  LEFT JOIN " . $this->roles_table . " r ON s.role_id = r.id
                  WHERE s.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Create staff member
    public function createStaffMember($data) {
        // Generate staff code
        $staff_code = 'STF' . date('Ymd') . rand(100, 999);
        
        $query = "INSERT INTO " . $this->staff_table . " 
                  (staff_code, first_name, last_name, role_id, email, phone,
                   address, hire_date, emergency_contact_name, emergency_contact_phone,
                   status, employment_type, hourly_rate, notes)
                  VALUES 
                  (:code, :first_name, :last_name, :role_id, :email, :phone,
                   :address, :hire_date, :emergency_name, :emergency_phone,
                   :status, :emp_type, :rate, :notes)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $staff_code);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':role_id', $data['role_id']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':hire_date', $data['hire_date']);
        $stmt->bindParam(':emergency_name', $data['emergency_contact_name']);
        $stmt->bindParam(':emergency_phone', $data['emergency_contact_phone']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':emp_type', $data['employment_type']);
        $stmt->bindParam(':rate', $data['hourly_rate']);
        $stmt->bindParam(':notes', $data['notes']);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Update staff member
    public function updateStaffMember($id, $data) {
        $query = "UPDATE " . $this->staff_table . " 
                  SET first_name = :first_name,
                      last_name = :last_name,
                      role_id = :role_id,
                      email = :email,
                      phone = :phone,
                      address = :address,
                      emergency_contact_name = :emergency_name,
                      emergency_contact_phone = :emergency_phone,
                      status = :status,
                      employment_type = :emp_type,
                      hourly_rate = :rate,
                      notes = :notes
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':role_id', $data['role_id']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':emergency_name', $data['emergency_contact_name']);
        $stmt->bindParam(':emergency_phone', $data['emergency_contact_phone']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':emp_type', $data['employment_type']);
        $stmt->bindParam(':rate', $data['hourly_rate']);
        $stmt->bindParam(':notes', $data['notes']);
        
        return $stmt->execute();
    }

    // Delete staff member
    public function deleteStaffMember($id) {
        $query = "DELETE FROM " . $this->staff_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Get all shifts
    public function getShifts() {
        $query = "SELECT * FROM " . $this->shifts_table . " ORDER BY start_time";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get staff schedule
    public function getSchedule($date = null) {
        if(!$date) {
            $date = date('Y-m-d');
        }
        
        $query = "SELECT ss.*, s.first_name, s.last_name, s.role_id,
                         r.role_name, sh.shift_name
                  FROM " . $this->schedule_table . " ss
                  JOIN " . $this->staff_table . " s ON ss.staff_id = s.id
                  LEFT JOIN " . $this->roles_table . " r ON s.role_id = r.id
                  LEFT JOIN " . $this->shifts_table . " sh ON ss.shift_id = sh.id
                  WHERE ss.schedule_date = :date
                  ORDER BY ss.start_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Create schedule
    public function createSchedule($data) {
        $query = "INSERT INTO " . $this->schedule_table . " 
                  (staff_id, shift_id, schedule_date, start_time, end_time,
                   break_start, break_end, status, notes)
                  VALUES 
                  (:staff_id, :shift_id, :date, :start_time, :end_time,
                   :break_start, :break_end, :status, :notes)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':staff_id', $data['staff_id']);
        $stmt->bindParam(':shift_id', $data['shift_id']);
        $stmt->bindParam(':date', $data['schedule_date']);
        $stmt->bindParam(':start_time', $data['start_time']);
        $stmt->bindParam(':end_time', $data['end_time']);
        $stmt->bindParam(':break_start', $data['break_start']);
        $stmt->bindParam(':break_end', $data['break_end']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':notes', $data['notes']);
        
        return $stmt->execute();
    }

    // Update schedule status (check-in/out)
    public function updateScheduleStatus($schedule_id, $status) {
        $query = "UPDATE " . $this->schedule_table . " 
                  SET status = :status";
        
        if($status === 'present') {
            $query .= ", check_in_time = NOW()";
        } elseif($status === 'completed') {
            $query .= ", check_out_time = NOW()";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $schedule_id);
        return $stmt->execute();
    }

    // Table assignments
    public function getTableAssignments($date = null) {
        if(!$date) {
            $date = date('Y-m-d');
        }
        
        $query = "SELECT ta.*, s.first_name, s.last_name, s.role_id,
                         r.role_name, t.table_number
                  FROM " . $this->assignments_table . " ta
                  JOIN " . $this->staff_table . " s ON ta.staff_id = s.id
                  LEFT JOIN " . $this->roles_table . " r ON s.role_id = r.id
                  JOIN " . $this->tables_table . " t ON ta.table_id = t.id
                  WHERE ta.assignment_date = :date AND ta.is_active = 1
                  ORDER BY t.table_number";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function assignTable($data) {
        // Check if already assigned
        $query = "SELECT id FROM " . $this->assignments_table . " 
                  WHERE staff_id = :staff_id AND table_id = :table_id
                  AND assignment_date = :date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':staff_id', $data['staff_id']);
        $stmt->bindParam(':table_id', $data['table_id']);
        $stmt->bindParam(':date', $data['assignment_date']);
        $stmt->execute();
        
        if($stmt->fetch()) {
            return false; // Already assigned
        }
        
        $query = "INSERT INTO " . $this->assignments_table . " 
                  (staff_id, table_id, assignment_date, shift_id, is_active)
                  VALUES 
                  (:staff_id, :table_id, :date, :shift_id, 1)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':staff_id', $data['staff_id']);
        $stmt->bindParam(':table_id', $data['table_id']);
        $stmt->bindParam(':date', $data['assignment_date']);
        $stmt->bindParam(':shift_id', $data['shift_id']);
        
        return $stmt->execute();
    }

    public function removeTableAssignment($assignment_id) {
        $query = "DELETE FROM " . $this->assignments_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $assignment_id);
        return $stmt->execute();
    }

    // Performance ratings
    public function addPerformanceRating($data) {
        $query = "INSERT INTO " . $this->performance_table . " 
                  (staff_id, rating_date, rated_by, customer_service_rating,
                   speed_rating, accuracy_rating, overall_rating, comments)
                  VALUES 
                  (:staff_id, CURDATE(), :rated_by, :service_rating,
                   :speed_rating, :accuracy_rating, :overall_rating, :comments)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':staff_id', $data['staff_id']);
        $stmt->bindParam(':rated_by', $data['rated_by']);
        $stmt->bindParam(':service_rating', $data['customer_service_rating']);
        $stmt->bindParam(':speed_rating', $data['speed_rating']);
        $stmt->bindParam(':accuracy_rating', $data['accuracy_rating']);
        $stmt->bindParam(':overall_rating', $data['overall_rating']);
        $stmt->bindParam(':comments', $data['comments']);
        
        return $stmt->execute();
    }

    public function getStaffPerformance($staff_id = null, $period = 'week') {
        $date_filter = "";
        if($period === 'week') {
            $date_filter = "AND rating_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        } elseif($period === 'month') {
            $date_filter = "AND rating_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        }
        
        $query = "SELECT sp.*, s.first_name, s.last_name, s.role_id,
                         r.role_name, rated.first_name as rated_by_name
                  FROM " . $this->performance_table . " sp
                  JOIN " . $this->staff_table . " s ON sp.staff_id = s.id
                  LEFT JOIN " . $this->roles_table . " r ON s.role_id = r.id
                  LEFT JOIN " . $this->staff_table . " rated ON sp.rated_by = rated.id
                  WHERE 1=1 " . $date_filter;
        
        if($staff_id) {
            $query .= " AND sp.staff_id = :staff_id";
        }
        
        $query .= " ORDER BY sp.rating_date DESC";
        
        $stmt = $this->conn->prepare($query);
        if($staff_id) {
            $stmt->bindParam(':staff_id', $staff_id);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Attendance tracking
    public function recordAttendance($data) {
        $query = "INSERT INTO " . $this->attendance_table . " 
                  (staff_id, attendance_date, check_in_time, status, notes)
                  VALUES 
                  (:staff_id, CURDATE(), NOW(), :status, :notes)
                  ON DUPLICATE KEY UPDATE
                  check_out_time = NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':staff_id', $data['staff_id']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':notes', $data['notes']);
        
        return $stmt->execute();
    }

    public function getAttendance($date = null) {
        if(!$date) {
            $date = date('Y-m-d');
        }
        
        $query = "SELECT a.*, s.first_name, s.last_name, s.role_id, r.role_name
                  FROM " . $this->attendance_table . " a
                  JOIN " . $this->staff_table . " s ON a.staff_id = s.id
                  LEFT JOIN " . $this->roles_table . " r ON s.role_id = r.id
                  WHERE a.attendance_date = :date
                  ORDER BY s.first_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get staff statistics
    public function getStatistics() {
        $stats = [];
        
        // Total staff
        $query = "SELECT COUNT(*) as total FROM " . $this->staff_table . " 
                  WHERE status = 'active'";
        $stmt = $this->conn->query($query);
        $stats['total_staff'] = $stmt->fetch()['total'];
        
        // On duty today
        $query = "SELECT COUNT(DISTINCT staff_id) as total 
                  FROM " . $this->schedule_table . " 
                  WHERE schedule_date = CURDATE() 
                  AND status IN ('scheduled', 'present')";
        $stmt = $this->conn->query($query);
        $stats['on_duty'] = $stmt->fetch()['total'];
        
        // On break
        $query = "SELECT COUNT(*) as total FROM " . $this->schedule_table . " 
                  WHERE schedule_date = CURDATE() 
                  AND status = 'on_break'";
        $stmt = $this->conn->query($query);
        $stats['on_break'] = $stmt->fetch()['total'];
        
        // Off duty
        $query = "SELECT COUNT(*) as total FROM " . $this->staff_table . " 
                  WHERE status = 'active'
                  AND id NOT IN (
                      SELECT staff_id FROM " . $this->schedule_table . " 
                      WHERE schedule_date = CURDATE()
                  )";
        $stmt = $this->conn->query($query);
        $stats['off_duty'] = $stmt->fetch()['total'];
        
        // Assigned tables
        $query = "SELECT COUNT(*) as total FROM " . $this->assignments_table . " 
                  WHERE assignment_date = CURDATE() AND is_active = 1";
        $stmt = $this->conn->query($query);
        $stats['assigned_tables'] = $stmt->fetch()['total'];
        
        return $stats;
    }
}
?>