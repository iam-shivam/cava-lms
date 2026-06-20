<?php
// Payment Controller

use Razorpay\Api\Api;

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/models/Payment.php';
require_once dirname(__DIR__) . '/models/Course.php';
require_once dirname(__DIR__) . '/models/Webinar.php';

class PaymentController {
    
    public static function initiatePayment($userId, $itemType, $itemId) {
        $price = 0.00;
        $title = '';
        
        if ($itemType === 'course') {
            $course = Course::getById($itemId);
            if (!$course) {
                throw new Exception("Course not found.");
            }
            $price = $course['price'];
            $title = $course['title'];
        } elseif ($itemType === 'webinar') {
            $webinar = Webinar::getById($itemId);
            if (!$webinar) {
                throw new Exception("Webinar not found.");
            }
            $price = $webinar['price'];
            $title = $webinar['title'];
        } else {
            throw new Exception("Invalid purchase item type.");
        }
        
        // Setup Razorpay API
        $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
        
        // Amount in paise
        $amountInPaise = round($price * 100);
        
        // Create Razorpay Order
        $orderData = [
            'receipt'         => 'rcpt_' . $userId . '_' . time(),
            'amount'          => $amountInPaise,
            'currency'        => 'INR',
            'payment_capture' => 1
        ];
        
        try {
            $razorpayOrder = $api->order->create($orderData);
            $orderId = $razorpayOrder['id'];
            
            // Log payment in database
            Payment::createPaymentLog($userId, $itemType, $itemId, $orderId, $price);
            
            return [
                'order_id' => $orderId,
                'amount' => $amountInPaise,
                'title' => $title,
                'price' => $price
            ];
        } catch (Exception $e) {
            throw new Exception("Razorpay Order Creation Failed: " . $e->getMessage());
        }
    }
    
    public static function verifyPayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature) {
        // Fetch payment log
        $payment = Payment::getByOrderId($razorpayOrderId);
        if (!$payment) {
            return false;
        }
        
        // Verify Razorpay Signature
        $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
        
        $success = false;
        try {
            $attributes = [
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature
            ];
            $api->utility->verifyPaymentSignature($attributes);
            $success = true;
        } catch (Exception $e) {
            $success = false;
        }
        
        if ($success) {
            // Update payment status to Success
            Payment::updatePaymentStatus($razorpayOrderId, $razorpayPaymentId, $razorpaySignature, 'Success');
            
            // Create Enrollment / Registration
            if ($payment['item_type'] === 'course') {
                Payment::createEnrollment($payment['user_id'], $payment['item_id'], $payment['id']);
                self::logTransactionEmail($payment['user_id'], 'course', $payment['item_id']);
            } elseif ($payment['item_type'] === 'webinar') {
                Payment::createWebinarRegistration($payment['user_id'], $payment['item_id'], $payment['id']);
                self::logTransactionEmail($payment['user_id'], 'webinar', $payment['item_id']);
            }
            return true;
        } else {
            // Update payment status to Failed
            Payment::updatePaymentStatus($razorpayOrderId, $razorpayPaymentId, $razorpaySignature, 'Failed');
            return false;
        }
    }
    
    private static function logTransactionEmail($userId, $type, $itemId) {
        try {
            require_once dirname(__DIR__) . '/helpers/EmailHelper.php';
            $user = DB::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            if (!$user) return;
            
            $recipient = $user['email'];
            $name = $user['full_name'];
            
            if ($type === 'course') {
                $course = Course::getById($itemId);
                $subject = "Course Purchased: " . $course['title'];
                $body = "<h3>Hi " . htmlspecialchars($name) . ",</h3><p>Thank you for purchasing <strong>" . htmlspecialchars($course['title']) . "</strong>. Go to your dashboard to start learning immediately!</p>";
            } else {
                $webinar = Webinar::getById($itemId);
                $subject = "Webinar Registration: " . $webinar['title'];
                $body = "<h3>Hi " . htmlspecialchars($name) . ",</h3><p>You have successfully registered for the webinar: <strong>" . htmlspecialchars($webinar['title']) . "</strong>.</p><p><strong>Date:</strong> " . date('d M, Y', strtotime($webinar['date'])) . "<br><strong>Time:</strong> " . date('h:i A', strtotime($webinar['time'])) . "</p><p>See you there!</p>";
            }
            
            EmailHelper::sendEmail($recipient, $name, $subject, $body);
        } catch (Exception $e) {
            // Fail silently
        }
    }
}
