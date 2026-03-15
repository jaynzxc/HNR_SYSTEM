# Profile & Dashboard Empty Data Debug

## 🎯 **Debugging Empty User Data**

Since you're logged in but dashboard/profile still show empty data, let's debug this step by step.

## 🧪 **Step 1: Test Debug APIs**

### **A. Test Session Debug**
Open in browser:
```
http://localhost/hotel_resto_system/src/customer_portal/api/current_user_debug.php
```

**Expected Response (if working):**
```json
{
  "success": true,
  "session_id": "abc123...",
  "session_data": {
    "user_id": 1,
    "user_role": "customer",
    "expires_at": "2026-03-12 16:00:00"
  },
  "current_user": {
    "user_id": 1,
    "first_name": "Your Name",
    "email": "your@email.com"
  },
  "user_exists": true
}
```

**If Issues Found:**
- `user_exists: false` - Session not finding user
- `current_user: null` - Database query failing
- `session_data: []` - Session data empty

### **B. Test Auth Session Check**
Open in browser:
```
http://localhost/hotel_resto_system/src/login-register/api/auth.php/check-session
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Session active",
  "data": {
    "user_id": 1,
    "email": "your@email.com",
    "user_role": "customer"
  }
}
```

### **C. Test User Profile API**
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
    "first_name": "Your Name",
    "last_name": "Last Name",
    "email": "your@email.com"
  }
}
```

## 🔍 **Step 2: Check Browser Console**

### **Open Developer Tools:**
1. Press `F12` (or `Ctrl+Shift+I`)
2. Go to **Console** tab
3. **Clear console** (click 🚫 icon)
4. **Refresh page** (`F5`)
5. **Look for debug messages:**

**Expected Console Output:**
```
🔍 Starting loadCurrentUser...
🔍 Checking session...
🔍 Session response: {success: true, data: {...}}
✅ Session active, loading user profile...
🔍 Profile response: {success: true, data: {...}}
✅ User data loaded: {user_id: 1, first_name: "..."}
```

**Error Messages to Look For:**
```
❌ No active session found
❌ Profile API failed: {error: "..."}
❌ Failed to load current user: Error: ...
```

## 🔧 **Step 3: Common Issues & Fixes**

### **Issue 1: Session Not Shared**
**Problem**: Login creates session but customer portal can't access it
**Debug**: Session debug shows `user_exists: false`
**Fix**: Check session cookie path/domain

**Add to auth.php**:
```php
// Set session cookie parameters
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => true
]);
```

### **Issue 2: Database Connection**
**Problem**: Customer portal can't connect to database
**Debug**: `database_connected: false` or SQL errors
**Fix**: Check database credentials

**Test Database Connection**:
```php
// Create test_db.php
<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=lucas_customer_portal', 'root', '');
    echo json_encode(['success' => true, 'message' => 'DB Connected']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
```

### **Issue 3: User Not Found**
**Problem**: Session exists but user_id doesn't match
**Debug**: `current_user: null` but session has user_id
**Fix**: Check user data consistency

**Add Debug to user.php**:
```php
// In handleProfile function, add:
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$currentUser['user_id']]);
$dbUser = $stmt->fetch();
echo json_encode(['debug_user' => $dbUser]);
```

### **Issue 4: Frontend JavaScript Errors**
**Problem**: API calls failing due to JavaScript errors
**Debug**: Console shows red error messages
**Fix**: Fix JavaScript syntax/logic

**Common JS Errors:**
- `Cannot read property 'currentUser' of undefined`
- `Failed to fetch` - Network error
- `Unexpected token < in JSON` - API returning HTML

## 🚀 **Step 4: Quick Fixes to Try**

### **Fix 1: Force Session Reload**
Add to customer portal pages:
```javascript
// In console, force session check
window.dbAPI.loadCurrentUser();
```

### **Fix 2: Check API URLs**
Verify correct API calls:
```javascript
// In console, test direct API call
fetch('./api/user.php?endpoint=profile')
  .then(res => res.json())
  .then(data => console.log('Direct API test:', data));
```

### **Fix 3: Clear Session Data**
If session is corrupted:
```php
// Add to any API for debugging
session_destroy();
session_start();
echo json_encode(['message' => 'Session reset']);
```

## 📋 **Debugging Checklist**

### **Run These Tests:**

#### **Session Debug:**
- [ ] Session ID is set and valid
- [ ] Session data contains user_id
- [ ] Session not expired
- [ ] Customer portal can access session

#### **Database Debug:**
- [ ] Database connection successful
- [ ] User exists in database
- [ ] User data is correct
- [ ] No SQL errors

#### **API Debug:**
- [ ] Profile API returns user data
- [ ] Response format is correct JSON
- [ ] No 500 errors
- [ ] All endpoints accessible

#### **Frontend Debug:**
- [ ] No JavaScript console errors
- [ ] API calls are being made
- [ ] Data is being processed correctly
- [ ] UI elements are being updated

## 🎯 **Expected Results**

### **When Fixed:**
- ✅ Session debug shows active user
- ✅ Profile API returns your data
- ✅ Console shows successful loading
- ✅ Dashboard displays your information
- ✅ Profile page shows your details

### **If Still Issues:**
1. **Run debug APIs** - Test each one
2. **Check console** - Look for JavaScript errors
3. **Verify database** - Ensure user data exists
4. **Test network** - Check API responses
5. **Report findings** - Share debug results

Run these debugging steps to identify exactly where the data loading is failing!
