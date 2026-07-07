<?php
// Production Database Initialization Script for CAVA LMS

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

try {
    // 1. Connect to MySQL Server (Using the predefined DB_NAME from config)
    // We do NOT use DROP DATABASE here because shared hosting panels do not allow script-based DB creation.
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "Connected to database `" . DB_NAME . "` successfully.<br>";
    
    // 2. Read and run schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("schema.sql not found at $schemaFile");
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Split SQL queries by semicolon
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }
    echo "Database tables created/verified successfully.<br>";
    
    // 3. Seed Default Admin Account
    $adminEmail = 'admin@cava.com';
    $adminUsername = 'admin';
    $adminPassword = 'AdminPassword123!';
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$adminEmail]);
    if (!$stmt->fetch()) {
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $adminId = generate_uuid();
        
        $insertAdmin = $pdo->prepare("INSERT INTO admins (id, username, email, password_hash) VALUES (?, ?, ?, ?)");
        $insertAdmin->execute([$adminId, $adminUsername, $adminEmail, $passwordHash]);
        
        echo "Default admin account created:<br>";
        echo "- Email: <b>$adminEmail</b><br>";
        echo "- Password: <b>$adminPassword</b><br>";
    } else {
        echo "Admin account `$adminEmail` already exists.<br>";
    }
    
    // 4. Seed Default Dynamic Settings
    $settings = [
        'site_title' => 'CAVA LMS Portal',
        'contact_email' => 'contact@cavalms.com',
        'contact_phone' => '+91 98765 43210',
        'about_us' => 'CAVA LMS is a premium e-learning portal designed to provide quality, affordable education for career development and skill enhancements.',
        'hero_title' => 'Upgrade Your Skills with CAVA LMS',
        'hero_subtitle' => 'Access high-quality courses, webinars, and masterclasses designed by industry experts to boost your career.'
    ];
    
    $insertSetting = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    foreach ($settings as $key => $val) {
        $insertSetting->execute([$key, $val, $val]);
    }
    echo "Default settings seeded successfully.<br>";
    
    echo "<h3>Production Database Initialization Completed Successfully!</h3>";
    echo "<p style='color:red; font-weight:bold;'>SECURITY WARNING: Please delete this init_db.php file from your server after running it!</p>";

} catch (Exception $e) {
    echo "<h3>Initialization Failed:</h3> " . $e->getMessage();
}
