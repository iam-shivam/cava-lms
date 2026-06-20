<?php
// Database Initialization Script for CAVA LMS

require_once __DIR__ . '/config/config.php';

try {
    // 1. Connect to MySQL Server (Without DB first, to create it if not exists)
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Create DB
    $dbName = DB_NAME;
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database `$dbName` created or already exists.<br>";
    
    // Switch to database
    $pdo->exec("USE `$dbName`");
    echo "Switched to database `$dbName`.<br>";
    
    // 2. Read and run schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("schema.sql not found at $schemaFile");
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Split SQL queries by semicolon (basic splitting)
    // Note: This matches standard schema files without complex procedures
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }
    echo "Database tables created successfully.<br>";
    
    // 3. Seed Default Admin Account
    $adminEmail = 'admin@cava.com';
    $adminUsername = 'admin';
    $adminPassword = 'AdminPassword123!';
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$adminEmail]);
    if (!$stmt->fetch()) {
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $insertAdmin = $pdo->prepare("INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)");
        $insertAdmin->execute([$adminUsername, $adminEmail, $passwordHash]);
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
    
    $checkSetting = $pdo->prepare("SELECT setting_key FROM settings WHERE setting_key = ?");
    $insertSetting = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    foreach ($settings as $key => $val) {
        $insertSetting->execute([$key, $val, $val]);
    }
    echo "Default settings seeded successfully.<br>";
    
    // 5. Seed Demo Category & Course & Sections & Videos
    $checkCat = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
    $insertCat = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    
    $catSlug = 'canada-immigration';
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$catSlug]);
    $catRow = $stmt->fetch();
    
    if (!$catRow) {
        $insertCat->execute(['Canada Immigration', $catSlug]);
        $catId = $pdo->lastInsertId();
        echo "Demo category 'Canada Immigration' created.<br>";
    } else {
        $catId = $catRow['id'];
    }
    
    // Demo Course
    $courseSlug = 'immigration-process-masterclass';
    $stmt = $pdo->prepare("SELECT id FROM courses WHERE slug = ?");
    $stmt->execute([$courseSlug]);
    $courseRow = $stmt->fetch();
    
    if (!$courseRow) {
        $insertCourse = $pdo->prepare("INSERT INTO courses (category_id, title, slug, thumbnail, description, price, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $desc = "Learn about the Express Entry system, Provincial Nominee Programs (PNP), and required documentation to immigrate to Canada.";
        $insertCourse->execute([
            $catId,
            'Canada Immigration Process Masterclass',
            $courseSlug,
            'canada_immigration_thumbnail.jpg',
            $desc,
            999.00,
            'Published'
        ]);
        $courseId = $pdo->lastInsertId();
        echo "Demo course 'Canada Immigration Process Masterclass' created.<br>";
        
        // Demo Section 1
        $insertSection = $pdo->prepare("INSERT INTO course_sections (course_id, title, sort_order) VALUES (?, ?, ?)");
        $insertSection->execute([$courseId, 'Section 1: The Basics of Canada Immigration', 1]);
        $sec1Id = $pdo->lastInsertId();
        
        $insertVideo = $pdo->prepare("INSERT INTO course_videos (section_id, course_id, title, thumbnail, video_url, video_source, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertVideo->execute([
            $sec1Id,
            $courseId,
            'What is ECA (Educational Credential Assessment)',
            'video1.jpg',
            'https://www.youtube.com/embed/dQw4w9WgXcQ', // Dummy Youtube Embed
            'youtube',
            1
        ]);
        $insertVideo->execute([
            $sec1Id,
            $courseId,
            'What is EOI (Expression of Interest)',
            'video2.jpg',
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'youtube',
            2
        ]);
        
        // Demo Section 2
        $insertSection->execute([$courseId, 'Section 2: Scoring Points Systems', 2]);
        $sec2Id = $pdo->lastInsertId();
        
        $insertVideo->execute([
            $sec2Id,
            $courseId,
            'FSW (Federal Skilled Worker) Point System',
            'video3.jpg',
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'youtube',
            1
        ]);
        $insertVideo->execute([
            $sec2Id,
            $courseId,
            'CRS (Comprehensive Ranking System) Point System',
            'video4.jpg',
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'youtube',
            2
        ]);
        
        echo "Demo sections and videos seeded successfully.<br>";
    } else {
        echo "Demo course already exists.<br>";
    }
    
    // 6. Seed Demo Webinar
    $stmt = $pdo->query("SELECT id FROM webinars WHERE title = 'Immigration Q&A Webinar'");
    if (!$stmt->fetch()) {
        $insertWebinar = $pdo->prepare("INSERT INTO webinars (title, description, date, time, price, status) VALUES (?, ?, ?, ?, ?, ?)");
        $webDesc = "Join our Regulated Canadian Immigration Consultant (RCIC) for a live query session answering all your CRS score, PNP draws, and documentation doubts.";
        $insertWebinar->execute([
            'Immigration Q&A Webinar',
            $webDesc,
            date('Y-m-d', strtotime('+3 days')),
            '18:00:00',
            99.00,
            'Active'
        ]);
        echo "Demo Webinar seeded successfully.<br>";
    } else {
        echo "Demo Webinar already exists.<br>";
    }
    
    // 7. Seed Demo Events
    $stmt = $pdo->query("SELECT id FROM events WHERE title = 'Virtual Immigration Fair 2026'");
    if (!$stmt->fetch()) {
        $insertEvent = $pdo->prepare("INSERT INTO events (title, description, date, event_image) VALUES (?, ?, ?, ?)");
        $eventDesc = "Meet representatives from Canadian universities, employers, and immigration consulting firms online.";
        $insertEvent->execute([
            'Virtual Immigration Fair 2026',
            $eventDesc,
            date('Y-m-d', strtotime('+7 days')),
            'fair_event.jpg'
        ]);
        echo "Demo Event seeded successfully.<br>";
    } else {
        echo "Demo Event already exists.<br>";
    }

    echo "<h3>Database Initialization Completed Successfully!</h3>";

} catch (Exception $e) {
    echo "<h3>Initialization Failed:</h3> " . $e->getMessage();
}
