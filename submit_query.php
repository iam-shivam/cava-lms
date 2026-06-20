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
    
    try {
        $saved = Query::create($userId, $name, $email, $mobile, $message);
        if ($saved) {
            set_flash_message('success', 'Your query has been submitted successfully! We will get back to you soon.');
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
