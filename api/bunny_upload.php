<?php
// api/bunny_upload.php

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/services/BunnyStreamService.php';

header('Content-Type: application/json');

// 1. Admin Authentication Check
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// 2. CSRF Token Verification
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'CSRF verification failed.']);
    exit;
}

// 3. File upload check
if (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
    $errCode = $_FILES['video_file']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['success' => false, 'message' => 'No video file uploaded or upload error occurred. Code: ' . $errCode]);
    exit;
}

// 4. Input validation
$courseId = trim($_POST['course_id'] ?? '');
$sectionId = trim($_POST['section_id'] ?? '');
$videoTitle = trim($_POST['video_title'] ?? '');
$description = trim($_POST['description'] ?? '');
$sortOrder = intval($_POST['sort_order'] ?? 0);
$isEdit = (trim($_POST['is_edit'] ?? '0') === '1');
$videoId = trim($_POST['video_id'] ?? ''); // only for edit mode

if (empty($courseId) || empty($sectionId) || empty($videoTitle)) {
    echo json_encode(['success' => false, 'message' => 'Please provide section, title, and course ID.']);
    exit;
}

if ($isEdit && empty($videoId)) {
    echo json_encode(['success' => false, 'message' => 'Missing Video ID for update.']);
    exit;
}

// 4.5. Check for duplicate video title
if ($isEdit) {
    $existingTitle = DB::fetch("SELECT id FROM course_videos WHERE title = ? AND course_id = ? AND id != ?", [$videoTitle, $courseId, $videoId]);
} else {
    $existingTitle = DB::fetch("SELECT id FROM course_videos WHERE title = ? AND course_id = ?", [$videoTitle, $courseId]);
}
if ($existingTitle) {
    echo json_encode(['success' => false, 'message' => 'A video lesson with this title already exists in the course.']);
    exit;
}

// 5. File size validation (configurable max size, e.g. 500MB)
$maxSize = 524288000; // 500MB
if ($_FILES['video_file']['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'Video file exceeds the maximum allowed size of 500MB.']);
    exit;
}

// 6. MIME Type & extension validation
$tmpPath = $_FILES['video_file']['tmp_name'];
$fileName = $_FILES['video_file']['name'];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $tmpPath);
finfo_close($finfo);

$allowedMimeTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-matroska', 'video/x-msvideo'];
$allowedExtensions = ['mp4', 'webm', 'ogg', 'mov', 'mkv', 'avi'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($mimeType, $allowedMimeTypes) || !in_array($fileExt, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file format. Only video files (MP4, WebM, Ogg, MOV, MKV, AVI) are allowed. Detected: ' . htmlspecialchars($mimeType)]);
    exit;
}

try {
    // 7. Initialize Bunny Stream Service
    $bunnyService = new BunnyStreamService();
    
    // Create Bunny Stream video slot
    $bunnyVideoId = $bunnyService->createVideo($videoTitle);
    
    // Upload actual binary
    $bunnyService->uploadVideo($bunnyVideoId, $tmpPath);
    
    // 8. Document resource handling (Optional document file if provided)
    $documentUrl = null;
    $existing = null;
    if ($isEdit) {
        $existing = DB::fetch("SELECT document_url, video_url, video_provider, bunny_video_id FROM course_videos WHERE id = ? AND course_id = ?", [$videoId, $courseId]);
        if ($existing) {
            $documentUrl = $existing['document_url'];
        }
    }
    
    if (!empty($_FILES['document_file']['name']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $docDir = BASE_PATH . '/uploads/documents/';
        if (!is_dir($docDir)) mkdir($docDir, 0755, true);
        
        $docExt = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
        $docName = 'doc_' . time() . '_' . uniqid() . '.' . $docExt;
        
        if (move_uploaded_file($_FILES['document_file']['tmp_name'], $docDir . $docName)) {
            // Delete old document if edit
            if ($isEdit && !empty($existing['document_url']) && file_exists(BASE_PATH . '/' . $existing['document_url'])) {
                @unlink(BASE_PATH . '/' . $existing['document_url']);
            }
            $documentUrl = 'uploads/documents/' . $docName;
        }
    }

    // 9. Save database entry
    $db = DB::getConnection();
    if ($isEdit) {
        // Cleanup old local video file if provider was local
        if ($existing && $existing['video_provider'] === 'local' && !empty($existing['video_url']) && file_exists(BASE_PATH . '/' . $existing['video_url'])) {
            @unlink(BASE_PATH . '/' . $existing['video_url']);
        }
        // Cleanup old Bunny video if provider was bunny
        if ($existing && $existing['video_provider'] === 'bunny' && !empty($existing['bunny_video_id'])) {
            try {
                $bunnyService->deleteVideo($existing['bunny_video_id']);
            } catch (Exception $ex) {
                // Silently ignore deletion error of old video to prevent transaction crash
            }
        }

        $sql = "UPDATE course_videos SET section_id = ?, title = ?, description = ?, video_url = NULL, video_provider = 'bunny', bunny_video_id = ?, document_url = ?, sort_order = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$sectionId, $videoTitle, $description, $bunnyVideoId, $documentUrl, $sortOrder, $videoId]);
    } else {
        $newId = generate_uuid();
        $sql = "INSERT INTO course_videos (id, section_id, course_id, title, description, video_url, video_provider, bunny_video_id, document_url, sort_order) VALUES (?, ?, ?, ?, ?, NULL, 'bunny', ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$newId, $sectionId, $courseId, $videoTitle, $description, $bunnyVideoId, $documentUrl, $sortOrder]);
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Video uploaded and registered in Bunny Stream successfully!',
        'bunny_video_id' => $bunnyVideoId
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => htmlspecialchars($e->getMessage())]);
    exit;
}
