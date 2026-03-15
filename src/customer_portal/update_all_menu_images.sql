-- Complete SQL Script to Update ALL Menu Items with Images
-- This script will update the image_url field for all menu items

-- First, let's see what items currently exist in the database
SELECT item_id, item_name, image_url FROM menu_items ORDER BY item_id;

-- Update all menu items with their corresponding images
UPDATE menu_items SET image_url = 'Menu Pics/Beef Nilaga.jpeg' WHERE item_name LIKE '%Beef Nilaga%';
UPDATE menu_items SET image_url = 'Menu Pics/Beef Steak.jpg' WHERE item_name LIKE '%Beef Steak%';
UPDATE menu_items SET image_url = 'Menu Pics/Brewed Coffee.jpeg' WHERE item_name LIKE '%Brewed Coffee%' OR item_name LIKE '%Coffee%';
UPDATE menu_items SET image_url = 'Menu Pics/Buko Juice.jpeg' WHERE item_name LIKE '%Buko Juice%' OR item_name LIKE '%Buko%';
UPDATE menu_items SET image_url = 'Menu Pics/Caesar Salad.jpeg' WHERE item_name LIKE '%Caesar Salad%' OR item_name LIKE '%Salad%';
UPDATE menu_items SET image_url = 'Menu Pics/Calamares.jpeg' WHERE item_name LIKE '%Calamares%';
UPDATE menu_items SET image_url = 'Menu Pics/Cheese Platter.jpeg' WHERE item_name LIKE '%Cheese Platter%' OR item_name LIKE '%Cheese%';
UPDATE menu_items SET image_url = 'Menu Pics/Chicken Tinola.jpeg' WHERE item_name LIKE '%Chicken Tinola%' OR item_name LIKE '%Tinola%';
UPDATE menu_items SET image_url = 'Menu Pics/Chocolate Cake.jpeg' WHERE item_name LIKE '%Chocolate Cake%' OR item_name LIKE '%Cake%' OR item_name LIKE '%Chocolate%';
UPDATE menu_items SET image_url = 'Menu Pics/Crispy Pata.jpeg' WHERE item_name LIKE '%Crispy Pata%' OR item_name LIKE '%Pata%';
UPDATE menu_items SET image_url = 'Menu Pics/Garden Salad.jpeg' WHERE item_name LIKE '%Garden Salad%' OR item_name LIKE '%Garden%';
UPDATE menu_items SET image_url = 'Menu Pics/Garlic Rice.jpeg' WHERE item_name LIKE '%Garlic Rice%' OR item_name LIKE '%Rice%';
UPDATE menu_items SET image_url = 'Menu Pics/Grilled Salmon.jpeg' WHERE item_name LIKE '%Grilled Salmon%' OR item_name LIKE '%Salmon%';
UPDATE menu_items SET image_url = 'Menu Pics/Halo-Halo.jpeg' WHERE item_name LIKE '%Halo-Halo%' OR item_name LIKE '%Halo%';
UPDATE menu_items SET image_url = 'Menu Pics/Iced Tea.jpeg' WHERE item_name LIKE '%Iced Tea%' OR item_name LIKE '%Tea%';
UPDATE menu_items SET image_url = 'Menu Pics/Leche Flan.jpeg' WHERE item_name LIKE '%Leche Flan%' OR item_name LIKE '%Flan%';
UPDATE menu_items SET image_url = 'Menu Pics/Lumping Shanghai.jpeg' WHERE item_name LIKE '%Lumpia%' OR item_name LIKE '%Lumping%' OR item_name LIKE '%Shanghai%';
UPDATE menu_items SET image_url = 'Menu Pics/Mango Shake.jpeg' WHERE item_name LIKE '%Mango Shake%' OR item_name LIKE '%Mango%';
UPDATE menu_items SET image_url = 'Menu Pics/Sinigang na Baboy.jpeg' WHERE item_name LIKE '%Sinigang%' OR item_name LIKE '%Sinigang na Baboy%';
UPDATE menu_items SET image_url = 'Menu Pics/Sizzling Sisig.jpeg' WHERE item_name LIKE '%Sisig%' OR item_name LIKE '%Sizzling%';

-- Verify the updates
SELECT item_id, item_name, image_url FROM menu_items WHERE image_url IS NOT NULL ORDER BY item_id;

-- Check for any items that still don't have images
SELECT item_id, item_name, image_url FROM menu_items WHERE image_url IS NULL OR image_url = '' ORDER BY item_id;

-- Count how many items now have images
SELECT 
    COUNT(*) as total_items,
    COUNT(CASE WHEN image_url IS NOT NULL AND image_url != '' THEN 1 END) as items_with_images,
    COUNT(CASE WHEN image_url IS NULL OR image_url = '' THEN 1 END) as items_without_images
FROM menu_items;
