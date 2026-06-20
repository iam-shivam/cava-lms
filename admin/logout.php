<?php
// Admin Logout Script
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/controllers/AuthController.php';

AuthController::adminLogout();
