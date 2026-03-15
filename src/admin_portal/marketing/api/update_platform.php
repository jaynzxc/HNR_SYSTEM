<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\api\update_platform.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Platform.php';

// Handle both JSON and form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check if we received JSON or form data
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        // JSON data
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        // Form data
        $data = $_POST;
    }
    
    // Validate required fields
    if (empty($data['platform_name'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Platform name is required'
        ]);
        exit;
    }

    $platform = new Platform();
    
    // Set platform properties
    $platform->id = $data['id'] ?? null;
    $platform->platform_name = $data['platform_name'];
    $platform->platform_type = $data['platform_type'] ?? 'delivery';
    $platform->status = $data['status'] ?? 'pending';
    $platform->commission_rate = $data['commission_rate'] ?? 0;
    $platform->api_key = $data['api_key'] ?? '';
    $platform->api_secret = $data['api_secret'] ?? '';
    $platform->webhook_url = $data['webhook_url'] ?? '';
    $platform->icon_class = $data['icon_class'] ?? 'globe';
    $platform->bg_color = $data['bg_color'] ?? 'amber-100';

    // Perform update or create
    if (!empty($platform->id)) {
        // Update existing platform
        $result = $platform->update();
        $message = 'Platform updated successfully';
    } else {
        // Create new platform
        $result = $platform->create();
        $message = 'Platform created successfully';
    }

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save platform'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>