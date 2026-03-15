# 500 Error Debug Guide

## 🔍 **Debugging Steps**

### **1. Test Debug API**
Open this URL in your browser:
```
http://localhost/hotel_resto_system/src/login-register/api/debug.php
```

This will show:
- If PHP files are loading correctly
- What error messages are being generated
- If database connection is working

### **2. Check PHP Error Logs**

#### **XAMPP Error Log Location:**
```bash
# Check PHP error log
tail -f /opt/lampp/logs/php_error_log

# Or check in XAMPP Control Panel
# Click on "Logs" → "PHP Error Log"
```

#### **Common Error Locations:**
- `/opt/lampp/logs/php_error_log`
- `/var/log/apache2/error.log`
- Windows: `C:\xampp\apache\logs\error.log`

### **3. Test Database Connection**

Create a simple test file `test_db.php`:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $database = new Database();
    $db = $database->getConnection();
    echo json_encode(['success' => 'Database connected', 'connection' => 'OK']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database connection failed', 'message' => $e->getMessage()]);
}
?>
```

### **4. Test Individual Components**

#### **Test SessionManager Only:**
```php
<?php
require_once '../config/database.php';
require_once '../models/SessionManager.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $sessionManager = new SessionManager($db);
    echo json_encode(['success' => 'SessionManager created']);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
```

#### **Test User Model Only:**
```php
<?php
require_once '../config/database.php';
require_once '../models/User.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $userModel = new User($db);
    echo json_encode(['success' => 'User model created']);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
```

### **5. Check for Common Issues**

#### **File Path Issues:**
- Verify file paths in `require_once` statements
- Check if files exist at specified locations
- Ensure relative paths are correct

#### **Database Credentials:**
- Check `config/database.php` credentials
- Verify database exists: `lucas_customer_portal`
- Test database connection manually

#### **PHP Version Issues:**
- Check if PHP version supports all functions used
- Verify required extensions are loaded
- Check for deprecated function usage

### **6. Network Issues**

#### **CORS Problems:**
- Check if requests are being blocked by CORS
- Verify Access-Control-Allow-Origin headers
- Test with different origins

#### **Request Format:**
- Verify Content-Type header is correct
- Check if request body is valid JSON
- Test with different HTTP methods

## 🧪 **Expected Debug Output**

### **Working Debug API Should Return:**
```json
{
  "debug": "API loaded successfully",
  "method": "POST",
  "path": "/api/debug.php",
  "files_included": {
    "0": "/path/to/config/database.php",
    "1": "/path/to/models/SessionManager.php",
    "2": "/path/to/models/User.php",
    "3": "/path/to/helpers/api_helpers.php"
  }
}
```

### **Common Error Patterns:**

#### **File Not Found:**
```json
{
  "error": "require_once(): Failed opening required '../config/database.php'"
}
```

#### **Database Connection:**
```json
{
  "error": "SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'"
}
```

#### **Missing Function:**
```json
{
  "error": "Call to undefined function validateRequired()"
}
```

## 🚀 **Next Steps**

1. **Run Debug API** - Check basic loading
2. **Review Error Logs** - Find specific error messages
3. **Test Components** - Isolate the failing part
4. **Fix Issues** - Address each error found
5. **Test Again** - Verify fixes work

## 📞 **If Still Failing**

### **Create Minimal Test:**
```php
<?php
// Minimal test - remove all complexity
header('Content-Type: application/json');
echo json_encode(['test' => 'minimal working']);
?>
```

### **Check Server Configuration:**
- Verify Apache/Nginx configuration
- Check PHP-FPM status
- Review server error logs
- Test with different PHP versions

Run these debugging steps to identify the exact cause of the 500 error!
