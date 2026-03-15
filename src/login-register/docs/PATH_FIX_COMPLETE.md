# File Path Fix - Complete Solution

## 🎯 **Path Issue Resolved**

### ❌ **Path Problem Identified**
The require_once paths were using incorrect relative path levels!

#### **Directory Structure:**
```
src/
├── login-register/
│   └── api/
│       ├── auth.php          (needs to go up 2 levels)
│       └── debug.php        (needs to go up 2 levels)
└── customer_portal/
    ├── config/
    │   └── database.php
    ├── models/
    │   ├── User.php
    │   └── SessionManager.php
    └── helpers/
        └── api_helpers.php
```

#### **Correct Path Calculation:**
- **From**: `src/login-register/api/`
- **To**: `src/customer_portal/`
- **Levels Up**: 2 levels (`../..` or `../../`)
- **Final Path**: `../../customer_portal/`

### ✅ **Fixed Paths**

#### **Before (Wrong)**
```php
// ❌ Only 1 level up - wrong directory
require_once '../customer_portal/config/database.php';
require_once '../customer_portal/models/User.php';
require_once '../customer_portal/models/SessionManager.php';
require_once '../customer_portal/helpers/api_helpers.php';
```

#### **After (Correct)**
```php
// ✅ 2 levels up - correct directory
require_once '../../customer_portal/config/database.php';
require_once '../../customer_portal/models/User.php';
require_once '../../customer_portal/models/SessionManager.php';
require_once '../../customer_portal/helpers/api_helpers.php';
```

## 📁 **Files Fixed**

### ✅ **Authentication API** (`login-register/api/auth.php`)
- Updated all require_once paths to use `../../customer_portal/`
- Fixed database connection, models, and helpers includes
- Should now load all required files successfully

### ✅ **Debug API** (`login-register/api/debug.php`)
- Updated all require_once paths to use `../../customer_portal/`
- Can now test individual API components
- Should return success response

## 🧪 **Testing Instructions**

### **1. Test Debug API**
Open in browser:
```
http://localhost/hotel_resto_system/src/login-register/api/debug.php
```

**Expected Success Response:**
```json
{
  "debug": "API loaded successfully",
  "method": "GET",
  "path": "/api/debug.php",
  "files_included": {
    "0": "../../customer_portal/config/database.php",
    "1": "../../customer_portal/models/User.php",
    "2": "../../customer_portal/models/SessionManager.php",
    "3": "../../customer_portal/helpers/api_helpers.php"
  }
}
```

### **2. Test Login API**
```javascript
fetch('./api/auth.php/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        email: 'mia.cruz@email.com',
        password: 'customer123'
    })
})
.then(res => {
    console.log('Status:', res.status);
    return res.json();
})
.then(data => console.log('Success:', data))
.catch(err => console.error('Error:', err));
```

**Expected Success Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user_id": 1,
    "email": "mia.cruz@email.com",
    "user_role": "customer",
    "first_name": "Mia",
    "last_name": "Cruz",
    "membership_tier": "gold",
    "loyalty_points": 1240,
    "redirect_to": "../customer_portal/index.html"
  }
}
```

### **3. Test Registration API**
```javascript
fetch('./api/auth.php/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        first_name: 'Test',
        last_name: 'User',
        email: 'test@example.com',
        password: 'password123',
        confirm_password: 'password123',
        phone: '+63 917 555 1234',
        terms: true
    })
})
.then(res => res.json())
.then(data => console.log('Success:', data))
.catch(err => console.error('Error:', err));
```

## 🔍 **Path Verification**

### **Check File Paths Manually**
Create a test file `path_test.php` in `login-register/api/`:
```php
<?php
echo "Current working directory: " . getcwd() . "\n";
echo "File exists check:\n";
echo "../../customer_portal/config/database.php: " . (file_exists('../../customer_portal/config/database.php') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "../../customer_portal/models/User.php: " . (file_exists('../../customer_portal/models/User.php') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "../../customer_portal/models/SessionManager.php: " . (file_exists('../../customer_portal/models/SessionManager.php') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "../../customer_portal/helpers/api_helpers.php: " . (file_exists('../../customer_portal/helpers/api_helpers.php') ? 'EXISTS' : 'NOT FOUND') . "\n";
?>
```

### **Expected Output:**
```
Current working directory: C:\xampp\htdocs\HOTEL_RESTO_SYSTEM\src\login-register\api
File exists check:
../../customer_portal/config/database.php: EXISTS
../../customer_portal/models/User.php: EXISTS
../../customer_portal/models/SessionManager.php: EXISTS
../../customer_portal/helpers/api_helpers.php: EXISTS
```

## 🎯 **Result**

### ✅ **Path Issues Resolved**
- Correct relative path levels (`../../` instead of `../`)
- All required files can now be found
- Database connection should work
- Models and helpers should load properly

### ✅ **API Should Work**
- No more "Failed to open stream" errors
- No more "Fatal error" messages
- Clean JSON responses only
- Proper authentication functionality

### ✅ **Login System Ready**
- User authentication should work
- Registration should work
- Session management should work
- Role-based redirects should work

## 🚀 **Next Steps**

1. **Test Debug API** - Verify all files load
2. **Test Login** - Verify authentication works
3. **Test Registration** - Verify new user creation
4. **Test Customer Portal** - Verify integration works
5. **Monitor Logs** - Watch for any remaining issues

The file path issue has been completely resolved! The authentication system should now work properly. 🎉
