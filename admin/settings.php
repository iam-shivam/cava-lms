<?php
// Admin Settings Editor
require_once __DIR__ . '/admin_header.php';

$csrfToken = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: settings.php");
        exit;
    }
    
    $settingsKeys = ['site_title', 'contact_email', 'contact_phone', 'about_us', 'hero_title', 'hero_subtitle'];
    
    try {
        $stmt = DB::getConnection()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        
        foreach ($settingsKeys as $key) {
            $value = trim($_POST[$key] ?? '');
            $stmt->execute([$key, $value, $value]);
        }
        
        set_flash_message('success', 'Portal configurations updated successfully!');
    } catch (Exception $e) {
        set_flash_message('danger', 'Database Error: ' . $e->getMessage());
    }
    
    header("Location: settings.php");
    exit;
}

// Fetch current configurations
$currentSettings = [];
try {
    $rows = DB::fetchAll("SELECT * FROM settings");
    foreach ($rows as $row) {
        $currentSettings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Silent fail
}
?>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4 p-md-5">
    <h5 class="fw-bold text-primary mb-4"><i class="fa-solid fa-gears me-2"></i>Global Portal Configurations</h5>
    
    <form action="settings.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        
        <div class="row">
            <!-- Site Info Section -->
            <div class="col-md-6 mb-4">
                <div class="border-bottom pb-2 mb-3">
                    <h6 class="fw-bold text-dark"><i class="fa-solid fa-circle-info text-primary me-2"></i>General Identity</h6>
                </div>
                
                <div class="mb-3">
                    <label for="site_title" class="form-label fw-semibold">LMS Site Title</label>
                    <input type="text" class="form-control" id="site_title" name="site_title" 
                           value="<?php echo htmlspecialchars($currentSettings['site_title'] ?? 'CAVA LMS Portal'); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="contact_email" class="form-label fw-semibold">Support Email Address</label>
                    <input type="email" class="form-control" id="contact_email" name="contact_email" 
                           value="<?php echo htmlspecialchars($currentSettings['contact_email'] ?? 'support@cavalms.com'); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="contact_phone" class="form-label fw-semibold">Support Contact Phone</label>
                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                           value="<?php echo htmlspecialchars($currentSettings['contact_phone'] ?? '+91 98765 43210'); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="about_us" class="form-label fw-semibold">About Portal Description</label>
                    <textarea class="form-control" id="about_us" name="about_us" rows="4" required><?php echo htmlspecialchars($currentSettings['about_us'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <!-- Hero Layout Section -->
            <div class="col-md-6 mb-4">
                <div class="border-bottom pb-2 mb-3">
                    <h6 class="fw-bold text-dark"><i class="fa-solid fa-pager text-primary me-2"></i>Landing Hero Banner</h6>
                </div>
                
                <div class="mb-3">
                    <label for="hero_title" class="form-label fw-semibold">Hero Heading</label>
                    <input type="text" class="form-control" id="hero_title" name="hero_title" 
                           value="<?php echo htmlspecialchars($currentSettings['hero_title'] ?? ''); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="hero_subtitle" class="form-label fw-semibold">Hero Subheading / Subtitle</label>
                    <textarea class="form-control" id="hero_subtitle" name="hero_subtitle" rows="3" required><?php echo htmlspecialchars($currentSettings['hero_subtitle'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="text-end border-top pt-3">
            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2">Save Portal Settings</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
