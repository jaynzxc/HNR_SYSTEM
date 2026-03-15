<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\models\MenuModel.php
require_once __DIR__ . '/../includes/helpers.php'; 

class MenuModel {
    private $conn;
    private $categories_table = "menu_categories";
    private $items_table = "menu_items";
    private $variations_table = "menu_item_variations";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ==================== CATEGORY METHODS ====================

    public function getCategories($active_only = true) {
        $query = "SELECT c.*, COUNT(i.id) as item_count 
                  FROM " . $this->categories_table . " c
                  LEFT JOIN " . $this->items_table . " i ON c.id = i.category_id
                  WHERE 1=1";
        
        if($active_only) {
            $query .= " AND c.is_active = 1";
        }
        
        $query .= " GROUP BY c.id ORDER BY c.display_order";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategory($id) {
        $query = "SELECT * FROM " . $this->categories_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function createCategory($data) {
        $query = "INSERT INTO " . $this->categories_table . " 
                  (category_name, category_description, display_order, is_active)
                  VALUES (:name, :desc, :order, :active)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $data['category_name']);
        $stmt->bindParam(':desc', $data['category_description']);
        $stmt->bindParam(':order', $data['display_order']);
        $stmt->bindParam(':active', $data['is_active']);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function updateCategory($id, $data) {
        $query = "UPDATE " . $this->categories_table . " 
                  SET category_name = :name,
                      category_description = :desc,
                      display_order = :order,
                      is_active = :active
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['category_name']);
        $stmt->bindParam(':desc', $data['category_description']);
        $stmt->bindParam(':order', $data['display_order']);
        $stmt->bindParam(':active', $data['is_active']);
        
        return $stmt->execute();
    }

    public function deleteCategory($id) {
        // Check if category has items
        $query = "SELECT COUNT(*) as count FROM " . $this->items_table . " WHERE category_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $count = $stmt->fetch()['count'];
        
        if($count > 0) {
            return false; // Cannot delete category with items
        }
        
        $query = "DELETE FROM " . $this->categories_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ==================== MENU ITEM METHODS ====================

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
        
        if(isset($filters['is_available'])) {
            $query .= " AND i.is_available = :is_available";
            $params[':is_available'] = $filters['is_available'];
        }
        
        if(isset($filters['is_special'])) {
            $query .= " AND i.is_special = :is_special";
            $params[':is_special'] = $filters['is_special'];
        }
        
        if(isset($filters['search'])) {
            $query .= " AND (i.item_name LIKE :search OR i.item_code LIKE :search OR i.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        if(isset($filters['low_stock'])) {
            $query .= " AND i.stock_quantity <= i.low_stock_threshold";
        }
        
        $query .= " ORDER BY i.category_id, i.item_name";
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMenuItem($id) {
        $query = "SELECT i.*, c.category_name,
                         (SELECT JSON_ARRAYAGG(
                             JSON_OBJECT('id', v.id, 'name', v.variation_name, 'price', v.price_adjustment)
                         ) FROM " . $this->variations_table . " v WHERE v.menu_item_id = i.id) as variations
                  FROM " . $this->items_table . " i
                  LEFT JOIN " . $this->categories_table . " c ON i.category_id = c.id
                  WHERE i.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $item = $stmt->fetch();
        
        if($item && $item['variations']) {
            $item['variations'] = json_decode($item['variations'], true);
        }
        
        return $item;
    }

    public function createMenuItem($data) {
        $query = "INSERT INTO " . $this->items_table . " 
                  (item_code, item_name, description, category_id, price, cost, 
                   stock_quantity, low_stock_threshold, unit, image_url, 
                   is_available, is_special, is_featured, preparation_time, 
                   calories, allergens, tags)
                  VALUES 
                  (:code, :name, :desc, :cat_id, :price, :cost, :stock, :threshold, 
                   :unit, :image, :available, :special, :featured, :prep_time, 
                   :calories, :allergens, :tags)";
        
        $stmt = $this->conn->prepare($query);
        
        // Generate item code if not provided
        if(empty($data['item_code'])) {
            $data['item_code'] = $this->generateItemCode();
        }
        
        // Set defaults
        $data['description'] = $data['description'] ?? '';
        $data['cost'] = $data['cost'] ?? 0;
        $data['stock_quantity'] = $data['stock_quantity'] ?? 0;
        $data['low_stock_threshold'] = $data['low_stock_threshold'] ?? 5;
        $data['unit'] = $data['unit'] ?? 'piece';
        $data['image_url'] = $data['image_url'] ?? '';
        $data['is_available'] = $data['is_available'] ?? 1;
        $data['is_special'] = $data['is_special'] ?? 0;
        $data['is_featured'] = $data['is_featured'] ?? 0;
        $data['preparation_time'] = $data['preparation_time'] ?? null;
        $data['calories'] = $data['calories'] ?? null;
        $data['allergens'] = $data['allergens'] ?? '';
        $data['tags'] = isset($data['tags']) ? json_encode($data['tags']) : null;
        
        $stmt->bindParam(':code', $data['item_code']);
        $stmt->bindParam(':name', $data['item_name']);
        $stmt->bindParam(':desc', $data['description']);
        $stmt->bindParam(':cat_id', $data['category_id']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':cost', $data['cost']);
        $stmt->bindParam(':stock', $data['stock_quantity']);
        $stmt->bindParam(':threshold', $data['low_stock_threshold']);
        $stmt->bindParam(':unit', $data['unit']);
        $stmt->bindParam(':image', $data['image_url']);
        $stmt->bindParam(':available', $data['is_available']);
        $stmt->bindParam(':special', $data['is_special']);
        $stmt->bindParam(':featured', $data['is_featured']);
        $stmt->bindParam(':prep_time', $data['preparation_time']);
        $stmt->bindParam(':calories', $data['calories']);
        $stmt->bindParam(':allergens', $data['allergens']);
        $stmt->bindParam(':tags', $data['tags']);
        
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
                      low_stock_threshold = :threshold,
                      unit = :unit,
                      image_url = :image,
                      is_available = :available,
                      is_special = :special,
                      is_featured = :featured,
                      preparation_time = :prep_time,
                      calories = :calories,
                      allergens = :allergens,
                      tags = :tags
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['item_name']);
        $stmt->bindParam(':desc', $data['description']);
        $stmt->bindParam(':cat_id', $data['category_id']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':cost', $data['cost']);
        $stmt->bindParam(':stock', $data['stock_quantity']);
        $stmt->bindParam(':threshold', $data['low_stock_threshold']);
        $stmt->bindParam(':unit', $data['unit']);
        $stmt->bindParam(':image', $data['image_url']);
        $stmt->bindParam(':available', $data['is_available']);
        $stmt->bindParam(':special', $data['is_special']);
        $stmt->bindParam(':featured', $data['is_featured']);
        $stmt->bindParam(':prep_time', $data['preparation_time']);
        $stmt->bindParam(':calories', $data['calories']);
        $stmt->bindParam(':allergens', $data['allergens']);
        $stmt->bindParam(':tags', json_encode($data['tags'] ?? []));
        
        return $stmt->execute();
    }

    public function deleteMenuItem($id) {
        $query = "DELETE FROM " . $this->items_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateStock($id, $quantity, $type = 'set') {
        if($type === 'add') {
            $query = "UPDATE " . $this->items_table . " 
                      SET stock_quantity = stock_quantity + :quantity 
                      WHERE id = :id";
        } elseif($type === 'subtract') {
            $query = "UPDATE " . $this->items_table . " 
                      SET stock_quantity = stock_quantity - :quantity 
                      WHERE id = :id AND stock_quantity >= :quantity";
        } else {
            $query = "UPDATE " . $this->items_table . " 
                      SET stock_quantity = :quantity 
                      WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ==================== VARIATION METHODS ====================

    public function getVariations($item_id) {
        $query = "SELECT * FROM " . $this->variations_table . " 
                  WHERE menu_item_id = :item_id ORDER BY price_adjustment";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':item_id', $item_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addVariation($data) {
        $query = "INSERT INTO " . $this->variations_table . " 
                  (menu_item_id, variation_name, price_adjustment, is_available)
                  VALUES (:item_id, :name, :price, :available)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':item_id', $data['menu_item_id']);
        $stmt->bindParam(':name', $data['variation_name']);
        $stmt->bindParam(':price', $data['price_adjustment']);
        $stmt->bindParam(':available', $data['is_available']);
        
        return $stmt->execute();
    }

    public function updateVariation($id, $data) {
        $query = "UPDATE " . $this->variations_table . " 
                  SET variation_name = :name,
                      price_adjustment = :price,
                      is_available = :available
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['variation_name']);
        $stmt->bindParam(':price', $data['price_adjustment']);
        $stmt->bindParam(':available', $data['is_available']);
        
        return $stmt->execute();
    }

    public function deleteVariation($id) {
        $query = "DELETE FROM " . $this->variations_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ==================== STATISTICS METHODS ====================

    public function getStatistics() {
        $stats = [];
        
        // Total items
        $query = "SELECT COUNT(*) as total FROM " . $this->items_table;
        $stmt = $this->conn->query($query);
        $stats['total_items'] = $stmt->fetch()['total'];
        
        // Available items
        $query = "SELECT COUNT(*) as total FROM " . $this->items_table . " 
                  WHERE is_available = 1 AND stock_quantity > 0";
        $stmt = $this->conn->query($query);
        $stats['available_items'] = $stmt->fetch()['total'];
        
        // Out of stock
        $query = "SELECT COUNT(*) as total FROM " . $this->items_table . " 
                  WHERE stock_quantity <= 0";
        $stmt = $this->conn->query($query);
        $stats['out_of_stock'] = $stmt->fetch()['total'];
        
        // Special items
        $query = "SELECT COUNT(*) as total FROM " . $this->items_table . " 
                  WHERE is_special = 1";
        $stmt = $this->conn->query($query);
        $stats['special_items'] = $stmt->fetch()['total'];
        
        // Low stock items
        $query = "SELECT COUNT(*) as total FROM " . $this->items_table . " 
                  WHERE stock_quantity > 0 AND stock_quantity <= low_stock_threshold";
        $stmt = $this->conn->query($query);
        $stats['low_stock'] = $stmt->fetch()['total'];
        
        // Total categories
        $query = "SELECT COUNT(*) as total FROM " . $this->categories_table . " 
                  WHERE is_active = 1";
        $stmt = $this->conn->query($query);
        $stats['total_categories'] = $stmt->fetch()['total'];
        
        // Most popular items (based on order count)
        $query = "SELECT mi.item_name, COUNT(oi.id) as order_count 
                  FROM " . $this->items_table . " mi
                  LEFT JOIN order_items oi ON mi.id = oi.menu_item_id
                  GROUP BY mi.id
                  ORDER BY order_count DESC
                  LIMIT 5";
        $stmt = $this->conn->query($query);
        $stats['popular_items'] = $stmt->fetchAll();
        
        return $stats;
    }

    private function generateItemCode() {
        $prefix = 'ITM';
        $query = "SELECT COUNT(*) as count FROM " . $this->items_table;
        $stmt = $this->conn->query($query);
        $count = $stmt->fetch()['count'] + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function searchItems($keyword) {
        $query = "SELECT i.*, c.category_name 
                  FROM " . $this->items_table . " i
                  LEFT JOIN " . $this->categories_table . " c ON i.category_id = c.id
                  WHERE i.item_name LIKE :keyword 
                     OR i.description LIKE :keyword
                     OR i.item_code LIKE :keyword
                  ORDER BY i.item_name";
        $stmt = $this->conn->prepare($query);
        $search = "%{$keyword}%";
        $stmt->bindParam(':keyword', $search);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getItemsByCategory($category_id) {
        return $this->getMenuItems(['category_id' => $category_id]);
    }

    public function toggleAvailability($id) {
        $query = "UPDATE " . $this->items_table . " 
                  SET is_available = NOT is_available 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function toggleSpecial($id) {
        $query = "UPDATE " . $this->items_table . " 
                  SET is_special = NOT is_special 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>