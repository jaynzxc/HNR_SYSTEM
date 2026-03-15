<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\api\update_campaign.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Campaign.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $data = $_POST;
    
    // Validate required fields
    if (empty($data['campaign_name'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Campaign name is required'
        ]);
        exit;
    }

    $campaign = new Campaign();
    
    // Set campaign properties
    $campaign->id = $data['id'] ?? null;
    $campaign->campaign_name = $data['campaign_name'];
    $campaign->description = $data['description'] ?? '';
    $campaign->campaign_type = $data['campaign_type'] ?? 'discount';
    $campaign->status = $data['status'] ?? 'draft';
    $campaign->discount_type = $data['discount_type'] ?? null;
    $campaign->discount_value = $data['discount_value'] ?? 0;
    $campaign->start_date = $data['start_date'] ?? null;
    $campaign->end_date = $data['end_date'] ?? null;
    $campaign->target_audience = $data['target_audience'] ?? '';
    $campaign->target_redemptions = $data['target_redemptions'] ?? 0;
    $campaign->budget = $data['budget'] ?? 0;
    $campaign->bg_color = $data['bg_color'] ?? 'green-100';
    $campaign->text_color = $data['text_color'] ?? 'green-700';

    // Perform update or create
    if (!empty($campaign->id)) {
        $result = $campaign->update();
        $message = 'Campaign updated successfully';
    } else {
        $result = $campaign->create();
        $message = 'Campaign created successfully';
    }

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save campaign'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>