# API Not Found Fix - Complete Solution

## 🎯 **API Routing Issue Identified**

### ❌ **Problem**
```
http://localhost/hotel_resto_system/src/customer_portal/api/user/profile
```
Returns **404 Not Found** instead of executing the API.

### 🔍 **Root Causes**

#### **1. Apache Configuration**
- `.htaccess` file missing or misconfigured
- URL rewriting not working properly
- Directory permissions incorrect

#### **2. PHP File Routing**
- API files not handling endpoints correctly
- Path parsing issues
- Method routing problems

#### **3. URL Structure**
- Incorrect base URL in frontend
- Missing trailing slashes
- Wrong relative paths

## ✅ **Solutions to Implement**

### **1. Create .htaccess File**
Create `.htaccess` in `customer_portal/api/` directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### **2. Create Router Index**
Create `index.php` in `customer_portal/api/` directory:
```php
<?php
/**
 * API Router
 * Routes all API requests to appropriate handlers
 */

$request_uri = $_SERVER['REQUEST_URI'];
$path_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));
$endpoint = end($path_parts);

// Route to appropriate file
switch ($endpoint) {
    case 'profile':
    require_once 'user.php';
        break;
    case 'bookings':
        require_once 'user.php';
        break;
    case 'reservations':
        require_once 'user.php';
        break;
    case 'hotel-rooms':
        require_once 'booking.php';
        break;
    case 'restaurant-tables':
        require_once 'booking.php';
        break;
    case 'menu-items':
        require_once 'booking.php';
        break;
    case 'create-hotel-booking':
    case 'create-restaurant-reservation':
    case 'create-food-order':
        require_once 'booking.php';
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Endpoint not found']);
        http_response_code(404);
        break;
}
?>
```

### **3. Update Frontend URLs**
Update `database.js` to use direct file calls:
```javascript
// Instead of: './api/user/profile'
// Use: './api/user.php?endpoint=profile'

async apiRequest(endpoint, options = {}) {
    const url = `./api/user.php?endpoint=${endpoint}`;
    // ... rest of code
}
```

## 🧪 **Testing Instructions**

### **1. Test Simple API**
Open in browser:
```
http://localhost/hotel_resto_system/src/customer_portal/api/test.php
```

**Expected Response:**
```json
{
  "success": true,
  "message": "API is working",
  "request_uri": "/hotel_resto_system/src/customer_portal/api/test.php",
  "request_method": "GET"
}
```

### **2. Test User API Directly**
Open in browser:
```
http://localhost/hotel_resto_system/src/customer_portal/api/user.php
```

**Expected Response:**
```json
{
  "success": true,
  "message": "User profile retrieved",
  "data": {
    "user_id": 1,
    "first_name": "Mia",
    "last_name": "Cruz"
  }
}
```

### **3. Test with Query Parameter**
Open in browser:
```
http://localhost/hotel_resto_system/src/customer_portal/api/user.php?endpoint=profile
```

**Expected Response:**
```json
{
  "success": true,
  "message": "User profile retrieved",
  "data": {
    "user_id": 1,
    "first_name": "Mia",
    "last_name": "Cruz"
  }
}
```

## 🔧 **Quick Fixes**

### **Fix 1: Direct File Access**
Update `database.js` to use direct file paths:
```javascript
// Update baseURL to use direct file calls
this.baseURL = './api';

// Update apiRequest method
async apiRequest(endpoint, options = {}) {
    let url;
    if (endpoint.includes('profile') || endpoint.includes('bookings') || endpoint.includes('reservations')) {
        url = `./api/user.php?endpoint=${endpoint}`;
    } else if (endpoint.includes('hotel-rooms') || endpoint.includes('restaurant-tables')) {
        url = `./api/booking.php?endpoint=${endpoint}`;
    } else {
        url = `./api/${endpoint}`;
    }
    
    const config = {
        headers: {
            'Content-Type': 'application/json',
            ...options.headers
        },
        ...options
    };
    
    try {
        const response = await fetch(url, config);
        // ... rest of code
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}
```

### **Fix 2: Update API Files**
Update `user.php` to handle query parameter:
```php
// Add at the top of user.php
$endpoint = $_GET['endpoint'] ?? 'profile';

// Then use $endpoint in switch statement
switch ($endpoint) {
    case 'profile':
        handleProfile($method, $userModel, $currentUser);
        break;
    // ... rest of cases
}
```

### **Fix 3: Create .htaccess**
Create `.htaccess` in `customer_portal/api/`:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

## 📁 **Files to Create/Update**

### **1. Create `.htaccess`**
```apache
# File: customer_portal/api/.htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### **2. Create `index.php`**
```php
// File: customer_portal/api/index.php
<?php
// Router code from above
?>
```

### **3. Update `database.js`**
```javascript
// Update baseURL and apiRequest method
this.baseURL = './api';

async apiRequest(endpoint, options = {}) {
    const url = `./api/user.php?endpoint=${endpoint}`;
    // ... rest of implementation
}
```

## 🚀 **Immediate Testing**

### **Step 1: Test Direct File Access**
1. Open: `http://localhost/hotel_resto_system/src/customer_portal/api/user.php`
2. Should return JSON response (not 404)

### **Step 2: Test with Query Parameter**
1. Open: `http://localhost/hotel_resto_system/src/customer_portal/api/user.php?endpoint=profile`
2. Should return user profile data

### **Step 3: Test Frontend**
1. Update database.js with new URL structure
2. Test customer portal pages
3. Should load user data correctly

## 🎯 **Expected Results**

### ✅ **API Access Working**
- Direct file access returns JSON
- Query parameter routing works
- No more 404 errors
- All endpoints accessible

### ✅ **Frontend Integration**
- User data loads properly
- Profile displays correctly
- Bookings and reservations show
- All features functional

### ✅ **Production Ready**
- Robust API routing
- Clean URL structure
- Proper error handling
- Complete integration

The API routing issue can be resolved by implementing these fixes! Choose the solution that works best for your Apache configuration. 🎉
