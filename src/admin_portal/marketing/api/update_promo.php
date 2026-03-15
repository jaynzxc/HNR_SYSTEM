<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\api\update_promo.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/PromoCode.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = $_POST;
    
    // Validate required fields
    if (empty($data['code'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Promo code is required'
        ]);
        exit;
    }

    $promoCode = new PromoCode();
    
    // Create promo code
    $result = $promoCode->create([
        'campaign_id' => $data['campaign_id'] ?? null,
        'code' => strtoupper($data['code']),
        'description' => $data['description'] ?? '',
        'max_uses' => $data['max_uses'] ?? 1,
        'is_active' => $data['is_active'] ?? 1
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Promo code created successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create promo code'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>