<?php
// Export webinar registrants as CSV (future support for PDF/Excel)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/admin/login.php');
    exit;
}

$webinarId = intval($_GET['webinar_id'] ?? 0);
$format = strtolower($_GET['format'] ?? 'csv');

if ($webinarId < 0) {
    die('Invalid webinar ID');
}

// Fetch webinar details (optional, for filename) if single webinar
if ($webinarId > 0) {
    $webinar = DB::fetch('SELECT title FROM webinars WHERE id = ?', [$webinarId]);
    if (!$webinar) {
        die('Webinar not found');
    }
}

// Retrieve registrations with user info
if ($webinarId > 0) {
    $registrations = DB::fetchAll(
        "SELECT wr.id, u.full_name, u.email, u.mobile_number, wr.registered_at FROM webinar_registrations wr JOIN users u ON wr.user_id = u.id WHERE wr.webinar_id = ? ORDER BY wr.registered_at ASC",
        [$webinarId]
    );
} else {
    // Bulk export: all webinars with webinar title
    $registrations = DB::fetchAll(
        "SELECT wr.id, w.title AS webinar_title, u.full_name, u.email, u.mobile_number, wr.registered_at FROM webinar_registrations wr JOIN users u ON wr.user_id = u.id JOIN webinars w ON wr.webinar_id = w.id ORDER BY w.title, wr.registered_at ASC"
    );
}

if (empty($registrations)) {
    set_flash_message('danger', 'No users have registered for this webinar.');
    header('Location: webinars.php');
    exit;
}
if ($format === 'csv' || $format === 'excel') {
    $filenameBase = $webinarId > 0 ? 'webinar_' . $webinarId . '_' . preg_replace('/[^a-z0-9]/i', '_', $webinar['title']) : 'all_webinars';
    $filename = $filenameBase . '_registrants_' . date('Ymd_His') . ($format === 'excel' ? '.xlsx' : '.csv');
    if ($format === 'excel') {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    } else {
        header('Content-Type: text/csv; charset=UTF-8');
    }
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    // BOM for Excel UTF-8 support
    fputs($out, "\xEF\xBB\xBF");
    // Header row
    $header = ['ID', 'Full Name', 'Email', 'Mobile Number', 'Registered At'];
    if ($webinarId == 0) {
        array_splice($header, 1, 0, 'Webinar Title'); // Insert Webinar Title after ID
    }
    fputcsv($out, $header);
    foreach ($registrations as $row) {
        $line = [$row['id'], $row['full_name'], $row['email'], $row['mobile_number'], $row['registered_at']];
        if ($webinarId == 0) {
            // Move webinar title to second column
            $line = [$row['id'], $row['webinar_title'], $row['full_name'], $row['email'], $row['mobile_number'], $row['registered_at']];
        }
        fputcsv($out, $line);
    }
    fclose($out);
    exit;
}
die('Requested format not supported yet.');
?>
