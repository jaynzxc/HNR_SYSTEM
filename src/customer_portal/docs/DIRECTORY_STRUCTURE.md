# Hotel Restaurant System - Directory Structure

## 📁 Customer Portal (`src/customer_portal/`)

### 📄 Main HTML Files
- `index.html` - Customer dashboard/homepage
- `hotel_booking.html` - Hotel room booking
- `restaurant_reservation.html` - Restaurant table reservation
- `order_food.html` - Food ordering system
- `payments.html` - Payment processing
- `loyalty_rewards.html` - Loyalty points and rewards
- `my_profile.html` - User profile management
- `my_reservation.html` - Reservation history
- `Notifications.html` - Notification center
- `reviews.html` - Customer reviews and feedback

### 📁 API Organization
```
api/
├── auth/
│   ├── simple_user.php          # User authentication & data
│   ├── user.php                 # User management
│   └── notification_preferences.php # Notification settings
├── booking/
│   └── booking.php              # Booking operations
├── payment/
│   └── payment_methods.php      # Payment method management
└── debug/
    ├── db_debug.php            # Database debugging
    ├── debug_simple_user.php   # User API debugging
    ├── session_debug.php       # Session debugging
    ├── current_user_debug.php  # Current user debugging
    └── test.php                # General testing
```

### 📁 Supporting Directories
- `js/` - JavaScript files (database.js, etc.)
- `config/` - Configuration files
- `database/` - Database connection/setup files
- `helpers/` - Helper functions and utilities
- `models/` - Data models
- `docs/` - Documentation and guides

---

## 📁 Login/Register (`src/login-register/`)

### 📄 Main HTML Files
- `login_form.html` - User login page
- `register_form.html` - User registration page
- `test_login.html` - Login testing page

### 📁 API Organization
```
api/
├── auth/
│   └── auth.php                # Authentication operations
├── debug/
│   ├── debug.php               # General debugging
│   ├── debug_login_response.php # Login response debugging
│   ├── debug_redirect.php      # Redirect debugging
│   ├── test_login_api.php      # Login API testing
│   ├── test_login_form.php     # Login form testing
│   └── create_test_user.php    # Test user creation
└── check_database.php          # Database connectivity check
```

### 📁 Supporting Directories
- `docs/` - Documentation and guides

---

## 🎯 Organization Benefits

### ✅ **Improved Navigation**
- Clear separation of concerns
- Logical grouping of related files
- Easy to locate specific functionality

### ✅ **Better Maintainability**
- Documentation files isolated from code
- API endpoints organized by function
- Debug files separated from production code

### ✅ **Enhanced Development**
- Faster file location and editing
- Clear structure for new developers
- Reduced clutter in main directories

### ✅ **Scalability**
- Easy to add new features in appropriate directories
- Clear expansion points for future development
- Maintained organization as project grows

---

## 📋 File Migration Summary

### Customer Portal Changes
- **10 documentation files** moved to `docs/`
- **API files** organized into 4 subdirectories:
  - `auth/` (3 files) - Authentication & user management
  - `booking/` (1 file) - Booking operations  
  - `payment/` (1 file) - Payment processing
  - `debug/` (5 files) - Debug & testing utilities

### Login/Register Changes  
- **5 documentation files** moved to `docs/`
- **API files** organized into 2 subdirectories:
  - `auth/` (1 file) - Authentication
  - `debug/` (6 files) - Debug & testing utilities

---

## 🔧 Next Steps

### ⚠️ **Important Considerations**
- Update any hardcoded file paths in HTML/JavaScript files
- Verify API endpoints still work with new paths
- Test all functionality after reorganization
- Update any documentation that references old file paths

### 🔄 **Path Updates Needed**
- HTML files referencing API endpoints may need path updates
- JavaScript files with hardcoded API paths
- Any configuration files with file references

---

*Last Updated: March 12, 2026*
*Organization Status: ✅ Complete*
