<?php
// Webinar Model

class Webinar {
    
    public static function getAll() {
        return DB::fetchAll("SELECT * FROM webinars WHERE status = 'Active' ORDER BY date ASC, time ASC");
    }
    
    public static function getById($id) {
        return DB::fetch("SELECT * FROM webinars WHERE id = ?", [$id]);
    }
    
    public static function isUserRegistered($userId, $webinarId) {
        if (!$userId) return false;
        $row = DB::fetch("SELECT id FROM webinar_registrations WHERE user_id = ? AND webinar_id = ?", [$userId, $webinarId]);
        return !empty($row);
    }
    
    public static function getRegisteredWebinars($userId) {
        return DB::fetchAll("
            SELECT w.*, r.registered_at 
            FROM webinar_registrations r 
            JOIN webinars w ON r.webinar_id = w.id 
            WHERE r.user_id = ?
            ORDER BY w.date ASC
        ", [$userId]);
    }
    
    public static function getRegistrationsByWebinar($webinarId) {
        return DB::fetchAll("
            SELECT r.*, u.full_name as user_name, u.email as user_email, u.mobile_number 
            FROM webinar_registrations r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.webinar_id = ?
            ORDER BY r.registered_at DESC
        ", [$webinarId]);
    }
}
