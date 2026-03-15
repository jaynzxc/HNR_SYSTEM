<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\api\menu_api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/models/MenuModel.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

$database = new Database();
$db = $database->getConnection();
$menu = new MenuModel($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch($method) {
        case 'GET':
            if($action === 'stats') {
                $data = $menu->getStatistics();
                sendResponse(true, $data, 'Statistics retrieved');
            }
            elseif($action === 'categories') {
                $data = $menu->getCategories();
                sendResponse(true, $data, 'Categories retrieved');
            }
            elseif($action === 'items') {
                $filters = [
                    'category_id' => $_GET['category_id'] ?? null,
                    'search' => $_GET['search'] ?? null
                ];
                $data = $menu->getMenuItems(array_filter($filters));
                sendResponse(true, $data, 'Menu items retrieved');
            }
            elseif($action === 'item' && isset($_GET['id'])) {
                $data = $menu->getMenuItem($_GET['id']);
                if($data) {
                    sendResponse(true, $data, 'Menu item retrieved');
                } else {
                    sendResponse(false, null, 'Item not found', 404);
                }
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if($action === 'item') {
                $result = $menu->createMenuItem($input);
                if($result) {
                    sendResponse(true, ['id' => $result], 'Menu item created');
                } else {
                    sendResponse(false, null, 'Failed to create menu item', 500);
                }
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if($action === 'item' && isset($_GET['id'])) {
                $result = $menu->updateMenuItem($_GET['id'], $input);
                if($result) {
                    sendResponse(true, null, 'Menu item updated');
                } else {
                    sendResponse(false, null, 'Failed to update menu item', 500);
                }
            }
            elseif($action === 'stock' && isset($_GET['id'])) {
                if(!isset($input['quantity'])) {
                    sendResponse(false, null, 'Quantity required', 400);
                }
                $result = $menu->updateStock($_GET['id'], $input['quantity']);
                if($result) {
                    sendResponse(true, null, 'Stock updated');
                } else {
                    sendResponse(false, null, 'Failed to update stock', 500);
                }
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        case 'DELETE':
            if($action === 'item' && isset($_GET['id'])) {
                $result = $menu->deleteMenuItem($_GET['id']);
                if($result) {
                    sendResponse(true, null, 'Menu item deleted');
                } else {
                    sendResponse(false, null, 'Failed to delete menu item', 500);
                }
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        default:
            sendResponse(false, null, 'Method not allowed', 405);
    }
} catch(Exception $e) {
    sendResponse(false, null, 'Server error: ' . $e->getMessage(), 500);
}
?>