<?php
// Event Model

class Event {
    
    public static function getAll($limit = null) {
        $sql = "SELECT * FROM events ORDER BY created_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        return DB::fetchAll($sql);
    }
    
    public static function getById($id) {
        return DB::fetch("SELECT * FROM events WHERE id = ?", [$id]);
    }
}
