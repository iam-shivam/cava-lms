<?php
// Course Model

class Course {
    
    public static function getAll() {
        return DB::fetchAll("SELECT c.*, cat.name as category_name FROM courses c JOIN categories cat ON c.category_id = cat.id WHERE c.status = 'Published' ORDER BY c.id DESC");
    }
    
    public static function getFeatured($limit = 3) {
        return DB::fetchAll("SELECT c.*, cat.name as category_name FROM courses c JOIN categories cat ON c.category_id = cat.id WHERE c.status = 'Published' ORDER BY c.id DESC LIMIT ?", [$limit]);
    }
    
    public static function getBySlug($slug) {
        return DB::fetch("SELECT c.*, cat.name as category_name FROM courses c JOIN categories cat ON c.category_id = cat.id WHERE c.slug = ? AND c.status = 'Published'", [$slug]);
    }
    
    public static function getById($id) {
        return DB::fetch("SELECT c.*, cat.name as category_name FROM courses c JOIN categories cat ON c.category_id = cat.id WHERE c.id = ?", [$id]);
    }
    
    public static function getSections($courseId) {
        return DB::fetchAll("SELECT * FROM course_sections WHERE course_id = ? ORDER BY sort_order ASC, id ASC", [$courseId]);
    }
    
    public static function getVideosBySection($sectionId) {
        return DB::fetchAll("SELECT * FROM course_videos WHERE section_id = ? ORDER BY sort_order ASC, id ASC", [$sectionId]);
    }
    
    public static function getSyllabus($courseId) {
        $sections = self::getSections($courseId);
        $syllabus = [];
        foreach ($sections as $sec) {
            $videos = self::getVideosBySection($sec['id']);
            $syllabus[] = [
                'section' => $sec,
                'videos' => $videos
            ];
        }
        return $syllabus;
    }
    
    public static function countLessons($courseId) {
        $row = DB::fetch("SELECT COUNT(id) as total FROM course_videos WHERE course_id = ?", [$courseId]);
        return $row ? $row['total'] : 0;
    }
    
    public static function isUserEnrolled($userId, $courseId) {
        if (!$userId) return false;
        $row = DB::fetch("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
        return !empty($row);
    }
    
    public static function getEnrolledCourses($userId) {
        return DB::fetchAll("
            SELECT c.*, e.enrolled_at 
            FROM enrollments e 
            JOIN courses c ON e.course_id = c.id 
            WHERE e.user_id = ?
            ORDER BY e.enrolled_at DESC
        ", [$userId]);
    }
    
    public static function getCategories() {
        return DB::fetchAll("SELECT * FROM categories ORDER BY name ASC");
    }
}
