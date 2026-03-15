<?php
/**
 * Maya Payment Gateway Integration
 * Handles Maya payment processing and callbacks
 */

require_once '../config/database.php';

class MayaGateway {
    private $db;
    private $publicKey = 'maya_test_public_key';
    private $secretKey = 'maya_test_secret_key';
    private $apiUrl = 'https://api.maya.com/v1/payments';
    
    public function __construct($database) {
        $this->db = $database->getConnection();
    }
    
    /**
     * Create Maya payment
     */
    public function createPayment($paymentId, $amount, $description, $returnUrl, $cancelUrl) {
        $paymentData = [
            'amount' => $amount,
            'currency' => 'PHP',
            'description' => $description,
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'client_reference' => 'PAY' . date('Ymd') . $paymentId,
            'public_key' => $this->publicKey
        ];
        
        // In production, make actual API call to Maya
        // For demo, simulate response
        $response = $this->simulateApiCall($paymentData);
        
        if ($response['success']) {
            // Update payment record with Maya reference
            $sql = "UPDATE payments SET gateway_transaction_id = ?, payment_description = ? WHERE payment_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$response['reference'], json_encode($response), $paymentId]);
            
            return [
                'success' => true,
                'payment_url' => $response['payment_url'],
                'reference' => $response['reference']
            ];
        } else {
            return [
                'success' => false,
                'error' => $response['error']
            ];
        }
    }
    
    /**
     * Process Maya callback
     */
    public function processCallback($callbackData) {
        $reference = $callbackData['client_reference'] ?? '';
        $status = $callbackData['status'] ?? '';
        $transactionId = $callbackData['transaction_id'] ?? '';
        
        // Find payment by reference
        $sql = "SELECT * FROM payments WHERE payment_reference = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reference]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($payment) {
            // Update payment status
            $newStatus = ($status === 'success') ? 'completed' : 'failed';
            $sql = "UPDATE payments SET status = ?, gateway_transaction_id = ?, paid_at = CURRENT_TIMESTAMP WHERE payment_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newStatus, $transactionId, $payment['payment_id']]);
            
            // Update related entity status if payment successful
            if ($status === 'success') {
                $this->updateEntityStatus($payment['payment_type'], $payment['related_entity_id'], 'confirmed');
            }
            
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Payment not found'];
    }
    
    /**
     * Simulate API call (for demo)
     */
    private function simulateApiCall($data) {
        // Simulate API delay
        usleep(1200000); // 1.2 seconds
        
        // Simulate success
        return [
            'success' => true,
            'reference' => 'MAYA' . time(),
            'payment_url' => 'https://maya.com/pay/' . uniqid(),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+15 minutes'))
        ];
    }
    
    /**
     * Update entity status
     */
    private function updateEntityStatus($paymentType, $entityId, $status) {
        switch ($paymentType) {
            case 'hotel_booking':
                $sql = "UPDATE hotel_bookings SET booking_status = ? WHERE booking_id = ?";
                break;
            case 'restaurant_reservation':
                $sql = "UPDATE restaurant_reservations SET reservation_status = ? WHERE reservation_id = ?";
                break;
            case 'food_order':
                $sql = "UPDATE food_orders SET order_status = ? WHERE order_id = ?";
                break;
            default:
                return false;
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $entityId]);
    }
}

// Handle callback from Maya
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_reference'])) {
    $database = new Database();
    $maya = new MayaGateway($database);
    
    $result = $maya->processCallback($_POST);
    
    if ($result['success']) {
        echo 'OK';
    } else {
        echo 'ERROR';
    }
    exit;
}
?>
