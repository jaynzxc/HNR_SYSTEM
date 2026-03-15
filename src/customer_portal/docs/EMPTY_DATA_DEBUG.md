# Empty Data Debug Guide

## 🎯 **Debugging Empty Customer Portal Data**

### 🧪 **Step-by-Step Testing**

#### **1. Test Session Debug API**
Open in browser AFTER logging in:
```
http://localhost/hotel_resto_system/src/customer_portal/api/session_debug.php
```

**Expected Response (when logged in):**
```json
{
  "session_id": "abc123...",
  "session_status": 2,
  "session_data": {
    "user_id": 1,
    "session_id": "abc123...",
    "expires_at": "2026-03-12 15:00:00",
    "user_role": "customer"
  },
  "cookie_data": {
    "PHPSESSID": "abc123..."
  }
}
```

**If Session is Empty:**
```json
{
  "session_id": "",
  "session_status": 1,
  "session_data": [],
  "cookie_data": []
}
```

#### **2. Test Database Debug API**
Open in browser:
```
http://localhost/hotel_resto_system/src/customer_portal/api/db_debug.php
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Database connection successful",
  "session_id": "abc123...",
  "session_data": {
    "user_id": 1,
    "user_role": "customer"
  },
  "database_connected": true
}
```

#### **3. Test User Profile API**
Open in browser:
```
http://localhost/hotel_resto_system/src/customer_portal/api/user/profile
```

**Expected Response:**
```json
{
  "success": true,
  "message": "User profile retrieved",
  "data": {
    "user_id": 1,
    "first_name": "Mia",
    "last_name": "Cruz",
    "email": "mia.cruz@email.com",
    "membership_tier": "gold",
    "loyalty_points": 1240
  }
}
```

### 🔍 **Common Issues & Solutions**

#### **Issue 1: Session Not Shared**
**Problem**: Login creates session, but customer portal can't access it
**Solution**: Check session path and domain settings

**Debug Code** (add to any API):
```php
echo "Session save path: " . session_save_path() . "\n";
echo "Session cookie params: " . json_encode(session_get_cookie_params()) . "\n";
```

#### **Issue 2: Database Connection Fails**
**Problem**: Customer portal can't connect to database
**Solution**: Check database credentials and connection

**Debug Code**:
```php
try {
    $db = new PDO('mysql:host=localhost;dbname=lucas_customer_portal', 'root', '');
    echo "Database: CONNECTED\n";
} catch (PDOException $e) {
    echo "Database: FAILED - " . $e->getMessage() . "\n";
}
```

#### **Issue 3: User Not Found in Database**
**Problem**: Session exists but user_id doesn't match any user
**Solution**: Check user data and session consistency

**Debug Code**:
```php
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    echo "User found: " . ($user ? 'YES' : 'NO') . "\n";
    if ($user) {
        echo "User email: " . $user['email'] . "\n";
    }
}
```

#### **Issue 4: JavaScript Errors**
**Problem**: Frontend JavaScript not loading data properly
**Solution**: Check browser console for errors

**Debug Code** (add to database.js):
```javascript
async loadCurrentUser() {
    console.log('Loading current user...');
    try {
        const sessionResponse = await this.apiRequest('../login-register/api/auth.php/check-session');
        console.log('Session response:', sessionResponse);
        
        if (!sessionResponse.success) {
            console.log('No active session');
            return null;
        }
        
        const response = await this.apiRequest('user/profile');
        console.log('Profile response:', response);
        
        if (response.success) {
            this.currentUser = response.data;
            console.log('Current user set:', this.currentUser);
            this.updateUIWithUserData();
            return response.data;
        }
        return null;
    } catch (error) {
        console.error('Failed to load current user:', error);
        return null;
    }
}
```

### 🚀 **Immediate Actions**

#### **1. Clear Everything**
```bash
# Clear browser cache
Ctrl + F5

# Clear PHP sessions (delete session files)
rm -f /tmp/sess_*

# Restart XAMPP
# Stop and start Apache/MySQL
```

#### **2. Test Fresh Login**
1. Open incognito/private browsing window
2. Go to login page
3. Login with test credentials
4. Check debug APIs immediately

#### **3. Check File Permissions**
```bash
# Ensure session directory is writable
ls -la /tmp/
chmod 777 /tmp/

# Check if PHP can write sessions
echo "<?php session_start(); ?>" > test_session.php
php test_session.php
```

#### **4. Verify Database Data**
```sql
-- Check if user exists
SELECT * FROM users WHERE email = 'mia.cruz@email.com';

-- Check session table
SELECT * FROM user_sessions WHERE expires_at > NOW();

-- Check user data
SELECT user_id, first_name, last_name, email FROM users WHERE user_id = 1;
```

### 📋 **Debugging Checklist**

#### **Session Debug**
- [ ] Session ID is set after login
- [ ] Session data contains user_id
- [ ] Session persists across page loads
- [ ] Customer portal can access session

#### **Database Debug**
- [ ] Database connection successful
- [ ] User exists in database
- [ ] User data is correct
- [ ] Session table has active session

#### **API Debug**
- [ ] User profile API returns data
- [ ] Booking API returns data
- [ ] All APIs return valid JSON
- [ ] No 500 errors

#### **Frontend Debug**
- [ ] No JavaScript console errors
- [ ] API calls are being made
- [ ] Data is being loaded into UI
- [ ] User interface updates correctly

### 🔧 **Quick Fixes to Try**

#### **Fix 1: Force Session Start**
Add to top of all customer portal APIs:
```php
// Force session start
session_start();
```

#### **Fix 2: Check Session Path**
Add to login API:
```php
// Set explicit session path
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => true
]);
```

#### **Fix 3: Debug Database Connection**
Add to database config:
```php
// Enable PDO error mode
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];
```

### 🎯 **Expected Debug Results**

#### **Working System Should Show:**
1. Session debug: Active session with user data
2. Database debug: Successful connection
3. Profile API: User data returned
4. Frontend: Data loaded into UI
5. Customer portal: Shows user information

#### **If Still Empty:**
1. Check debug API responses
2. Identify where the chain breaks
3. Fix the specific issue
4. Test again
5. Verify complete flow

Run these debugging steps to identify exactly where the data loading is failing!
