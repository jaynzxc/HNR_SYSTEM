-- =============================================
-- SAMPLE MENU ITEMS INSERTION
-- =============================================

-- Insert menu categories first
INSERT INTO menu_categories (category_name, category_description, display_order, is_active) VALUES
('Mains', 'Main courses and signature dishes', 1, TRUE),
('Appetizers', 'Starters and small plates', 2, TRUE),
('Desserts', 'Sweet treats and desserts', 3, TRUE),
('Beverages', 'Drinks and beverages', 4, TRUE);

-- Insert sample menu items
INSERT INTO menu_items (category_id, item_name, item_description, price, item_status, preparation_time_minutes, spicy_level, is_signature) VALUES
-- Mains (category_id = 1)
(1, 'Sinigang na Baboy', 'Traditional Filipino sour soup with pork and vegetables', 320.00, 'available', 25, 'mild', TRUE),
(1, 'Sizzling Sisig', 'Chopped pork with onions, served sizzling hot', 290.00, 'available', 15, 'medium', FALSE),
(1, 'Crispy Pata', 'Deep-fried pork knuckle with garlic rice', 550.00, 'available', 35, 'none', TRUE),
(1, 'Garlic Rice', 'Fragrant garlic fried rice', 50.00, 'available', 10, 'none', FALSE),

-- Appetizers (category_id = 2)
(2, 'Calamares', 'Crispy fried squid rings with dipping sauce', 180.00, 'available', 15, 'none', FALSE),
(2, 'Tuna Pie', 'Creamy tuna pie with vegetables', 150.00, 'available', 12, 'none', FALSE),

-- Desserts (category_id = 3)
(3, 'Halo-Halo', 'Filipino shaved ice dessert with fruits and leche flan', 150.00, 'available', 5, 'none', TRUE),
(3, 'Leche Flan', 'Caramel custard dessert', 120.00, 'available', 3, 'none', FALSE),

-- Beverages (category_id = 4)
(4, 'Fresh Buko Juice', 'Fresh coconut juice with pulp', 90.00, 'available', 3, 'none', FALSE),
(4, 'Calamansi Juice', 'Filipino limeade', 70.00, 'available', 3, 'none', FALSE);
