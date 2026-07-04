<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';

// Prevent caching for secure content
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$docId = trim($_GET['id'] ?? '');

if (empty($docId) || empty($_SESSION['user_id'])) {
    http_response_code(403);
    die('Unauthorized: Please log in to view this document.');
}

$userId = $_SESSION['user_id'];

try {
    // Verify document exists and get metadata
    $doc = DB::fetch("SELECT * FROM video_documents WHERE id = ?", [$docId]);
    if (!$doc) {
        http_response_code(404);
        die('Document not found.');
    }

    // Verify video exists
    $video = DB::fetch("SELECT * FROM course_videos WHERE id = ?", [$doc['video_id']]);
    if (!$video) {
        http_response_code(404);
        die('Associated video not found.');
    }

    $courseId = $video['course_id'];

    // Admin override check
    $isAdmin = isset($_SESSION['admin_id']);

    if (!$isAdmin) {
        // Verify course is purchased, active, and not expired
        $enrollment = DB::fetch("
            SELECT * FROM enrollments 
            WHERE user_id = ? AND course_id = ? AND status = 'Active'
        ", [$userId, $courseId]);

        if (!$enrollment) {
            http_response_code(403);
            die('Unauthorized: Active course enrollment required.');
        }

        if (!empty($enrollment['expiry_date']) && strtotime($enrollment['expiry_date']) < time()) {
            http_response_code(403);
            die('Unauthorized: Your course enrollment has expired.');
        }

        // Check if video is unlocked (if partial payments apply)
        $course = Course::getById($courseId);
        if ($course && $course['allow_partial_payment']) {
            $payment = DB::fetch("SELECT * FROM payments WHERE user_id = ? AND item_id = ? AND item_type = 'course' AND status = 'Success' ORDER BY created_at DESC LIMIT 1", [$userId, $courseId]);
            if ($payment && $payment['payment_type'] === 'Partial') {
                // Determine if this specific section/video is unlocked (simplified check - assume 50% sections are locked for partial)
                // In cava-lms, partial payment usually unlocks only first half of sections.
                // We'll rely on the existing logic:
                $sections = DB::fetchAll("SELECT id FROM course_sections WHERE course_id = ? ORDER BY sort_order ASC, id ASC", [$courseId]);
                $totalSections = count($sections);
                $allowedSections = ceil($totalSections / 2);
                $isAllowed = false;
                for ($i = 0; $i < $allowedSections; $i++) {
                    if ($sections[$i]['id'] == $video['section_id']) {
                        $isAllowed = true;
                        break;
                    }
                }
                if (!$isAllowed) {
                    http_response_code(403);
                    die('Unauthorized: Please complete full payment to unlock this video and its resources.');
                }
            }
        }
    }

    $filePath = BASE_PATH . '/' . $doc['file_path'];
    if (!file_exists($filePath)) {
        http_response_code(404);
        die('The physical file could not be found on the server.');
    }

    $mimeType = mime_content_type($filePath);
    if (!$mimeType) {
        $mimeType = 'application/octet-stream';
    }

    // Force PDF to show inline, others as attachment or inline depending on browser support
    $disposition = ($doc['file_type'] === 'pdf') ? 'inline' : 'attachment';
    
    // Enforce custom header for PDFs to block direct network tab downloads
    if ($doc['file_type'] === 'pdf' && !$isAdmin) {
        if (!isset($_SERVER['HTTP_X_VIEWER_AUTH']) || $_SERVER['HTTP_X_VIEWER_AUTH'] !== 'true') {
            http_response_code(403);
            die('Unauthorized: Direct downloading of PDFs is disabled. Please view the document securely through the course player.');
        }
    }

    header('Content-Type: ' . $mimeType);
    $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $doc['title']) . '.' . $doc['file_type'];
    header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Accept-Ranges: bytes');

    readfile($filePath);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    die('Internal Server Error: ' . $e->getMessage());
}
