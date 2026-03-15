<?php
// file: HNR_SYSTEM/src/admin_portal/operations/inventory_&_stocks.php
// ========== BACKEND PHP CODE ==========
// Turn off error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering
ob_start();

// Include database connection
require_once __DIR__ . '/../marketing/config/database.php';

// ========== INVENTORY CLASS ==========
class Inventory {
    private $conn;
    private $table_items = "inventory_items";
    private $table_suppliers = "inventory_suppliers";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // Create tables if they don't exist
        $this->createTables();
    }
    
    private function createTables() {
        try {
            // Create items table
            $sql = "CREATE TABLE IF NOT EXISTS {$this->table_items} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                category ENUM('Food','Beverage','Housekeeping','Linens','Amenities','Maintenance') NOT NULL,
                sku VARCHAR(50) UNIQUE NOT NULL,
                quantity INT NOT NULL DEFAULT 0,
                unit VARCHAR(50) NOT NULL,
                reorder_level INT NOT NULL DEFAULT 10,
                status ENUM('in stock','low stock','out of stock') GENERATED ALWAYS AS (
                    CASE 
                        WHEN quantity <= 0 THEN 'out of stock'
                        WHEN quantity <= reorder_level THEN 'low stock'
                        ELSE 'in stock'
                    END
                ) STORED,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_category (category),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            $this->conn->exec($sql);
            
            // Create suppliers table
            $sql = "CREATE TABLE IF NOT EXISTS {$this->table_suppliers} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_name VARCHAR(255) NOT NULL,
                contact_person VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                email VARCHAR(255) DEFAULT NULL,
                address TEXT DEFAULT NULL,
                category VARCHAR(100) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            $this->conn->exec($sql);
            
            // Insert sample data if empty
            $this->insertSampleData();
            
        } catch (PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
        }
    }
    
    private function insertSampleData() {
        // Check if suppliers exist
        $stmt = $this->conn->query("SELECT COUNT(*) as count FROM {$this->table_suppliers}");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($count == 0) {
            $suppliers = [
                ['Fresh Foods Inc.', 'Juan Dela Cruz', '0917 555 1234', 'juan@freshfoods.com', 'Food'],
                ['Hotel Supplies Co.', 'Maria Santos', '0917 555 5678', 'maria@hotelsupplies.com', 'Housekeeping'],
                ['Linens & More', 'Jose Reyes', '0917 555 9012', 'jose@linensmore.com', 'Linens'],
                ['Amenities Plus', 'Ana Lopez', '0917 555 3456', 'ana@amenitiesplus.com', 'Amenities'],
                ['Maintenance Pro', 'Pedro Cruz', '0917 555 7890', 'pedro@maintenancepro.com', 'Maintenance']
            ];
            
            $sql = "INSERT INTO {$this->table_suppliers} (company_name, contact_person, phone, email, category) 
                    VALUES (:company, :contact, :phone, :email, :category)";
            $stmt = $this->conn->prepare($sql);
            
            foreach ($suppliers as $supplier) {
                $stmt->execute([
                    ':company' => $supplier[0],
                    ':contact' => $supplier[1],
                    ':phone' => $supplier[2],
                    ':email' => $supplier[3],
                    ':category' => $supplier[4]
                ]);
            }
        }
        
        // Check if items exist
        $stmt = $this->conn->query("SELECT COUNT(*) as count FROM {$this->table_items}");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($count == 0) {
            $items = [
                ['Rice (50kg)', 'Food', 'FOD-001', 24, 'bags', 10],
                ['Chicken (kg)', 'Food', 'FOD-002', 8, 'kg', 15],
                ['Bath Towels', 'Linens', 'LIN-001', 124, 'pcs', 50],
                ['Toilet Paper (case)', 'Housekeeping', 'HKS-001', 6, 'cases', 10],
                ['Shampoo (ml)', 'Amenities', 'AME-001', 0, 'bottles', 50],
                ['Light Bulbs', 'Maintenance', 'MNT-001', 32, 'pcs', 20]
            ];
            
            $sql = "INSERT INTO {$this->table_items} (name, category, sku, quantity, unit, reorder_level) 
                    VALUES (:name, :category, :sku, :quantity, :unit, :reorder_level)";
            $stmt = $this->conn->prepare($sql);
            
            foreach ($items as $item) {
                $stmt->execute([
                    ':name' => $item[0],
                    ':category' => $item[1],
                    ':sku' => $item[2],
                    ':quantity' => $item[3],
                    ':unit' => $item[4],
                    ':reorder_level' => $item[5]
                ]);
            }
        }
    }
    
    public function getDashboardStats() {
        $stats = [];
        
        try {
            // Total items
            $query = "SELECT COUNT(*) as total FROM {$this->table_items}";
            $stmt = $this->conn->query($query);
            $stats['total_items'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // In stock items
            $query = "SELECT COUNT(*) as count FROM {$this->table_items} WHERE quantity > reorder_level AND quantity > 0";
            $stmt = $this->conn->query($query);
            $stats['in_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Low stock items
            $query = "SELECT COUNT(*) as count FROM {$this->table_items} WHERE quantity <= reorder_level AND quantity > 0";
            $stmt = $this->conn->query($query);
            $stats['low_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Out of stock items
            $query = "SELECT COUNT(*) as count FROM {$this->table_items} WHERE quantity = 0";
            $stmt = $this->conn->query($query);
            $stats['out_of_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stats['to_reorder'] = $stats['low_stock'] + $stats['out_of_stock'];
            
        } catch (Exception $e) {
            $stats = [
                'total_items' => 0,
                'in_stock' => 0,
                'low_stock' => 0,
                'out_of_stock' => 0,
                'to_reorder' => 0
            ];
        }
        
        return $stats;
    }
    
    public function getItemsToReorder() {
        try {
            $query = "SELECT * FROM {$this->table_items} WHERE quantity <= reorder_level ORDER BY quantity ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function getSuppliers() {
        try {
            $query = "SELECT * FROM {$this->table_suppliers} ORDER BY company_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function getItems($category = null, $lowStockOnly = false, $search = null) {
        try {
            $query = "SELECT * FROM {$this->table_items} WHERE 1=1";
            
            if ($category && $category != 'all') {
                $query .= " AND category = :category";
            }
            
            if ($lowStockOnly) {
                $query .= " AND quantity <= reorder_level";
            }
            
            if ($search) {
                $query .= " AND (name LIKE :search OR sku LIKE :search)";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($category && $category != 'all') {
                $stmt->bindParam(':category', $category);
            }
            
            if ($search) {
                $searchTerm = "%$search%";
                $stmt->bindParam(':search', $searchTerm);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function getItem($id) {
        try {
            $query = "SELECT * FROM {$this->table_items} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function addItem($data) {
        try {
            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSKU($data['category']);
            }
            
            $query = "INSERT INTO {$this->table_items} (name, category, sku, quantity, unit, reorder_level) 
                      VALUES (:name, :category, :sku, :quantity, :unit, :reorder_level)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':sku', $data['sku']);
            $stmt->bindParam(':quantity', $data['quantity']);
            $stmt->bindParam(':unit', $data['unit']);
            $stmt->bindParam(':reorder_level', $data['reorder_level']);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Add item error: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateItem($id, $data) {
        try {
            $query = "UPDATE {$this->table_items} SET 
                      name = :name, 
                      category = :category, 
                      unit = :unit, 
                      reorder_level = :reorder_level
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':unit', $data['unit']);
            $stmt->bindParam(':reorder_level', $data['reorder_level']);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function deleteItem($id) {
        try {
            $query = "DELETE FROM {$this->table_items} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function adjustStock($itemId, $newQuantity, $notes = '') {
        try {
            $query = "UPDATE {$this->table_items} SET quantity = :quantity WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':quantity', $newQuantity);
            $stmt->bindParam(':id', $itemId);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function createPurchaseOrder($supplierId, $items) {
        // Simple implementation - just return a PO number
        return 'PO-' . date('Ymd') . '-' . rand(1000, 9999);
    }
    
    private function generateSKU($category) {
        $prefix = substr(strtoupper($category), 0, 3);
        $query = "SELECT COUNT(*) as count FROM {$this->table_items} WHERE category = :category";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] + 1;
        
        return $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}

// ========== API HANDLER ==========
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $inventory = new Inventory();
    
    try {
        $action = $_GET['action'];
        
        switch ($action) {
            case 'get_stats':
                $stats = $inventory->getDashboardStats();
                $reorderItems = $inventory->getItemsToReorder();
                $suppliers = $inventory->getSuppliers();
                echo json_encode([
                    'success' => true,
                    'stats' => $stats,
                    'reorder_items' => $reorderItems,
                    'suppliers' => $suppliers
                ]);
                break;
                
            case 'get_items':
                $category = isset($_GET['category']) ? $_GET['category'] : null;
                $lowStockOnly = isset($_GET['low_stock']) && $_GET['low_stock'] == 'true';
                $search = isset($_GET['search']) ? $_GET['search'] : null;
                $id = isset($_GET['id']) ? $_GET['id'] : null;
                
                if ($id) {
                    $item = $inventory->getItem($id);
                    echo json_encode(['success' => true, 'data' => $item ? [$item] : []]);
                } else {
                    $items = $inventory->getItems($category, $lowStockOnly, $search);
                    echo json_encode(['success' => true, 'data' => $items]);
                }
                break;
                
            case 'add_item':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    throw new Exception('Method not allowed');
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    throw new Exception('Invalid JSON data');
                }
                
                $required = ['name', 'category', 'quantity', 'unit'];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        throw new Exception("Missing required field: $field");
                    }
                }
                
                $data = [
                    'name' => $input['name'],
                    'category' => ucfirst($input['category']),
                    'quantity' => intval($input['quantity']),
                    'unit' => $input['unit'],
                    'reorder_level' => isset($input['reorder_level']) ? intval($input['reorder_level']) : 10,
                    'sku' => isset($input['sku']) ? $input['sku'] : ''
                ];
                
                $result = $inventory->addItem($data);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Item added successfully', 'id' => $result]);
                } else {
                    throw new Exception('Failed to add item');
                }
                break;
                
            case 'update_item':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    throw new Exception('Method not allowed');
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['id'])) {
                    throw new Exception('Item ID is required');
                }
                
                $result = $inventory->updateItem($input['id'], $input);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
                } else {
                    throw new Exception('Failed to update item');
                }
                break;
                
            case 'delete_item':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    throw new Exception('Method not allowed');
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['id'])) {
                    throw new Exception('Item ID is required');
                }
                
                $result = $inventory->deleteItem($input['id']);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
                } else {
                    throw new Exception('Failed to delete item');
                }
                break;
                
            case 'adjust_stock':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    throw new Exception('Method not allowed');
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['item_id']) || !isset($input['new_quantity'])) {
                    throw new Exception('Item ID and new quantity are required');
                }
                
                $result = $inventory->adjustStock(
                    $input['item_id'],
                    intval($input['new_quantity']),
                    $input['notes'] ?? 'Manual adjustment'
                );
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Stock adjusted successfully']);
                } else {
                    throw new Exception('Failed to adjust stock');
                }
                break;
                
            case 'create_po':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    throw new Exception('Method not allowed');
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['supplier_id']) || !isset($input['items'])) {
                    throw new Exception('Supplier ID and items are required');
                }
                
                $result = $inventory->createPurchaseOrder($input['supplier_id'], $input['items']);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Purchase order created successfully', 'po_number' => $result]);
                } else {
                    throw new Exception('Failed to create purchase order');
                }
                break;
                
            default:
                throw new Exception('Invalid action: ' . $action);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Clear output buffer
