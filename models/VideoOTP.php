<?php
// Video OTP Model

class VideoOTP {
    
    public static function createSession($userId, $videoId, $durationMinutes) {
        $db = DB::getConnection();
        
        // Remove existing active sessions for this user for ALL videos (enforces single active video)
        $db->prepare("DELETE FROM video_otp_sessions WHERE user_id = ?")->execute([$userId]);
        
        $expiresAt = date('Y-m-d H:i:s', strtotime("+$durationMinutes minutes"));
        
        $sql = "INSERT INTO video_otp_sessions (user_id, video_id, expires_at) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$userId, $videoId, $expiresAt]);
    }
    
    public static function hasValidSession($userId, $videoId) {
        $row = DB::fetch("SELECT id FROM video_otp_sessions WHERE user_id = ? AND video_id = ? AND expires_at >= NOW()", [$userId, $videoId]);
        return !empty($row);
    }
}
