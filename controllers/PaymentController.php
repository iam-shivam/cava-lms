<?php
// Payment Controller

use Razorpay\Api\Api;

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/models/Payment.php';
require_once dirname(__DIR__) . '/models/Course.php';
require_once dirname(__DIR__) . '/models/Webinar.php';

class PaymentController {
    
    public static function initiatePayment($userId, $itemType, $itemId, $amountToPay = null) {
        $price = 0.00;
        $title = '';
        $isPartial = false;
        
        if ($itemType === 'course') {
            $course = Course::getById($itemId);
            if (!$course) {
                throw new Exception("Course not found.");
            }
            $title = $course['title'];
            $totalPaid = Payment::getTotalPaid($userId, 'course', $itemId);
            $remainingBalance = $course['price'] - $totalPaid;
            
            if ($remainingBalance <= 0) {
                throw new Exception("Course is already fully paid.");
            }
            
            if ($amountToPay !== null && $amountToPay > 0 && $course['allow_partial_payment']) {
                if ($amountToPay < $course['min_installment'] && $amountToPay < $remainingBalance) {
                    throw new Exception("Minimum installment amount is ₹" . number_format($course['min_installment'], 2));
                }
                if ($amountToPay > $remainingBalance) {
                    $amountToPay = $remainingBalance;
                }
                $price = $amountToPay;
                $isPartial = ($price < $remainingBalance);
            } else {
                $price = $remainingBalance;
            }
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
            'receipt'         => 'rcpt_' . substr($userId, 0, 8) . '_' . time(),
            'amount'          => $amountInPaise,
            'currency'        => 'INR',
            'payment_capture' => 1
        ];
        
        $maxRetries = 2;
        $attempt = 0;
        $lastException = null;
        
        while ($attempt <= $maxRetries) {
            try {
                $razorpayOrder = $api->order->create($orderData);
                $orderId = $razorpayOrder['id'];
                
                $paymentType = $isPartial ? 'Partial' : 'Full';
                // Log payment in database
                Payment::createPaymentLog($userId, $itemType, $itemId, $orderId, $price, $paymentType);
                
                return [
                    'order_id' => $orderId,
                    'amount' => $amountInPaise,
                    'title' => $title,
                    'price' => $price
                ];
            } catch (Exception $e) {
                $lastException = $e;
                $msg = $e->getMessage();
                // Only retry on transient network errors (cURL DNS / connection errors)
                if (strpos($msg, 'cURL error 6') !== false || strpos($msg, 'cURL error 7') !== false || strpos($msg, 'cURL error 28') !== false) {
                    $attempt++;
                    if ($attempt <= $maxRetries) {
                        sleep(1); // Wait 1 second before retry
                        continue;
                    }
                    throw new Exception("Payment gateway is currently unreachable (network error). Please check your internet connection and try again. (Detail: " . $msg . ")");
                }
                // Non-network errors: fail immediately
                throw new Exception("Razorpay Order Creation Failed: " . $msg);
            }
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
            $paymentMethod = null;
            $paymentCurrency = 'INR';
            try {
                $rzpPayment = $api->payment->fetch($razorpayPaymentId);
                $paymentMethod = $rzpPayment->method; // e.g., 'upi', 'card', 'netbanking'
                $paymentCurrency = $rzpPayment->currency ?? 'INR';
            } catch (Exception $e) {
                // Fail silently if fetch fails
            }
            
            // Update payment status to Success
            Payment::updatePaymentStatus($razorpayOrderId, $razorpayPaymentId, $razorpaySignature, 'Success', $paymentMethod, $paymentCurrency);
            
            // Create Enrollment / Registration
            if ($payment['item_type'] === 'course') {
                $course = Course::getById($payment['item_id']);
                $totalPaid = Payment::getTotalPaid($payment['user_id'], 'course', $payment['item_id']);
                
                $status = 'Pending';
                $expiryDate = null;
                
                if ($totalPaid >= $course['price']) {
                    $status = 'Active';
                    if ($course['course_duration'] > 0) {
                        $expiryDate = date('Y-m-d H:i:s', strtotime('+' . $course['course_duration'] . ' months'));
                    }
                }
                
                Payment::createEnrollment($payment['user_id'], $payment['item_id'], $payment['id'], $status, $expiryDate);
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
                EmailHelper::sendTemplateEmail($recipient, $name, 'course_purchase', [
                    'user_name' => $name,
                    'course_title' => $course['title']
                ]);
            } else {
                $webinar = Webinar::getById($itemId);
                EmailHelper::sendTemplateEmail($recipient, $name, 'webinar_registration', [
                    'user_name' => $name,
                    'webinar_title' => $webinar['title'],
                    'webinar_date' => date('d M, Y', strtotime($webinar['date'])),
                    'webinar_time' => date('h:i A', strtotime($webinar['time']))
                ]);
            }
        } catch (Exception $e) {
            // Fail silently
        }
    }

}
