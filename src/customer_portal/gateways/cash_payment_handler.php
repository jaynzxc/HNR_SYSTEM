<?php
/**
 * Cash Payment Handler
 * Handles cash payments and generates payment slips
 */

require_once '../config/database.php';

class CashPaymentHandler {
    private $db;
    
    public function __construct($database) {
        $this->db = $database->getConnection();
    }
    
    /**
     * Process cash payment
     */
    public function processPayment($paymentId, $amount, $description, $paymentType, $entityId) {
        // Generate cash payment reference
        $reference = 'CASH' . date('Ymd') . $paymentId;
        
        // Create cash payment record
        $sql = "UPDATE payments SET status = 'pending', gateway_transaction_id = ?, payment_description = ?, due_date = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 24 HOUR) WHERE payment_id = ?";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$reference, json_encode(['payment_type' => 'cash', 'due_by' => date('Y-m-d H:i:s', strtotime('+24 hours'))]), $paymentId]);
        
        if ($result) {
            // Generate payment slip
            $paymentSlip = $this->generatePaymentSlip($paymentId, $reference, $amount, $description);
            
            return [
                'success' => true,
                'reference' => $reference,
                'payment_slip' => $paymentSlip,
                'due_date' => date('Y-m-d H:i:s', strtotime('+24 hours')),
                'message' => 'Cash payment recorded. Please present this slip at the counter.'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to process cash payment'
            ];
        }
    }
    
    /**
     * Generate payment slip
     */
    private function generatePaymentSlip($paymentId, $reference, $amount, $description) {
        $sql = "SELECT p.*, u.first_name, u.last_name FROM payments p JOIN users u ON p.user_id = u.user_id WHERE p.payment_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            return null;
        }
        
        return [
            'reference' => $reference,
            'amount' => $amount,
            'description' => $description,
            'customer_name' => $payment['first_name'] . ' ' . $payment['last_name'],
            'payment_type' => $payment['payment_type'],
            'entity_id' => $payment['related_entity_id'],
            'generated_date' => date('Y-m-d H:i:s'),
            'due_date' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'barcode' => $this->generateBarcode($reference),
            'qr_code' => $this->generateQRCode($reference)
        ];
    }
    
    /**
     * Confirm cash payment (used by staff)
     */
    public function confirmCashPayment($reference, $staffId, $notes = '') {
        // Find payment by reference
        $sql = "SELECT * FROM payments WHERE gateway_transaction_id = ? AND payment_method_id IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reference]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($payment) {
            // Update payment status
            $sql = "UPDATE payments SET status = 'completed', paid_at = CURRENT_TIMESTAMP, payment_description = ?, notes = ? WHERE payment_id = ?";
            $stmt = $this->db->prepare($sql);
            
            $paymentData = json_decode($payment['payment_description'] ?? '{}', true);
            $paymentData['confirmed_by'] = $staffId;
            $paymentData['confirmed_at'] = date('Y-m-d H:i:s');
            $paymentData['staff_notes'] = $notes;
            
            $result = $stmt->execute([json_encode($paymentData), $notes, $payment['payment_id']]);
            
            if ($result) {
                // Update related entity status
                $this->updateEntityStatus($payment['payment_type'], $payment['related_entity_id'], 'confirmed');
                
                return ['success' => true, 'message' => 'Cash payment confirmed'];
            }
        }
        
        return ['success' => false, 'error' => 'Payment not found or already processed'];
    }
    
    /**
     * Generate barcode (simulated)
     */
    private function generateBarcode($reference) {
        // In production, use actual barcode library
        return [
            'type' => 'CODE128',
            'data' => $reference,
            'image_url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
        ];
    }
    
    /**
     * Generate QR code (simulated)
     */
    private function generateQRCode($reference) {
        // In production, use actual QR code library
        return [
            'data' => $reference,
            'image_url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
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
    
    /**
     * Get pending cash payments
     */
    public function getPendingCashPayments() {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.email 
                FROM payments p 
                JOIN users u ON p.user_id = u.user_id 
                WHERE p.status = 'pending' AND p.payment_method_id IS NULL 
                ORDER BY p.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payment slip details
     */
    public function getPaymentSlip($reference) {
        $sql = "SELECT * FROM payments WHERE gateway_transaction_id = ? AND payment_method_id IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reference]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($payment) {
            $paymentData = json_decode($payment['payment_description'] ?? '{}', true);
            return [
                'reference' => $payment['gateway_transaction_id'],
                'amount' => $payment['amount'],
                'payment_type' => $payment['payment_type'],
                'entity_id' => $payment['related_entity_id'],
                'due_date' => $payment['due_date'],
                'generated_date' => $payment['created_at'],
                'barcode' => $this->generateBarcode($payment['gateway_transaction_id']),
                'qr_code' => $this->generateQRCode($payment['gateway_transaction_id'])
            ];
        }
        
        return null;
    }
}

// Handle staff confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_cash_payment') {
    session_start();
    $database = new Database();
    $cashHandler = new CashPaymentHandler($database);
    
    $reference = $_POST['reference'] ?? '';
    $staffId = $_SESSION['user_id'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    
    $result = $cashHandler->confirmCashPayment($reference, $staffId, $notes);
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
?>
