<?php
/**
 * Credit Card Payment Gateway Integration
 * Handles credit card payment processing
 */

require_once '../config/database.php';

class CreditCardGateway {
    private $db;
    private $merchantId = 'test_merchant_123';
    private $apiUrl = 'https://api.paymentgateway.com/v1/charge';
    
    public function __construct($database) {
        $this->db = $database->getConnection();
    }
    
    /**
     * Process credit card payment
     */
    public function processPayment($paymentId, $cardData, $amount, $description) {
        // Validate card data
        $validation = $this->validateCardData($cardData);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }
        
        $paymentData = [
            'merchant_id' => $this->merchantId,
            'amount' => $amount,
            'currency' => 'PHP',
            'description' => $description,
            'card_number' => $cardData['card_number'],
            'expiry_month' => $cardData['expiry_month'],
            'expiry_year' => $cardData['expiry_year'],
            'cvv' => $cardData['cvv'],
            'cardholder_name' => $cardData['cardholder_name']
        ];
        
        // In production, make actual API call to payment gateway
        // For demo, simulate response
        $response = $this->simulateApiCall($paymentData);
        
        if ($response['success']) {
            // Update payment record
            $sql = "UPDATE payments SET status = 'completed', gateway_transaction_id = ?, paid_at = CURRENT_TIMESTAMP, payment_description = ? WHERE payment_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$response['transaction_id'], json_encode($response), $paymentId]);
            
            // Update related entity status
            $this->updateEntityStatus($response['payment_type'], $response['entity_id'], 'confirmed');
            
            return [
                'success' => true,
                'transaction_id' => $response['transaction_id'],
                'auth_code' => $response['auth_code'],
                'message' => 'Payment successful'
            ];
        } else {
            // Update payment status to failed
            $sql = "UPDATE payments SET status = 'failed', payment_description = ? WHERE payment_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([json_encode($response), $paymentId]);
            
            return [
                'success' => false,
                'error' => $response['error'],
                'message' => 'Payment declined'
            ];
        }
    }
    
    /**
     * Validate card data
     */
    private function validateCardData($cardData) {
        // Check required fields
        $required = ['card_number', 'expiry_month', 'expiry_year', 'cvv', 'cardholder_name'];
        foreach ($required as $field) {
            if (empty($cardData[$field])) {
                return ['valid' => false, 'error' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
            }
        }
        
        // Validate card number (basic Luhn algorithm check)
        $cardNumber = preg_replace('/\D/', '', $cardData['card_number']);
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return ['valid' => false, 'error' => 'Invalid card number'];
        }
        
        // Validate expiry
        $expiryMonth = intval($cardData['expiry_month']);
        $expiryYear = intval($cardData['expiry_year']);
        $currentYear = intval(date('Y'));
        $currentMonth = intval(date('m'));
        
        if ($expiryMonth < 1 || $expiryMonth > 12) {
            return ['valid' => false, 'error' => 'Invalid expiry month'];
        }
        
        if ($expiryYear < $currentYear || ($expiryYear == $currentYear && $expiryMonth < $currentMonth)) {
            return ['valid' => false, 'error' => 'Card has expired'];
        }
        
        // Validate CVV
        $cvv = $cardData['cvv'];
        if (strlen($cvv) < 3 || strlen($cvv) > 4 || !ctype_digit($cvv)) {
            return ['valid' => false, 'error' => 'Invalid CVV'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Simulate API call (for demo)
     */
    private function simulateApiCall($data) {
        // Simulate processing delay
        usleep(2000000); // 2 seconds
        
        // Simulate 85% success rate
        if (rand(1, 100) <= 85) {
            return [
                'success' => true,
                'transaction_id' => 'CC' . time() . rand(1000, 9999),
                'auth_code' => 'AUTH' . rand(100000, 999999),
                'payment_type' => 'credit_card',
                'entity_id' => $data['entity_id'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Insufficient funds',
                'decline_code' => '05'
            ];
        }
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
    
    /**
     * Get card type from number
     */
    public function getCardType($cardNumber) {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        if (preg_match('/^4/', $cardNumber)) {
            return 'visa';
        } elseif (preg_match('/^5[1-5]/', $cardNumber)) {
            return 'mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            return 'amex';
        } elseif (preg_match('/^6/', $cardNumber)) {
            return 'discover';
        }
        
        return 'unknown';
    }
    
    /**
     * Format card number for display
     */
    public function formatCardNumber($cardNumber) {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        $length = strlen($cardNumber);
        
        if ($length <= 4) {
            return $cardNumber;
        }
        
        // Show last 4 digits, mask the rest
        $lastFour = substr($cardNumber, -4);
        $masked = str_repeat('*', $length - 4) . $lastFour;
        
        // Add spacing for readability
        return chunk_split($masked, 4, ' ');
    }
}
?>
