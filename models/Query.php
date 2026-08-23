<?php
// Query Model

class Query {
    
    /**
     * Generate a unique 6-digit alphanumeric ticket number (uppercase).
     */
    private static function generateTicketNumber() {
        $db = DB::getConnection();
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Excludes I, O, 0, 1 to avoid confusion
        $maxAttempts = 20;
        
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $ticket = '';
            for ($i = 0; $i < 6; $i++) {
                $ticket .= $chars[random_int(0, strlen($chars) - 1)];
            }
            // Check uniqueness
            $existing = DB::fetch("SELECT id FROM queries WHERE ticket_number = ?", [$ticket]);
            if (!$existing) {
                return $ticket;
            }
        }
        // Fallback: use timestamp-based
        return strtoupper(substr(md5(microtime(true)), 0, 6));
    }
    
    public static function create($userId, $name, $email, $mobileNumber, $message) {
        $db = DB::getConnection();
        $uuid = generate_uuid();
        $ticketNumber = self::generateTicketNumber();
        $sql = "INSERT INTO queries (id, ticket_number, user_id, name, email, mobile_number, query_message, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$uuid, $ticketNumber, $userId ?: null, $name, $email, $mobileNumber, $message]);
        return $ticketNumber;
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
