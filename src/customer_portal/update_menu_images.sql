-- SQL Script to Update Menu Items with Image URLs
-- This script will update the image_url field for menu items

UPDATE menu_items SET image_url = 'Menu Pics/Beef Nilaga.jpeg' WHERE item_name LIKE '%Beef Nilaga%';
UPDATE menu_items SET image_url = 'Menu Pics/Beef Steak.jpg' WHERE item_name LIKE '%Beef Steak%';
UPDATE menu_items SET image_url = 'Menu Pics/Brewed Coffee.jpeg' WHERE item_name LIKE '%Brewed Coffee%';
UPDATE menu_items SET image_url = 'Menu Pics/Buko Juice.jpeg' WHERE item_name LIKE '%Buko Juice%';
UPDATE menu_items SET image_url = 'Menu Pics/Caesar Salad.jpeg' WHERE item_name LIKE '%Caesar Salad%';
UPDATE menu_items SET image_url = 'Menu Pics/Calamares.jpeg' WHERE item_name LIKE '%Calamares%';
UPDATE menu_items SET image_url = 'Menu Pics/Cheese Platter.jpeg' WHERE item_name LIKE '%Cheese Platter%';
UPDATE menu_items SET image_url = 'Menu Pics/Chicken Tinola.jpeg' WHERE item_name LIKE '%Chicken Tinola%';
UPDATE menu_items SET image_url = 'Menu Pics/Chocolate Cake.jpeg' WHERE item_name LIKE '%Chocolate Cake%';
UPDATE menu_items SET image_url = 'Menu Pics/Crispy Pata.jpeg' WHERE item_name LIKE '%Crispy Pata%';
UPDATE menu_items SET image_url = 'Menu Pics/Garden Salad.jpeg' WHERE item_name LIKE '%Garden Salad%';
UPDATE menu_items SET image_url = 'Menu Pics/Garlic Rice.jpeg' WHERE item_name LIKE '%Garlic Rice%';
UPDATE menu_items SET image_url = 'Menu Pics/Grilled Salmon.jpeg' WHERE item_name LIKE '%Grilled Salmon%';
UPDATE menu_items SET image_url = 'Menu Pics/Halo-Halo.jpeg' WHERE item_name LIKE '%Halo-Halo%';
UPDATE menu_items SET image_url = 'Menu Pics/Iced Tea.jpeg' WHERE item_name LIKE '%Iced Tea%';
UPDATE menu_items SET image_url = 'Menu Pics/Leche Flan.jpeg' WHERE item_name LIKE '%Leche Flan%';
UPDATE menu_items SET image_url = 'Menu Pics/Lumping Shanghai.jpeg' WHERE item_name LIKE '%Lumpia%' OR item_name LIKE '%Lumping%';
UPDATE menu_items SET image_url = 'Menu Pics/Mango Shake.jpeg' WHERE item_name LIKE '%Mango Shake%';
UPDATE menu_items SET image_url = 'Menu Pics/Sinigang na Baboy.jpeg' WHERE item_name LIKE '%Sinigang%' OR item_name LIKE '%Sinigang na Baboy%';
UPDATE menu_items SET image_url = 'Menu Pics/Sizzling Sisig.jpeg' WHERE item_name LIKE '%Sisig%' OR item_name LIKE '%Sizzling Sisig%';

-- Check the results
SELECT item_id, item_name, image_url FROM menu_items WHERE image_url IS NOT NULL;
