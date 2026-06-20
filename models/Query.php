<?php
// Query Model

class Query {
    
    public static function create($userId, $name, $email, $mobileNumber, $message) {
        $db = DB::getConnection();
        $sql = "INSERT INTO queries (user_id, name, email, mobile_number, query_message, status) VALUES (?, ?, ?, ?, ?, 'Pending')";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$userId ?: null, $name, $email, $mobileNumber, $message]);
    }
    
    public static function getByUser($userId) {
        return DB::fetchAll("SELECT * FROM queries WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
    }
    
    public static function getAll() {
        return DB::fetchAll("SELECT * FROM queries ORDER BY created_at DESC");
    }
    
    public static function resolve($id) {
        $db = DB::getConnection();
        $sql = "UPDATE queries SET status = 'Resolved', resolved_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
