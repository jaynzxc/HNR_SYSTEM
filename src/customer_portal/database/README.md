# Lùcas Customer Portal Database

This directory contains the complete database schema and seed data for the Lùcas Hotel & Restaurant Customer Portal system.

## Files Overview

- **`01_schema.sql`** - Complete database schema with empty tables
- **`02_seed_data.sql`** - Sample seed data based on existing customer portal data
- **`README.md`** - This documentation file

## Database Structure

The database consists of 20 interconnected tables that support the full customer portal functionality:

### Core Tables
1. **users** - Customer accounts and profiles
2. **hotel_rooms** - Hotel room inventory
3. **hotel_bookings** - Hotel reservation records
4. **restaurant_tables** - Restaurant table management
5. **restaurant_reservations** - Restaurant booking system
6. **menu_categories** - Food menu categories
7. **menu_items** - Individual menu items with pricing
8. **food_orders** - Customer food orders
9. **food_order_items** - Detailed order line items

### Supporting Tables
10. **payment_methods** - Customer payment options
11. **transactions** - Financial transaction records
12. **loyalty_rewards** - Available reward options
13. **user_reward_redemptions** - Customer reward usage
14. **notifications** - System notifications
15. **user_notification_preferences** - Communication preferences
16. **user_reviews** - Customer feedback and ratings
17. **points_history** - Loyalty points tracking
18. **waiting_list** - Restaurant/hotel waiting queue
19. **user_sessions** - Authentication sessions
20. **audit_log** - System activity tracking

## Installation Instructions

### Prerequisites
- MySQL 8.0+ or MariaDB 10.5+
- Database creation privileges
- Basic SQL knowledge

### Step 1: Create Database
```sql
CREATE DATABASE lucas_customer_portal;
USE lucas_customer_portal;
```

### Step 2: Import Schema
```bash
mysql -u username -p lucas_customer_portal < 01_schema.sql
```

### Step 3: Import Seed Data (Optional)
```bash
mysql -u username -p lucas_customer_portal < 02_seed_data.sql
```

## Seed Data Overview

The seed data includes:

### Users
- **Mia Cruz** (Gold member, 1,240 points) - Main demo user
- **Juan Mateo** (Silver member, 680 points)
- **Sofia Reyes** (Member, 150 points)
- **Carlos Santos** (Platinum member, 2,150 points)

### Hotel Rooms
- Deluxe Twin Rooms (₱4,200/night)
- Ocean Suites (₱6,900/night)
- Executive Suite (₱8,500/night)
- Presidential Suite (₱15,000/night)

### Menu Items
- **Appetizers**: Calamares, Lumpiang Shanghai, Cheese Platter
- **Mains**: Sinigang na Baboy, Sizzling Sisig, Crispy Pata, Garlic Rice
- **Desserts**: Halo-Halo, Leche Flan, Chocolate Cake
- **Beverages**: Fresh Buko Juice, Coffee, Iced Tea, Mango Shake

### Sample Data
- Hotel bookings and reservations
- Food orders and transactions
- Loyalty rewards and redemptions
- Notifications and reviews
- Points history

## Key Features

### Loyalty System
- **Points Earning**: 5 pts per ₱100 (hotel), 3 pts per ₱100 (dining)
- **Tier Levels**: Member → Silver (1,000 pts) → Gold (2,000 pts) → Platinum (5,000 pts)
- **Rewards**: Free items, discounts, upgrades, services

### Payment Integration
- Multiple payment methods (GCash, Maya, Credit Cards, Cash)
- Transaction tracking and history
- Deposit system for reservations

### Notifications
- Multi-channel delivery (Email, SMS, In-app)
- User preference controls
- Automated booking confirmations and reminders

### Review System
- 5-star rating system
- Multiple review types (hotel, dining, food items)
- Staff response capabilities

## Database Views

### Pre-defined Views for Common Queries
- **user_dashboard_view** - Complete user summary
- **active_bookings_view** - Current hotel bookings
- **today_reservations_view** - Today's restaurant reservations

## Triggers and Automation

### Automatic Updates
- **update_users_timestamp** - Updates user modified timestamps
- **update_user_points_after_history** - Syncs loyalty points balance

## Security Considerations

### Data Protection
- Sensitive data encrypted (payment methods)
- Audit logging for all changes
- Session management for authentication

### Access Control
- User-based permissions through foreign keys
- Cascade deletes for data integrity
- Status fields for soft deletes

## Performance Optimization

### Indexes
- Primary key indexes on all tables
- Foreign key indexes for join performance
- Composite indexes for common query patterns
- Status and date indexes for filtering

### Query Optimization
- Views for complex joins
- Efficient data types (DECIMAL for currency)
- Proper normalization to reduce redundancy

## Development Notes

### Auto-increment Values
- Set to start at 100+ to avoid conflicts with seed data
- Allows clean separation between seed and production data

### JSON Fields
- Used for flexible data storage (amenities, allergens, dietary info)
- Provides extensibility without schema changes

### Enum Types
- Ensures data consistency
- Improves query performance
- Provides clear business logic constraints

## Integration with Frontend

### Data Mapping
The database schema aligns with the frontend JavaScript objects found in the customer portal:

```javascript
// Frontend user object maps to users table
{
  name: 'Mia Cruz',           → first_name + last_name
  email: 'mia.cruz@email.com', → email
  phone: '+63 917 555 1234',  → phone
  membership: 'gold',         → membership_tier
  points: 1240,               → loyalty_points
  // ... other fields
}
```

### API Endpoints
The schema supports RESTful API patterns:
- `GET /users/{id}` - User profile
- `GET /users/{id}/bookings` - User bookings
- `POST /orders` - Create food order
- `GET /rewards` - Available rewards

## Maintenance

### Regular Tasks
- Clear expired sessions
- Archive old audit logs
- Update reward availability
- Process point expirations

### Backup Strategy
- Daily full backups
- Transaction log backups
- Point-in-time recovery capability

## Troubleshooting

### Common Issues
1. **Foreign Key Constraints** - Ensure data integrity during imports
2. **Character Encoding** - Use UTF-8 for special characters
3. **Time Zones** - All timestamps stored in UTC
4. **JSON Validation** - Ensure valid JSON format in JSON fields

### Performance Monitoring
- Monitor slow query logs
- Check index usage statistics
- Optimize frequent queries
- Monitor database size growth

## Future Enhancements

### Planned Features
- Multi-hotel support
- Advanced analytics dashboard
- Mobile app backend APIs
- Integration with external booking systems
- Real-time notifications via WebSockets

### Scalability Considerations
- Database sharding for multi-location support
- Read replicas for reporting queries
- Caching layer for frequently accessed data
- Connection pooling optimization

---

**Last Updated**: March 12, 2026  
**Version**: 1.0  
**Compatibility**: MySQL 8.0+, MariaDB 10.5+
