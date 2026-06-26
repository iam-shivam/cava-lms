<?php
// Secure Video Streaming Proxy
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/VideoOTP.php';

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$userId = $_SESSION['user_id'];
$videoId = intval($_GET['id'] ?? 0);

if ($videoId <= 0 || !VideoOTP::hasValidSession($userId, $videoId)) {
    header("HTTP/1.1 403 Forbidden");
    exit('Access Denied or OTP Session Expired.');
}

$video = DB::fetch("SELECT video_url FROM course_videos WHERE id = ?", [$videoId]);

if (!$video || empty($video['video_url'])) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

$file = BASE_PATH . '/' . $video['video_url'];

if (!file_exists($file) || !is_readable($file)) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

// Serve video file with Range support
$fp = @fopen($file, 'rb');
$size   = filesize($file);
$length = $size;
$start  = 0;
$end    = $size - 1;

header('Content-type: video/mp4');
header("Accept-Ranges: bytes");

if (isset($_SERVER['HTTP_RANGE'])) {
    $c_start = $start;
    $c_end   = $end;

    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    if (strpos($range, ',') !== false) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes $start-$end/$size");
        exit;
    }
    
    if ($range == '-') {
        $c_start = $size - substr($range, 1);
    } else {
        $range  = explode('-', $range);
        $c_start = $range[0];
        $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
    }
    
    $c_end = ($c_end > $end) ? $end : $c_end;
    
    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes $start-$end/$size");
        exit;
    }
    
    $start  = $c_start;
    $end    = $c_end;
    $length = $end - $start + 1;
    fseek($fp, $start);
    header('HTTP/1.1 206 Partial Content');
}

header("Content-Range: bytes $start-$end/$size");
header("Content-Length: ".$length);

$buffer = 1024 * 8;
while(!feof($fp) && ($p = ftell($fp)) <= $end) {
    if ($p + $buffer > $end) {
        $buffer = $end - $p + 1;
    }
    set_time_limit(0);
    echo fread($fp, $buffer);
    flush();
}
fclose($fp);
exit;
