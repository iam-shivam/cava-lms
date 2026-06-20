<?php
// Event Model

class Event {
    
    public static function getAll() {
        return DB::fetchAll("SELECT * FROM events ORDER BY date ASC");
    }
    
    public static function getById($id) {
        return DB::fetch("SELECT * FROM events WHERE id = ?", [$id]);
    }
}
