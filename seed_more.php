<?php
// Seed More Mock Data for CAVA LMS

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

try {
    $pdo = DB::getConnection();
    echo "Connected to database successfully.<br>";
    
    // 1. Seed Categories
    $categories = [
        ['name' => 'Express Entry', 'slug' => 'express-entry'],
        ['name' => 'IELTS Preparation', 'slug' => 'ielts-preparation'],
        ['name' => 'Provincial Nominee Programs (PNP)', 'slug' => 'pnp-programs']
    ];
    
    $insertCat = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = ?");
    $catIds = [];
    foreach ($categories as $cat) {
        $insertCat->execute([$cat['name'], $cat['slug'], $cat['name']]);
        // Get the ID
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->execute([$cat['slug']]);
        $catIds[$cat['slug']] = $stmt->fetch()['id'];
        echo "Category '{$cat['name']}' seeded.<br>";
    }
    
    // 2. Seed Courses
    $courses = [
        [
            'cat_id' => $catIds['ielts-preparation'],
            'title' => 'IELTS Speaking & Writing Band 8 Masterclass',
            'slug' => 'ielts-speaking-writing-band-8',
            'thumbnail' => 'ielts_thumbnail.jpg',
            'desc' => "Crack the IELTS exam with our expert-led modules. Learn band 8 templates, vocabularies, and speaking card tricks.",
            'price' => 499.00
        ],
        [
            'cat_id' => $catIds['express-entry'],
            'title' => 'Step-by-Step Express Entry System Profile Creation',
            'slug' => 'express-entry-profile-creation',
            'thumbnail' => 'express_entry_thumbnail.jpg',
            'desc' => "A click-by-click walkthrough of building your Express Entry profile, choosing NOC codes, and uploading reference letters.",
            'price' => 799.00
        ],
        [
            'cat_id' => $catIds['pnp-programs'],
            'title' => 'How to Apply for Ontario PNP (OINP) Successfully',
            'slug' => 'ontario-pnp-oinp-mastery',
            'thumbnail' => 'ontario_pnp_thumbnail.jpg',
            'desc' => "Unlock Canada PR pathways via Ontario PNP draws. Details of Human Capital Priorities stream and Job Offer streams.",
            'price' => 1299.00
        ]
    ];
    
    $insertCourse = $pdo->prepare("INSERT INTO courses (category_id, title, slug, thumbnail, description, price, status) VALUES (?, ?, ?, ?, ?, ?, 'Published') ON DUPLICATE KEY UPDATE description = ?, price = ?");
    
    $courseIds = [];
    foreach ($courses as $c) {
        $insertCourse->execute([
            $c['cat_id'], $c['title'], $c['slug'], $c['thumbnail'], $c['desc'], $c['price'],
            $c['desc'], $c['price']
        ]);
        
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE slug = ?");
        $stmt->execute([$c['slug']]);
        $courseIds[$c['slug']] = $stmt->fetch()['id'];
        echo "Course '{$c['title']}' seeded.<br>";
    }
    
    // 3. Seed Sections & Videos
    $insertSec = $pdo->prepare("INSERT INTO course_sections (course_id, title, sort_order) VALUES (?, ?, ?)");
    $insertVid = $pdo->prepare("INSERT INTO course_videos (section_id, course_id, title, video_url, video_source, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    
    // IELTS Sections
    $ieltsId = $courseIds['ielts-speaking-writing-band-8'];
    // Check if sections already exist to prevent duplicate seedings
    $stmt = $pdo->prepare("SELECT id FROM course_sections WHERE course_id = ?");
    $stmt->execute([$ieltsId]);
    if (!$stmt->fetch()) {
        $insertSec->execute([$ieltsId, 'Section 1: Speaking Cue Cards Tips', 1]);
        $sec1 = $pdo->lastInsertId();
        $insertVid->execute([$sec1, $ieltsId, 'Structuring your 2-minute Speaking Speech', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 1]);
        $insertVid->execute([$sec1, $ieltsId, 'Common Idioms for Band 8+ Vocabulary', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 2]);
        
        $insertSec->execute([$ieltsId, 'Section 2: Writing Task 2 Essay Templates', 2]);
        $sec2 = $pdo->lastInsertId();
        $insertVid->execute([$sec2, $ieltsId, 'How to Structure Agree/Disagree Essays', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 1]);
        echo "IELTS course sections and videos seeded.<br>";
    }
    
    // Express Entry Sections
    $eeId = $courseIds['express-entry-profile-creation'];
    $stmt->execute([$eeId]);
    if (!$stmt->fetch()) {
        $insertSec->execute([$eeId, 'Section 1: Documents Checklist', 1]);
        $sec1 = $pdo->lastInsertId();
        $insertVid->execute([$sec1, $eeId, 'How to choose the correct NOC TEER Code', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 1]);
        $insertVid->execute([$sec1, $eeId, 'WES Evaluation Step-by-Step guide', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 2]);
        echo "Express Entry course sections seeded.<br>";
    }
    
    // 4. Seed Webinars
    $webinars = [
        [
            'title' => 'Express Entry Draws Analysis 2026',
            'desc' => 'Live analysis of recent CRS scores, category-based draws (healthcare, STEM, trades), and predictions for the rest of 2026.',
            'date' => date('Y-m-d', strtotime('+5 days')),
            'time' => '17:00:00',
            'price' => 149.00
        ],
        [
            'title' => 'IELTS Reading Hacks & Mock Test Session',
            'desc' => 'Struggling with True/False/Not Given or Heading Matchings? Attend this live mock walkthrough to master reading speed tips.',
            'date' => date('Y-m-d', strtotime('+10 days')),
            'time' => '11:00:00',
            'price' => 99.00
        ]
    ];
    
    $insertWebinar = $pdo->prepare("INSERT INTO webinars (title, description, date, time, price, status) VALUES (?, ?, ?, ?, ?, 'Active')");
    foreach ($webinars as $w) {
        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM webinars WHERE title = ?");
        $stmt->execute([$w['title']]);
        if (!$stmt->fetch()) {
            $insertWebinar->execute([$w['title'], $w['desc'], $w['date'], $w['time'], $w['price']]);
            echo "Webinar '{$w['title']}' seeded.<br>";
        }
    }
    
    // 5. Seed Events
    $events = [
        [
            'title' => 'IELTS Live Mock Speaking Assessment',
            'desc' => 'Get evaluated live by a former IELTS examiner. Receive an instant band score feedback and detailed correction pointers.',
            'date' => date('Y-m-d', strtotime('+12 days'))
        ],
        [
            'title' => 'Ontario PNP Student Intake Fair',
            'desc' => 'Virtual session for international graduates in Ontario to understand direct employer job offer streams and secure nominations.',
            'date' => date('Y-m-d', strtotime('+15 days'))
        ]
    ];
    
    $insertEvent = $pdo->prepare("INSERT INTO events (title, description, date) VALUES (?, ?, ?)");
    foreach ($events as $ev) {
        $stmt = $pdo->prepare("SELECT id FROM events WHERE title = ?");
        $stmt->execute([$ev['title']]);
        if (!$stmt->fetch()) {
            $insertEvent->execute([$ev['title'], $ev['desc'], $ev['date']]);
            echo "Event '{$ev['title']}' seeded.<br>";
        }
    }
    
    // 6. Seed Mock Users
    $users = [
        ['name' => 'Aman Mehta', 'email' => 'aman@cava.com', 'mobile' => '9876543211', 'pass' => 'UserAman123!'],
        ['name' => 'Simran Patel', 'email' => 'simran@cava.com', 'mobile' => '9876543212', 'pass' => 'UserSimran123!'],
        ['name' => 'Rajesh Kumar', 'email' => 'rajesh@cava.com', 'mobile' => '9876543213', 'pass' => 'UserRajesh123!']
    ];
    
    $insertUser = $pdo->prepare("INSERT INTO users (full_name, email, mobile_number, password_hash, status) VALUES (?, ?, ?, ?, 'Active')");
    foreach ($users as $u) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$u['email']]);
        if (!$stmt->fetch()) {
            $hash = password_hash($u['pass'], PASSWORD_DEFAULT);
            $insertUser->execute([$u['name'], $u['email'], $u['mobile'], $hash]);
            echo "Mock User '{$u['name']}' seeded (Email: {$u['email']}, Password: {$u['pass']}).<br>";
        }
    }
    
    echo "<h3>All Mock Data Seeded Successfully!</h3>";
    
} catch (Exception $e) {
    echo "<h3>Seeding Failed:</h3> " . $e->getMessage();
}
