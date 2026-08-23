<?php
// Query Submission Handler
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Query.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile_number'] ?? '');
    $message = trim($_POST['query_message'] ?? '');
    $userId = $_SESSION['user_id'] ?? null;
    
    if (empty($name) || empty($email) || empty($mobile) || empty($message)) {
        set_flash_message('danger', 'Please fill out all fields in the contact form.');
        header("Location: support.php");
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash_message('danger', 'Invalid email address.');
        header("Location: support.php");
        exit;
    }
    
    // Word count validation (max 100 words)
    $wordCount = str_word_count($message);
    if ($wordCount > 100) {
        set_flash_message('danger', 'Your query message must not exceed 100 words. Currently: ' . $wordCount . ' words.');
        header("Location: support.php");
        exit;
    }
    
    try {
        $ticketNumber = Query::create($userId, $name, $email, $mobile, $message);
        if ($ticketNumber) {
            // Push payload to session for async client-side execution in the footer (Option 2)
            if (defined('GOOGLE_SHEETS_WEBHOOK') && GOOGLE_SHEETS_WEBHOOK && strpos(GOOGLE_SHEETS_WEBHOOK, 'YOUR_SCRIPT_ID') === false) {
                $_SESSION['pending_webhook_payload'] = json_encode([
                    'ticket_number' => $ticketNumber,
                    'name'          => $name,
                    'email'         => $email,
                    'mobile'        => $mobile,
                    'message'       => $message,
                    'user_id'       => $userId ?? 'Guest',
                ]);
            }
            set_flash_message('success', 'Your support query has been received. Ticket #' . $ticketNumber . ' has been created — our team will respond within 24 hours.');
        } else {
            set_flash_message('danger', 'Failed to submit your query. Please try again.');
        }
    } catch (Exception $e) {
        set_flash_message('danger', 'Error: ' . $e->getMessage());
    }
    
    header("Location: support.php");
    exit;
} else {
    header("Location: support.php");
    exit;
}
