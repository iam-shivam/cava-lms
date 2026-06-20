<?php
// Admin Model

class Admin {
    public static function findByEmailOrUsername($emailOrUsername) {
        return DB::fetch(
            "SELECT * FROM admins WHERE email = ? OR username = ?", 
            [$emailOrUsername, $emailOrUsername]
        );
    }
    
    public static function findById($id) {
        return DB::fetch("SELECT * FROM admins WHERE id = ?", [$id]);
    }
}
