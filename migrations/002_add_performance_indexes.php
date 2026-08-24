<?php
// Add Database Performance Indexes safely
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

echo "Running Database Index Optimization...\n";

$indexes = [
    ['table' => 'courses', 'name' => 'idx_courses_status_created', 'sql' => 'CREATE INDEX idx_courses_status_created ON courses (status, created_at)'],
    ['table' => 'course_videos', 'name' => 'idx_videos_course_sec', 'sql' => 'CREATE INDEX idx_videos_course_sec ON course_videos (course_id, section_id)'],
    ['table' => 'enrollments', 'name' => 'idx_enrollments_user_status', 'sql' => 'CREATE INDEX idx_enrollments_user_status ON enrollments (user_id, status, expiry_date)'],
    ['table' => 'user_video_progress', 'name' => 'idx_progress_user_course_status', 'sql' => 'CREATE INDEX idx_progress_user_course_status ON user_video_progress (user_id, course_id, status)'],
    ['table' => 'payments', 'name' => 'idx_payments_user_status', 'sql' => 'CREATE INDEX idx_payments_user_status ON payments (user_id, status)'],
    ['table' => 'webinar_registrations', 'name' => 'idx_webinar_reg_user', 'sql' => 'CREATE INDEX idx_webinar_reg_user ON webinar_registrations (user_id, webinar_id)'],
    ['table' => 'queries', 'name' => 'idx_queries_user_created', 'sql' => 'CREATE INDEX idx_queries_user_created ON queries (user_id, created_at)']
];

foreach ($indexes as $idx) {
    try {
        $check = DB::fetchAll("SHOW INDEX FROM {$idx['table']} WHERE Key_name = ?", [$idx['name']]);
        if (empty($check)) {
            DB::query($idx['sql']);
            echo "[+] Added index {$idx['name']} to table {$idx['table']}\n";
        } else {
            echo "[=] Index {$idx['name']} already exists on {$idx['table']}\n";
        }
    } catch (Exception $e) {
        echo "[!] Warning on {$idx['table']}: " . $e->getMessage() . "\n";
    }
}

echo "Database Indexing Complete!\n";
