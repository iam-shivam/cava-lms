    <?php
    // seed.php - Updated seeder matching current schema and migrations
    // Run via: php seed.php (after DB is created and config is set)

    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/db.php';

    function insert($sql, $params = []) {
        $stmt = DB::query($sql, $params);
        return $stmt->rowCount();
    }

    try {
        // ---------- Admins (idempotent) ----------
        $adminPwd = password_hash('AdminPassword123!', PASSWORD_DEFAULT);
        $adminInsert = "INSERT INTO admins (id, username, email, password_hash) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE username=VALUES(username), email=VALUES(email)";
        insert($adminInsert, [generate_uuid(), 'admin1', 'admin1@cava.com', $adminPwd]);
        insert($adminInsert, [generate_uuid(), 'admin2', 'admin2@cava.com', $adminPwd]);
        insert($adminInsert, [generate_uuid(), 'admin3', 'admin3@cava.com', $adminPwd]);

        // ---------- Users (idempotent) ----------
        $userPwd = password_hash('UserPass123!', PASSWORD_DEFAULT);
        $userInsert = "INSERT INTO users (id, full_name, email, mobile_number, password_hash) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), email=VALUES(email), mobile_number=VALUES(mobile_number)";
        insert($userInsert, [generate_uuid(), 'User One', 'user1@example.com', '1234567890', $userPwd]);
        insert($userInsert, [generate_uuid(), 'User Two', 'user2@example.com', '1234567891', $userPwd]);
        insert($userInsert, [generate_uuid(), 'User Three', 'user3@example.com', '1234567892', $userPwd]);

        // ---------- Categories ----------
        $catInsert = "INSERT INTO categories (id, name, slug) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name)";
        insert($catInsert, [generate_uuid(), 'Business', 'business']);
        insert($catInsert, [generate_uuid(), 'Ethics', 'ethics']);
        insert($catInsert, [generate_uuid(), 'Immigration', 'immigration']);

        // ---------- Course ----------
        $courseSlug = 'business-fundamentals';
        // Ensure we have the business category ID
        $categoryId = DB::fetch('SELECT id FROM categories WHERE slug = ?', ['business'])['id'];
        // Try inserting with a new UUID; if the course already exists (slug unique), it will be updated.
        $tempCourseId = generate_uuid();
        $courseInsert = "INSERT INTO courses (id, category_id, title, slug, description, thumbnail, price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), price=VALUES(price), status=VALUES(status)";
        insert($courseInsert, [
            $tempCourseId,
            $categoryId,
            'Business Fundamentals',
            $courseSlug,
            'Learn core business concepts and models.',
            'uploads/Business model D1V2.png',
            0.00,
            'Draft'
        ]);
        // Retrieve the actual ID (whether newly inserted or existing)
        $courseId = DB::fetch('SELECT id FROM courses WHERE slug = ?', [$courseSlug])['id'];

        // ---------- Sections (course_sections) ----------
        $sectionIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $sectionTitle = "Section $i";
            // Check if section already exists for this course
            $existingSec = DB::fetch("SELECT id FROM course_sections WHERE course_id = ? AND title = ?", [$courseId, $sectionTitle]);
            if ($existingSec) {
                $secId = $existingSec['id'];
            } else {
                $secId = generate_uuid();
                $sectionInsert = "INSERT INTO course_sections (id, course_id, title, sort_order) VALUES (?, ?, ?, ?)";
                insert($sectionInsert, [$secId, $courseId, $sectionTitle, $i]);
            }
            $sectionIds[] = $secId;
        }

        // ---------- Videos (course_videos) ----------
        $videos = [
            [
                'title' => 'Business Model',
                'filename' => 'uploads/videos/Business model D1V2.mp4',
                'thumbnail' => 'uploads/Business model D1V2.png',
                'section_index' => 0
            ],
            [
                'title' => 'Code of Honour',
                'filename' => 'uploads/videos/CODE OF HONOUR - D1V5.mp4',
                'thumbnail' => 'uploads/CODE OF HONOUR - D1V5.png',
                'section_index' => 1
            ],
            [
                'title' => 'Types of Visas',
                'filename' => 'uploads/videos/Types of Visas D2V2.mp4',
                'thumbnail' => 'uploads/Types of Visas D2V2.png',
                'section_index' => 2
            ]
        ];

        foreach ($videos as $idx => $v) {
            $sectionId = $sectionIds[$v['section_index']];
            // Check if video already exists in this section/course
            $existingVid = DB::fetch("SELECT id FROM course_videos WHERE course_id = ? AND section_id = ? AND title = ?", [$courseId, $sectionId, $v['title']]);
            if ($existingVid) {
                $vidId = $existingVid['id'];
            } else {
                $vidId = generate_uuid();
                $videoInsert = "INSERT INTO course_videos (id, section_id, course_id, title, thumbnail, video_url, video_source, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                insert($videoInsert, [
                    $vidId,
                    $sectionId,
                    $courseId,
                    $v['title'],
                    $v['thumbnail'],
                    $v['filename'],
                    'local',
                    $idx + 1
                ]);
            }
            // ---- Optional: create a placeholder document entry for each video ----
            $existingDoc = DB::fetch("SELECT id FROM video_documents WHERE video_id = ? AND title = ?", [$vidId, $v['title'] . ' Resources']);
            if (!$existingDoc) {
                $docInsert = "INSERT INTO video_documents (id, video_id, title, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?)";
                $docId = generate_uuid();
                insert($docInsert, [
                    $docId,
                    $vidId,
                    $v['title'] . ' Resources',
                    'uploads/sample.pdf', // you can replace with actual path later
                    'pdf',
                    0
                ]);
            }
        }

        // ---------- Webinars ----------
        $webinarsToSeed = [
            ['Visa Workshop', 'Deep dive into visa processes.', '2026-08-15', '10:00:00', 199.99, 'Active'],
            ['Business Ethics Seminar', 'Understanding ethical practices.', '2026-09-01', '14:00:00', 149.99, 'Active']
        ];
        foreach ($webinarsToSeed as $w) {
            $existingWebinar = DB::fetch("SELECT id FROM webinars WHERE title = ?", [$w[0]]);
            if (!$existingWebinar) {
                $webinarInsert = "INSERT INTO webinars (id, title, description, date, time, price, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
                insert($webinarInsert, [generate_uuid(), $w[0], $w[1], $w[2], $w[3], $w[4], $w[5]]);
            }
        }

        // ---------- Events ----------
        $eventsToSeed = [
            ['Annual Conference', 'Company-wide annual conference.', '2026-10-05', null],
            ['Tech Expo', 'Showcase of latest tech.', '2026-11-20', null]
        ];
        foreach ($eventsToSeed as $ev) {
            $existingEvent = DB::fetch("SELECT id FROM events WHERE title = ?", [$ev[0]]);
            if (!$existingEvent) {
                $eventInsert = "INSERT INTO events (id, title, description, date, event_image) VALUES (?, ?, ?, ?, ?)";
                insert($eventInsert, [generate_uuid(), $ev[0], $ev[1], $ev[2], $ev[3]]);
            }
        }

        // ---------- Settings ----------
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