ob_clean();
?>
  <title>Admin · Inventory & Stock</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .transition-side { transition: all 0.2s ease; }
    .dropdown-arrow { transition: transform 0.2s; }
    details[open] .dropdown-arrow { transform: rotate(90deg); }
    details > summary { list-style: none; }
    details summary::-webkit-details-marker { display: none; }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .animate-slide-in { animation: slideIn 0.3s ease-out; }
    
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 20px;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close:hover { color: black; }
    
    .form-group { margin-bottom: 15px; }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #374151;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #f59e0b;
        ring: 2px solid #f59e0b;
    }
    
    .btn-submit {
        background-color: #f59e0b;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        width: 100%;
    }
    
    .btn-submit:hover { background-color: #d97706; }
    
    .btn-cancel {
        background-color: #e5e7eb;
        color: #374151;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        width: 100%;
        margin-top: 10px;
    }
    
    .btn-cancel:hover { background-color: #d1d5db; }
  </style>
<body class="bg-white font-sans antialiased">

  <!-- Add Item Modal -->
  <div id="addItemModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('addItemModal')">&times;</span>
      <h2 class="text-xl font-semibold mb-4">Add New Item</h2>
      <form id="addItemForm" onsubmit="return false;">
        <div class="form-group">
          <label for="itemName">Item Name *</label>
          <input type="text" id="itemName" required>
        </div>
        
        <div class="form-group">
          <label for="itemCategory">Category *</label>
          <select id="itemCategory" required>
            <option value="">Select Category</option>
            <option value="Food">Food</option>
            <option value="Beverage">Beverage</option>
            <option value="Housekeeping">Housekeeping</option>
            <option value="Linens">Linens</option>
            <option value="Amenities">Amenities</option>
            <option value="Maintenance">Maintenance</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="itemQuantity">Initial Quantity *</label>
          <input type="number" id="itemQuantity" min="0" required>
        </div>
        
        <div class="form-group">
          <label for="itemUnit">Unit *</label>
          <select id="itemUnit" required>
            <option value="">Select Unit</option>
            <option value="bags">bags</option>
            <option value="kg">kg</option>
            <option value="pcs">pcs</option>
            <option value="cases">cases</option>
            <option value="bottles">bottles</option>
            <option value="liters">liters</option>
            <option value="packs">packs</option>
            <option value="bars">bars</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="itemReorderLevel">Reorder Level</label>
          <input type="number" id="itemReorderLevel" min="0" value="10">
        </div>
        
        <div class="form-group">
          <label for="itemSKU">SKU (leave blank for auto-generate)</label>
          <input type="text" id="itemSKU" placeholder="e.g., FOD-001">
        </div>
        
        <button type="submit" class="btn-submit" onclick="submitAddItem()">Add Item</button>
        <button type="button" class="btn-cancel" onclick="closeModal('addItemModal')">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Edit Item Modal -->
  <div id="editItemModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('editItemModal')">&times;</span>
      <h2 class="text-xl font-semibold mb-4">Edit Item</h2>
      <form id="editItemForm" onsubmit="return false;">
        <input type="hidden" id="editItemId">
        <div class="form-group">
          <label for="editItemName">Item Name *</label>
          <input type="text" id="editItemName" required>
        </div>
        
        <div class="form-group">
          <label for="editItemCategory">Category *</label>
          <select id="editItemCategory" required>
            <option value="Food">Food</option>
            <option value="Beverage">Beverage</option>
            <option value="Housekeeping">Housekeeping</option>
            <option value="Linens">Linens</option>
            <option value="Amenities">Amenities</option>
            <option value="Maintenance">Maintenance</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="editItemUnit">Unit *</label>
          <select id="editItemUnit" required>
            <option value="bags">bags</option>
            <option value="kg">kg</option>
            <option value="pcs">pcs</option>
            <option value="cases">cases</option>
            <option value="bottles">bottles</option>
            <option value="liters">liters</option>
            <option value="packs">packs</option>
            <option value="bars">bars</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="editItemReorderLevel">Reorder Level</label>
          <input type="number" id="editItemReorderLevel" min="0">
        </div>
        
        <button type="submit" class="btn-submit" onclick="submitEditItem()">Update Item</button>
        <button type="button" class="btn-cancel" onclick="closeModal('editItemModal')">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Adjust Stock Modal -->
  <div id="adjustStockModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('adjustStockModal')">&times;</span>
      <h2 class="text-xl font-semibold mb-4">Adjust Stock</h2>
      <form id="adjustStockForm" onsubmit="return false;">
        <input type="hidden" id="adjustItemId">
        <div class="form-group">
          <label for="currentQuantity">Current Quantity</label>
          <input type="text" id="currentQuantity" readonly class="bg-gray-100">
        </div>
        
        <div class="form-group">
          <label for="newQuantity">New Quantity *</label>
          <input type="number" id="newQuantity" min="0" required>
        </div>
        
        <div class="form-group">
          <label for="adjustNotes">Notes</label>
          <textarea id="adjustNotes" rows="3" placeholder="Reason for adjustment"></textarea>
        </div>
        
        <button type="submit" class="btn-submit" onclick="submitAdjustStock()">Adjust Stock</button>
        <button type="button" class="btn-cancel" onclick="closeModal('adjustStockModal')">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Create PO Modal -->
  <div id="createPOModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('createPOModal')">&times;</span>
      <h2 class="text-xl font-semibold mb-4">Create Purchase Order</h2>
      <form id="createPOForm" onsubmit="return false;">
        <input type="hidden" id="poItemId">
        <div class="form-group">
          <label for="poSupplier">Supplier *</label>
          <select id="poSupplier" required>
            <option value="">Select Supplier</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="poItemName">Item</label>
          <input type="text" id="poItemName" readonly class="bg-gray-100">
        </div>
        
        <div class="form-group">
          <label for="poQuantity">Quantity *</label>
          <input type="number" id="poQuantity" min="1" required>
        </div>
        
        <div class="form-group">
          <label for="poNotes">Notes</label>
          <textarea id="poNotes" rows="3" placeholder="Additional notes"></textarea>
        </div>
        
        <button type="submit" class="btn-submit" onclick="submitCreatePO()">Create Purchase Order</button>
        <button type="button" class="btn-cancel" onclick="closeModal('createPOModal')">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Main Content -->
  <div class="min-h-screen flex flex-col lg:flex-row">
    <!-- Sidebar (keep your existing sidebar code) -->
    <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm lg:min-h-screen shrink-0 overflow-y-auto">
      <!-- Your existing sidebar code here -->
      <div class="px-5 py-6 border-b border-slate-100 flex items-center gap-2">
        <i class="fa-solid fa-utensils text-amber-600 text-xl"></i>
        <i class="fa-solid fa-bed text-amber-600 text-xl"></i>
        <span class="font-semibold text-lg tracking-tight text-slate-800">Lùcas<span class="text-amber-600">.admin</span></span>
      </div>

      <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-slate-50/60">
        <div class="h-9 w-9 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold">A</div>
        <div>
          <p class="font-medium text-sm">Andreo Reyes</p>
          <p class="text-xs text-slate-500">general manager</p>
        </div>
      </div>
<!-- Dashboard (top level, no dropdown) -->
<a href="./dashboard.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition">
  <i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>
  <span>Dashboard</span>
</a>
      <nav class="p-4 space-y-2 text-sm">
        

        <!-- HOTEL MANAGEMENT GROUP (dropdown) -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer transition-side">
            <i class=" fa-solid fa-hotel w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">HOTEL MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../admin_portal/Hotel_management/front_desk_reception.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-reception w-4 text-slate-400"></i> Front Desk / Reception</a>
            <a href="../admin_portal/Hotel_management/room_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bed w-4 text-slate-400"></i> Room Management</a>
            <a href="../admin_portal/Hotel_management/reservation_&_booking.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar-check w-4 text-slate-400"></i> Reservations & Booking</a>
            <a href="../admin_portal/Hotel_management/housekeeping_&_maintenance.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-broom w-4 text-slate-400"></i> Housekeeping & Maintenance</a>
            <a href="../admin_portal/Hotel_management/event_&_conference.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar w-4 text-slate-400"></i> Events & Conference</a>
          </div>
        </details>

        <!-- RESTAURANT MANAGEMENT GROUP -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-utensils w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">RESTAURANT MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../admin_portal/restaurant/table_reservation.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-clock w-4"></i> Table Reservation</a>
            <a href="../admin_portal/restaurant/menu_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bars w-4"></i> Menu Management</a>
            <a href="../admin_portal/restaurant/orders_pos.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-cash-register w-4"></i> Orders / POS</a>
            <a href="../admin_portal/restaurant/kitchen_orders.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-fire w-4"></i> Kitchen Orders (KOT)</a>
            <a href="../admin_portal/restaurant/wait_staff_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-user w-4"></i> Wait Staff Management</a>
          </div>
        </details>

        <!-- CUSTOMER MANAGEMENT -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-regular fa-address-book w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">CUSTOMER MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../customer_management/customer_relationship.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-handshake w-4"></i> Customer Relationship (CRM)</a>
            <a href="../customer_management/loyalty_rewards.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-star w-4"></i> Loyalty & Rewards</a>
            <a href="../customer_management/customer_feedback_&_reviews.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-pen-to-square w-4"></i> Customer Feedback & Reviews</a>
          </div>
        </details>

      <!-- OPERATIONS -->
<details class="group" open>
  <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-amber-800 bg-amber-50 cursor-pointer transition-side">
    <i class="fa-solid fa-gears w-5 text-amber-600"></i>
    <span class="font-medium">OPERATIONS</span>
    <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-amber-600"></i>
  </summary>
  <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-200">
    <a href="inventory_&_stocks.php" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-100/50 text-amber-700 font-medium">
      <i class="fa-solid fa-boxes w-4 text-amber-600"></i> Inventory & Stock
    </a>
    <a href="billing_&_payment.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50">
      <i class="fa-regular fa-credit-card w-4 text-slate-400"></i> Billing & Payments
    </a>
    <a href="payment_gateway.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50">
      <i class="fa-solid fa-wifi w-4 text-slate-400"></i> Payment Gateway
    </a>
  </div>
</details>

        <!-- MARKETING -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-megaphone w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">MARKETING</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="/admin_portal/marketing/hotelmarketing_&_promotions.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-gem w-4"></i> Hotel Marketing & Promotions</a>
            <a href="../admin_portal/marketing/online_ordering_integration.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-cart-shopping w-4"></i> Online Ordering Integration</a>
          </div>
        </details>

        <!-- REPORTS & ANALYTICS -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-chart-simple w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">REPORTS & ANALYTICS</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../admin_portal/reports_&_analytics/sales_reports.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-chart-line w-4"></i> Sales Reports</a>
            <a href="../admin_portal/reports_&_analytics/booking_reports.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar w-4"></i> Booking Reports</a>
            <a href="../admin_portal/reports_&_analytics/analytics_dashboard.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-chart-pie w-4"></i> Analytics Dashboard</a>
          </div>
        </details>

        <!-- SYSTEM (with special items: door lock integration) -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-computer w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">SYSTEM</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../admin_portal/System/channel_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-code-branch w-4"></i> Channel Management</a>
            <a href="../admin_portal/System/door_lock_integration.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-lock w-4"></i> Door Lock Integration</a>
            <a href="../admin_portal/System/settings.html " class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-sliders w-4"></i> Settings</a>
          </div>
        </details>

        <!-- logout -->
        <div class="border-t border-slate-200 pt-3 mt-3">
          <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700">
            <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
            <span>Logout</span>
          </a>
        </div>
        <!-- Add other menu items as needed -->
      </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">
      <!-- Your existing main content here -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Inventory & Stock</h1>
          <p class="text-sm text-slate-500 mt-0.5">manage supplies, track stock levels, and handle reorders</p>
        </div>
        <div class="flex gap-3 text-sm">
          <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i class="fa-regular fa-calendar text-slate-400"></i> <span id="current-date"></span></span>
          <span class="bg-white border rounded-full px-4 py-2 shadow-sm"><i class="fa-regular fa-bell"></i></span>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Total items</p>
          <p class="text-2xl font-semibold" id="stat-total">0</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">In stock</p>
          <p class="text-2xl font-semibold text-green-600" id="stat-instock">0</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Low stock</p>
          <p class="text-2xl font-semibold text-amber-600" id="stat-lowstock">0</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Out of stock</p>
          <p class="text-2xl font-semibold text-rose-600" id="stat-outstock">0</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">To reorder</p>
          <p class="text-2xl font-semibold" id="stat-reorder">0</p>
        </div>
      </div>

      <!-- Action Bar -->
      <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
        <div class="flex gap-2 flex-wrap">
          <button onclick="openAddModal()" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm">+ add item</button>
          <button onclick="openPOModal()" class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50">create PO</button>
          <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50">receive stock</button>
          <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50">export</button>
        </div>
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
          <input type="text" id="search-input" placeholder="search inventory..." class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
        </div>
      </div>

      <!-- Category Tabs -->
      <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
        <button class="category-filter px-4 py-2 bg-amber-600 text-white rounded-full text-sm" data-category="all">all</button>
        <button class="category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-category="food">food & beverage</button>
        <button class="category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-category="housekeeping">housekeeping</button>
        <button class="category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-category="linens">linens</button>
        <button class="category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-category="amenities">amenities</button>
        <button class="category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-category="maintenance">maintenance</button>
      </div>

      <!-- Inventory Table -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
          <h2 class="font-semibold flex items-center gap-2"><i class="fa-solid fa-boxes text-amber-600"></i> current inventory</h2>
          <div class="flex gap-2">
            <button id="low-stock-btn" class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50">low stock only</button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="text-slate-500 text-xs border-b">
              <tr>
                <td class="p-3">Item</td>
                <td class="p-3">Category</td>
                <td class="p-3">SKU</td>
                <td class="p-3">In stock</td>
                <td class="p-3">Unit</td>
                <td class="p-3">Reorder level</td>
                <td class="p-3">Status</td>
                <td class="p-3">Actions</td>
              </tr>
            </thead>
            <tbody id="inventory-table-body" class="divide-y">
              <tr><td colspan="8" class="text-center py-8 text-slate-500">Loading items...</td></tr>
            </tbody>
          </table>
        </div>
        <div class="p-4 border-t border-slate-200 flex items-center justify-between">
          <span class="text-xs text-slate-500" id="table-info">Loading items...</span>
          <div class="flex gap-2">
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm">Previous</button>
            <button class="bg-amber-600 text-white px-3 py-1 rounded-lg text-sm">1</button>
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm">2</button>
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm">3</button>
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm">4</button>
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm">5</button>
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm">Next</button>
          </div>
        </div>
      </div>

      <!-- Bottom Section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
          <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i class="fa-regular fa-clock text-amber-600"></i> items to reorder</h2>
          <div id="reorder-list" class="space-y-3">
            <div class="text-center text-slate-500 py-4">Loading...</div>
          </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
          <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fa-regular fa-truck text-amber-600"></i> primary suppliers</h3>
          <ul id="suppliers-list" class="space-y-2">
            <li class="text-sm text-slate-500">Loading...</li>
          </ul>
        </div>
      </div>

      <div class="mt-8 text-center text-xs text-slate-400 border-t pt-5">
        ✅ Inventory & Stock — item list with quantities, low stock alerts, reorder list, supplier contacts
      </div>
    </main>
  </div>

  <script>
    // API endpoint - this file handles both UI and API
    const API_URL = window.location.href;
    
    // Global variables
    let currentCategory = 'all';
    let lowStockOnly = false;
    
    // Set current date
    document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', { 
        year: 'numeric', month: 'long', day: 'numeric' 
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardStats();
        loadInventoryItems();
        setupEventListeners();
    });

    // API Call function
    async function apiCall(action, method = 'GET', data = null) {
        const url = `${API_URL}?action=${action}`;
        const options = {
            method: method,
            headers: { 'Content-Type': 'application/json' }
        };
        
        if (data) options.body = JSON.stringify(data);
        
        try {
            const response = await fetch(url, options);
            return await response.json();
        } catch (error) {
            console.error('API call failed:', error);
            showNotification('Network error', 'error');
            return null;
        }
    }

    // Load dashboard stats
    async function loadDashboardStats() {
        const data = await apiCall('get_stats');
        if (data?.success) {
            document.getElementById('stat-total').textContent = data.stats.total_items || 0;
            document.getElementById('stat-instock').textContent = data.stats.in_stock || 0;
            document.getElementById('stat-lowstock').textContent = data.stats.low_stock || 0;
            document.getElementById('stat-outstock').textContent = data.stats.out_of_stock || 0;
            document.getElementById('stat-reorder').textContent = data.stats.to_reorder || 0;
            updateReorderList(data.reorder_items);
            updateSuppliers(data.suppliers);
        }
    }

    // Load inventory items
    async function loadInventoryItems() {
        let url = `${API_URL}?action=get_items`;
        if (currentCategory !== 'all') url += `&category=${encodeURIComponent(currentCategory)}`;
        if (lowStockOnly) url += `&low_stock=true`;
        
        try {
            const response = await fetch(url);
            const data = await response.json();
            if (data?.success) renderInventoryTable(data.data);
        } catch (error) {
            console.error('Failed to load items:', error);
        }
    }

    // Update UI functions
    function updateReorderList(items) {
        const container = document.getElementById('reorder-list');
        if (!items?.length) {
            container.innerHTML = '<div class="text-center text-slate-500 py-4">No items to reorder</div>';
            return;
        }
        container.innerHTML = items.map(item => `
            <div class="flex justify-between items-center border-b pb-2">
                <div>
                    <span class="font-medium">${escapeHtml(item.name)}</span>
                    <p class="text-xs text-slate-500">Current: ${item.quantity} | Reorder at: ${item.reorder_level}</p>
                </div>
                <button class="bg-amber-600 text-white px-3 py-1 rounded-lg text-xs" onclick="openPOModal(${item.id}, '${escapeHtml(item.name)}')">reorder</button>
            </div>
        `).join('');
    }

    function updateSuppliers(suppliers) {
        const container = document.getElementById('suppliers-list');
        if (!suppliers?.length) {
            container.innerHTML = '<li class="text-sm text-slate-500">No suppliers found</li>';
            return;
        }
        container.innerHTML = suppliers.map(supplier => `
            <li class="text-sm border-b pb-1">
                <span class="font-medium">${escapeHtml(supplier.company_name)}</span>
                <p class="text-xs text-slate-500">Contact: ${escapeHtml(supplier.contact_person)} · ${escapeHtml(supplier.phone)}</p>
            </li>
        `).join('');
    }

    function renderInventoryTable(items) {
        const tbody = document.getElementById('inventory-table-body');
        document.getElementById('table-info').textContent = `Showing ${items.length} items`;

        if (!items?.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-slate-500">No items found</td></tr>';
            return;
        }

        tbody.innerHTML = items.map(item => {
            let statusClass = 'bg-slate-100 text-slate-700';
            if (item.status === 'in stock') statusClass = 'bg-green-100 text-green-700';
            else if (item.status === 'low stock') statusClass = 'bg-amber-100 text-amber-700';
            else if (item.status === 'out of stock') statusClass = 'bg-rose-100 text-rose-700';

            let actionButton = item.status === 'in stock' 
                ? `<button class="text-blue-600 text-xs hover:underline" onclick="openAdjustModal(${item.id}, ${item.quantity})">adjust</button>`
                : `<button class="text-blue-600 text-xs hover:underline" onclick="openPOModal(${item.id}, '${escapeHtml(item.name)}')">reorder</button>`;

            return `
                <tr>
                    <td class="p-3"><span class="font-medium">${escapeHtml(item.name)}</span></td>
                    <td class="p-3">${escapeHtml(item.category)}</td>
                    <td class="p-3 text-xs">${escapeHtml(item.sku)}</td>
                    <td class="p-3 font-medium">${item.quantity}</td>
                    <td class="p-3">${escapeHtml(item.unit)}</td>
                    <td class="p-3">${item.reorder_level}</td>
                    <td class="p-3"><span class="${statusClass} px-2 py-0.5 rounded-full text-xs">${item.status}</span></td>
                    <td class="p-3">
                        <button class="text-amber-700 text-xs hover:underline mr-2" onclick="openEditModal(${item.id})">edit</button>
                        ${actionButton}
                        <button class="text-red-600 text-xs hover:underline ml-2" onclick="deleteItem(${item.id})">delete</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Event listeners
    function setupEventListeners() {
        document.querySelectorAll('.category-filter').forEach(btn => {
            btn.addEventListener('click', (e) => {
                currentCategory = e.target.dataset.category;
                lowStockOnly = false;
                document.querySelectorAll('.category-filter').forEach(b => {
                    b.classList.remove('bg-amber-600', 'text-white');
                    b.classList.add('bg-white', 'border', 'border-slate-200');
                });
                e.target.classList.remove('bg-white', 'border', 'border-slate-200');
                e.target.classList.add('bg-amber-600', 'text-white');
                loadInventoryItems();
            });
        });

        document.getElementById('search-input').addEventListener('input', (e) => {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(() => {
                e.target.value.length >= 2 ? searchItems(e.target.value) : loadInventoryItems();
            }, 500);
        });

        document.getElementById('low-stock-btn').addEventListener('click', () => {
            lowStockOnly = true;
            loadInventoryItems();
        });
    }

    async function searchItems(keyword) {
        const response = await fetch(`${API_URL}?action=get_items&search=${encodeURIComponent(keyword)}`);
        const data = await response.json();
        if (data?.success) renderInventoryTable(data.data);
    }

    // Modal functions
    function openModal(id) { document.getElementById(id).style.display = 'block'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function openAddModal() {
        document.getElementById('addItemForm').reset();
        openModal('addItemModal');
    }

    async function submitAddItem() {
        const data = {
            name: document.getElementById('itemName').value,
            category: document.getElementById('itemCategory').value,
            quantity: parseInt(document.getElementById('itemQuantity').value),
            unit: document.getElementById('itemUnit').value,
            reorder_level: parseInt(document.getElementById('itemReorderLevel').value || 10),
            sku: document.getElementById('itemSKU').value
        };

        if (!data.name || !data.category || !data.quantity || !data.unit) {
            showNotification('Please fill all required fields', 'error');
            return;
        }

        const result = await apiCall('add_item', 'POST', data);
        if (result?.success) {
            showNotification('Item added successfully', 'success');
            closeModal('addItemModal');
            loadDashboardStats();
            loadInventoryItems();
        } else {
            showNotification(result?.error || 'Failed to add item', 'error');
        }
    }

    async function openEditModal(itemId) {
        const response = await fetch(`${API_URL}?action=get_items&id=${itemId}`);
        const data = await response.json();
        if (data?.success && data.data[0]) {
            const item = data.data[0];
            document.getElementById('editItemId').value = item.id;
            document.getElementById('editItemName').value = item.name;
            document.getElementById('editItemCategory').value = item.category;
            document.getElementById('editItemUnit').value = item.unit;
            document.getElementById('editItemReorderLevel').value = item.reorder_level;
            openModal('editItemModal');
        }
    }

    async function submitEditItem() {
        const data = {
            id: parseInt(document.getElementById('editItemId').value),
            name: document.getElementById('editItemName').value,
            category: document.getElementById('editItemCategory').value,
            unit: document.getElementById('editItemUnit').value,
            reorder_level: parseInt(document.getElementById('editItemReorderLevel').value)
        };

        const result = await apiCall('update_item', 'POST', data);
        if (result?.success) {
            showNotification('Item updated successfully', 'success');
            closeModal('editItemModal');
            loadInventoryItems();
        } else {
            showNotification(result?.error || 'Failed to update item', 'error');
        }
    }

    function openAdjustModal(itemId, currentQty) {
        document.getElementById('adjustItemId').value = itemId;
        document.getElementById('currentQuantity').value = currentQty;
        document.getElementById('newQuantity').value = '';
        document.getElementById('adjustNotes').value = '';
        openModal('adjustStockModal');
    }

    async function submitAdjustStock() {
        const data = {
            item_id: parseInt(document.getElementById('adjustItemId').value),
            new_quantity: parseInt(document.getElementById('newQuantity').value),
            notes: document.getElementById('adjustNotes').value || 'Manual adjustment'
        };

        if (!data.new_quantity) {
            showNotification('Please enter new quantity', 'error');
            return;
        }

        const result = await apiCall('adjust_stock', 'POST', data);
        if (result?.success) {
            showNotification('Stock adjusted successfully', 'success');
            closeModal('adjustStockModal');
            loadDashboardStats();
            loadInventoryItems();
        } else {
            showNotification(result?.error || 'Failed to adjust stock', 'error');
        }
    }

    async function openPOModal(itemId = null, itemName = '') {
        const response = await fetch(`${API_URL}?action=get_stats`);
        const data = await response.json();
        if (data?.success && data.suppliers) {
            const select = document.getElementById('poSupplier');
            select.innerHTML = '<option value="">Select Supplier</option>';
            data.suppliers.forEach(s => select.innerHTML += `<option value="${s.id}">${escapeHtml(s.company_name)}</option>`);
        }

        document.getElementById('poItemId').value = itemId || '';
        document.getElementById('poItemName').value = itemName || '';
        document.getElementById('poQuantity').value = '';
        document.getElementById('poNotes').value = '';
        openModal('createPOModal');
    }

    async function submitCreatePO() {
        const data = {
            supplier_id: parseInt(document.getElementById('poSupplier').value),
            items: [{
                item_id: parseInt(document.getElementById('poItemId').value),
                quantity: parseInt(document.getElementById('poQuantity').value)
            }]
        };

        if (!data.supplier_id || !data.items[0].item_id || !data.items[0].quantity) {
            showNotification('Please fill all fields', 'error');
            return;
        }

        const result = await apiCall('create_po', 'POST', data);
        if (result?.success) {
            showNotification(`PO ${result.po_number} created`, 'success');
            closeModal('createPOModal');
        } else {
            showNotification(result?.error || 'Failed to create PO', 'error');
        }
    }

    async function deleteItem(itemId) {
        if (!confirm('Delete this item?')) return;
        const result = await apiCall('delete_item', 'POST', { id: itemId });
        if (result?.success) {
            showNotification('Item deleted', 'success');
            loadDashboardStats();
            loadInventoryItems();
        } else {
            showNotification(result?.error || 'Failed to delete', 'error');
        }
    }

    function showNotification(msg, type) {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 animate-slide-in ${
            type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-amber-600'
        } text-white`;
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    window.onclick = (e) => { if (e.target.classList.contains('modal')) e.target.style.display = 'none'; };
  </script>
</body>
</html>