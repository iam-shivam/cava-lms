<?php
// User Model

class User {
    public static function create($fullName, $email, $mobileNumber, $password) {
        $db = DB::getConnection();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (full_name, email, mobile_number, password_hash, status) VALUES (?, ?, ?, ?, 'Active')";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$fullName, $email, $mobileNumber, $passwordHash]);
    }
    
    public static function findByEmail($email) {
        return DB::fetch("SELECT * FROM users WHERE email = ?", [$email]);
    }
    
    public static function findById($id) {
        return DB::fetch("SELECT * FROM users WHERE id = ?", [$id]);
    }
    
    public static function findByIdentifier($identifier) {
        return DB::fetch("SELECT * FROM users WHERE email = ? OR mobile_number = ?", [$identifier, $identifier]);
    }
    
    public static function emailExists($email) {
        $row = DB::fetch("SELECT id FROM users WHERE email = ?", [$email]);
        return !empty($row);
    }
    
    public static function mobileExists($mobileNumber) {
        $row = DB::fetch("SELECT id FROM users WHERE mobile_number = ?", [$mobileNumber]);
        return !empty($row);
    }
    
    public static function updateProfile($id, $fullName, $mobileNumber) {
        $db = DB::getConnection();
        $sql = "UPDATE users SET full_name = ?, mobile_number = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$fullName, $mobileNumber, $id]);
    }
    
    public static function updatePassword($id, $newPassword) {
        $db = DB::getConnection();
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$passwordHash, $id]);
    }
}
