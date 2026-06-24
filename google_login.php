
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Initiate Google OAuth login
AuthController::googleLogin();
?>
