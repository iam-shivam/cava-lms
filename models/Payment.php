<?php
// Payment Model

class Payment {
    
    public static function createPaymentLog($userId, $itemType, $itemId, $orderId, $amount, $paymentType = 'Full') {
        $db = DB::getConnection();
        $sql = "INSERT INTO payments (user_id, item_type, item_id, razorpay_order_id, amount, payment_type, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $itemType, $itemId, $orderId, $amount, $paymentType]);
        return DB::lastInsertId();
    }
    
    public static function updatePaymentStatus($orderId, $paymentId, $signature, $status) {
        $db = DB::getConnection();
        $sql = "UPDATE payments SET razorpay_payment_id = ?, razorpay_signature = ?, status = ? WHERE razorpay_order_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$paymentId, $signature, $status, $orderId]);
    }
    
    public static function getByOrderId($orderId) {
        return DB::fetch("SELECT * FROM payments WHERE razorpay_order_id = ?", [$orderId]);
    }
    
    public static function createEnrollment($userId, $courseId, $paymentLogId, $status = 'Pending', $expiryDate = null) {
        $db = DB::getConnection();
        $sql = "INSERT INTO enrollments (user_id, course_id, payment_id, status, expiry_date) VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE payment_id = VALUES(payment_id), status = VALUES(status), expiry_date = VALUES(expiry_date)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$userId, $courseId, $paymentLogId, $status, $expiryDate]);
    }
    
    public static function getTotalPaid($userId, $itemType, $itemId) {
        $row = DB::fetch("SELECT SUM(amount) as total FROM payments WHERE user_id = ? AND item_type = ? AND item_id = ? AND status = 'Success'", [$userId, $itemType, $itemId]);
        return $row && $row['total'] ? (float)$row['total'] : 0.00;
    }
    
    public static function createWebinarRegistration($userId, $webinarId, $paymentLogId) {
        $db = DB::getConnection();
        $sql = "INSERT IGNORE INTO webinar_registrations (webinar_id, user_id, payment_id) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$webinarId, $userId, $paymentLogId]);
    }
    
    public static function getPaymentsByUser($userId) {
        return DB::fetchAll("
            SELECT p.*, 
                   CASE 
                       WHEN p.item_type = 'course' THEN (SELECT title FROM courses WHERE id = p.item_id)
                       WHEN p.item_type = 'webinar' THEN (SELECT title FROM webinars WHERE id = p.item_id)
                   END as item_title
            FROM payments p
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
        ", [$userId]);
    }
    
    public static function getAllPayments() {
        return DB::fetchAll("
            SELECT p.*, u.full_name as user_name, u.email as user_email,
                   CASE 
                       WHEN p.item_type = 'course' THEN (SELECT title FROM courses WHERE id = p.item_id)
                       WHEN p.item_type = 'webinar' THEN (SELECT title FROM webinars WHERE id = p.item_id)
                   END as item_title
            FROM payments p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC
        ");
    }
}
