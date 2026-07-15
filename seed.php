<?php
// seed.php - Basic data seeder for CAVA LMS
// Run via: php seed.php (after DB is created and config is set)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

function insert($sql, $params = []) {
    $stmt = DB::query($sql, $params);
    return $stmt->rowCount();
}

try {
    // 1. Admins (3 records)
    $adminPwd = password_hash('AdminPassword123!', PASSWORD_DEFAULT);
    insert("INSERT INTO admins (id, username, email, password_hash) VALUES (?, ?, ?, ?)", [generate_uuid(), 'admin1', 'admin1@cava.com', $adminPwd]);
    insert("INSERT INTO admins (id, username, email, password_hash) VALUES (?, ?, ?, ?)", [generate_uuid(), 'admin2', 'admin2@cava.com', $adminPwd]);
    insert("INSERT INTO admins (id, username, email, password_hash) VALUES (?, ?, ?, ?)", [generate_uuid(), 'admin3', 'admin3@cava.com', $adminPwd]);

    // 2. Users (3 records)
    $userPwd = password_hash('UserPass123!', PASSWORD_DEFAULT);
    insert("INSERT INTO users (id, username, email, password_hash) VALUES (?, ?, ?, ?)", [generate_uuid(), 'user1', 'user1@example.com', $userPwd]);
    insert("INSERT INTO users (id, username, email, password_hash) VALUES (?, ?, ?, ?)", [generate_uuid(), 'user2', 'user2@example.com', $userPwd]);
    insert("INSERT INTO users (id, username, email, password_hash) VALUES (?, ?, ?, ?)", [generate_uuid(), 'user3', 'user3@example.com', $userPwd]);

    // 3. Categories (3 records)
    insert("INSERT INTO categories (id, name, slug) VALUES (?, ?, ?)", [generate_uuid(), 'Business', 'business']);
    insert("INSERT INTO categories (id, name, slug) VALUES (?, ?, ?)", [generate_uuid(), 'Ethics', 'ethics']);
    insert("INSERT INTO categories (id, name, slug) VALUES (?, ?, ?)", [generate_uuid(), 'Immigration', 'immigration']);

    // 4. Courses (1 main course with legitimate data)
    $courseId = generate_uuid();
    $categoryId = DB::fetch('SELECT id FROM categories WHERE slug = ?', ['business'])['id'];
    insert("INSERT INTO courses (id, title, slug, category_id, description, thumbnail, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())", [
        $courseId,
        'Business Fundamentals',
        'business-fundamentals',
        $categoryId,
        'Learn core business concepts and models.',
        'uploads/Business model D1V2.png'
    ]);

    // 5. Sections (3 sections for the course)
    $sectionIds = [];
    for ($i = 1; $i <= 3; $i++) {
        $secId = generate_uuid();
        $sectionIds[] = $secId;
        insert("INSERT INTO sections (id, course_id, title, position) VALUES (?, ?, ?, ?)", [$secId, $courseId, "Section $i", $i]);
    }

    // 6. Videos (3 videos linked to sections)
    $videos = [
        [
            'title' => 'Business Model',
            'filename' => 'uploads/videos/Business model D1V2.mp4',
            'thumbnail' => 'uploads/Business model D1V2.png',
            'provider' => 'local',
            'section_index' => 0
        ],
        [
            'title' => 'Code of Honour',
            'filename' => 'uploads/videos/CODE OF HONOUR - D1V5.mp4',
            'thumbnail' => 'uploads/CODE OF HONOUR - D1V5.png',
            'provider' => 'local',
            'section_index' => 1
        ],
        [
            'title' => 'Types of Visas',
            'filename' => 'uploads/videos/Types of Visas D2V2.mp4',
            'thumbnail' => 'uploads/Types of Visas D2V2.png',
            'provider' => 'local',
            'section_index' => 2
        ]
    ];

    foreach ($videos as $idx => $v) {
        $vidId = generate_uuid();
        $sectionId = $sectionIds[$v['section_index']];
        insert("INSERT INTO videos (id, section_id, title, video_url, thumbnail, video_provider, position, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())", [
            $vidId,
            $sectionId,
            $v['title'],
            $v['filename'],
            $v['thumbnail'],
            $v['provider'],
            $idx + 1
        ]);
    }

    // 7. Settings (3 generic settings)
    $settings = [
        'site_title' => 'CAVA LMS Portal',
        'contact_email' => 'contact@cavalms.com',
        'contact_phone' => '+91 98765 43210'
    ];
    foreach ($settings as $k => $val) {
        insert("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?", [$k, $val, $val]);
    }

    echo "Seeder executed successfully.\n";
} catch (Exception $e) {
    echo "Seeder error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
