<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\models\MenuModel.php
class MenuModel {
    private $conn;
    private $categories_table = "menu_categories";
    private $items_table = "menu_items";
    private $variations_table = "menu_item_variations";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getStatistics() {
        $stats = [];
        
        $query = "SELECT COUNT(*) as total FROM " . $this->items_table;
        $stmt = $this->conn->query($query);
        $stats['total_items'] = $stmt->fetch()['total'];
        
        $query = "SELECT COUNT(*) as total FROM " . $this->items_table . " WHERE is_available = 1 AND stock_quantity > 0";
        $stmt = $this->conn->query($query);
        $stats['available_items'] = $stmt->fetch()['total'];
        
        $query = "SELECT COUNT(*) as total FROM " . $this->items_table . " WHERE stock_quantity <= 0";
        $stmt = $this->conn->query($query);
        $stats['out_of_stock'] = $stmt->fetch()['total'];
        
        $query = "SELECT COUNT(*) as total FROM " . $this->items_table . " WHERE is_special = 1";
        $stmt = $this->conn->query($query);
        $stats['special_items'] = $stmt->fetch()['total'];
        
        $query = "SELECT COUNT(*) as total FROM " . $this->categories_table . " WHERE is_active = 1";
        $stmt = $this->conn->query($query);
        $stats['total_categories'] = $stmt->fetch()['total'];
        
        return $stats;
    }

    public function getCategories() {
        $query = "SELECT c.*, COUNT(i.id) as item_count 
                  FROM " . $this->categories_table . " c
                  LEFT JOIN " . $this->items_table . " i ON c.id = i.category_id
                  WHERE c.is_active = 1
                  GROUP BY c.id
                  ORDER BY c.display_order";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMenuItems($filters = []) {
        $query = "SELECT i.*, c.category_name 
                  FROM " . $this->items_table . " i
                  LEFT JOIN " . $this->categories_table . " c ON i.category_id = c.id
                  WHERE 1=1";
        
        $params = [];
        
        if(isset($filters['category_id'])) {
            $query .= " AND i.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        if(isset($filters['search'])) {
            $query .= " AND (i.item_name LIKE :search OR i.item_code LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $query .= " ORDER BY i.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMenuItem($id) {
        $query = "SELECT i.*, c.category_name 
                  FROM " . $this->items_table . " i
                  LEFT JOIN " . $this->categories_table . " c ON i.category_id = c.id
                  WHERE i.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function createMenuItem($data) {
        $query = "INSERT INTO " . $this->items_table . " 
                  (item_code, item_name, description, category_id, price, cost, 
                   stock_quantity, unit, is_available, is_special)
                  VALUES 
                  (:code, :name, :desc, :cat_id, :price, :cost, :stock, :unit, :available, :special)";
        
        $stmt = $this->conn->prepare($query);
        
        if(empty($data['item_code'])) {
            $data['item_code'] = $this->generateItemCode();
        }
        
        $stmt->bindParam(':code', $data['item_code']);
        $stmt->bindParam(':name', $data['item_name']);
        $stmt->bindParam(':desc', $data['description']);
        $stmt->bindParam(':cat_id', $data['category_id']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':cost', $data['cost']);
        $stmt->bindParam(':stock', $data['stock_quantity']);
        $stmt->bindParam(':unit', $data['unit']);
        $stmt->bindParam(':available', $data['is_available']);
        $stmt->bindParam(':special', $data['is_special']);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function updateMenuItem($id, $data) {
        $query = "UPDATE " . $this->items_table . " 
                  SET item_name = :name,
                      description = :desc,
                      category_id = :cat_id,
                      price = :price,
                      cost = :cost,
                      stock_quantity = :stock,
                      unit = :unit,
                      is_available = :available,
                      is_special = :special
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['item_name']);
        $stmt->bindParam(':desc', $data['description']);
        $stmt->bindParam(':cat_id', $data['category_id']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':cost', $data['cost']);
        $stmt->bindParam(':stock', $data['stock_quantity']);
        $stmt->bindParam(':unit', $data['unit']);
        $stmt->bindParam(':available', $data['is_available']);
        $stmt->bindParam(':special', $data['is_special']);
        
        return $stmt->execute();
    }

    public function deleteMenuItem($id) {
        $query = "DELETE FROM " . $this->items_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateStock($id, $quantity) {
        $query = "UPDATE " . $this->items_table . " SET stock_quantity = :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    private function generateItemCode() {
        $prefix = 'ITM';
        $query = "SELECT COUNT(*) as count FROM " . $this->items_table;
        $stmt = $this->conn->query($query);
        $count = $stmt->fetch()['count'] + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
?>