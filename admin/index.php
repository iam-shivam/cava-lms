<?php
// Admin Main Entry router
require_once dirname(__DIR__) . '/config/config.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: " . SITE_URL . "/admin/dashboard.php");
    exit;
} else {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}
