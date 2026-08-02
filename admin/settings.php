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
    
    $settingsKeys = [
        'site_title', 'contact_email', 'contact_phone', 'about_us', 
        'hero_eyebrow', 'hero_title', 'hero_subtitle', 
        'hero_stat_1_title', 'hero_stat_1_desc', 
        'hero_stat_2_title', 'hero_stat_2_desc', 
        'hero_stat_3_title', 'hero_stat_3_desc'
    ];
    $heroImgKeys  = ['hero_img_1'];
    
    try {
        $currentSettings = [];
        $rows = DB::fetchAll("SELECT setting_key, setting_value FROM settings");
        foreach ($rows as $row) {
            $currentSettings[$row['setting_key']] = $row['setting_value'];
        }

        $stmt = DB::getConnection()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        
        $changesMade = false;
        
        // Process text configuration fields
        foreach ($settingsKeys as $key) {
            $value = trim($_POST[$key] ?? '');
            if (!isset($currentSettings[$key]) || $currentSettings[$key] !== $value) {
                $stmt->execute([$key, $value, $value]);
                $changesMade = true;
            }
        }

        // Process Hero Image Upload
        $uploadFileDir = BASE_PATH . '/uploads/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMimeTypes  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        foreach ($heroImgKeys as $imgKey) {
            if (isset($_FILES[$imgKey]) && $_FILES[$imgKey]['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES[$imgKey]['tmp_name'];
                $fileName    = $_FILES[$imgKey]['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedMime = finfo_file($finfo, $fileTmpPath);
                finfo_close($finfo);

                if (in_array($fileExtension, $allowedExtensions) && in_array($detectedMime, $allowedMimeTypes)) {
                    $newFileName = md5(time() . $fileName . rand(100, 999)) . '.' . $fileExtension;
                    $destPath = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $stmt->execute([$imgKey, $newFileName, $newFileName]);
                        $changesMade = true;
                    }
                }
            }
        }
        
        if ($changesMade) {
            set_flash_message('success', 'Portal configurations and hero media updated successfully!');
        } else {
            set_flash_message('success', 'No changes were made.');
        }
    } catch (Exception $e) {
        set_flash_message('danger', 'Database Error: ' . $e->getMessage());
    }
    
    header("Location: settings.php");
    exit;
}

// Fetch current configurations
$currentSettings = [];
try {
    $rows = DB::fetchAll("SELECT setting_key, setting_value FROM settings");
    foreach ($rows as $row) {
        $currentSettings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Silent fail
}
?>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4 p-md-5">
    <h5 class="fw-bold text-primary mb-4"><i class="fa-solid fa-gears me-2"></i>Global Portal Configurations</h5>
    
    <form action="settings.php" method="POST" enctype="multipart/form-data">
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
            
            <!-- Hero Layout Text Section -->
            <div class="col-md-6 mb-4">
                <div class="border-bottom pb-2 mb-3">
                    <h6 class="fw-bold text-dark"><i class="fa-solid fa-pager text-primary me-2"></i>Landing Hero Content & Copy</h6>
                </div>

                <div class="mb-3">
                    <label for="hero_eyebrow" class="form-label fw-semibold">Hero Eyebrow (Top Small Text)</label>
                    <input type="text" class="form-control" id="hero_eyebrow" name="hero_eyebrow" 
                           value="<?php echo htmlspecialchars($currentSettings['hero_eyebrow'] ?? 'Cava Career Abroad Visa Academy'); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="hero_title" class="form-label fw-semibold">Hero Main Heading</label>
                    <input type="text" class="form-control" id="hero_title" name="hero_title" 
                           value="<?php echo htmlspecialchars($currentSettings['hero_title'] ?? 'Upgrade Your Skills with CAVA LMS'); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="hero_subtitle" class="form-label fw-semibold">Hero Subtitle / Description</label>
                    <textarea class="form-control" id="hero_subtitle" name="hero_subtitle" rows="3" required><?php echo htmlspecialchars($currentSettings['hero_subtitle'] ?? 'Access high-quality courses, webinars, and masterclasses designed by industry experts to boost your career.'); ?></textarea>
                </div>
            </div>

            <!-- Hero Statistics Row Controls -->
            <div class="col-12 mb-4">
                <div class="border-bottom pb-2 mb-3">
                    <h6 class="fw-bold text-dark"><i class="fa-solid fa-chart-simple text-primary me-2"></i>Hero Highlights & Statistics Row</h6>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-4 border">
                            <label class="form-label fw-semibold text-dark mb-1">Highlight 1 Title</label>
                            <input type="text" class="form-control form-control-sm mb-2" name="hero_stat_1_title" value="<?php echo htmlspecialchars($currentSettings['hero_stat_1_title'] ?? 'Global Students'); ?>">
                            <label class="form-label fw-semibold text-dark mb-1">Highlight 1 Subtitle</label>
                            <input type="text" class="form-control form-control-sm" name="hero_stat_1_desc" value="<?php echo htmlspecialchars($currentSettings['hero_stat_1_desc'] ?? 'Ages 12-18'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-4 border">
                            <label class="form-label fw-semibold text-dark mb-1">Highlight 2 Title</label>
                            <input type="text" class="form-control form-control-sm mb-2" name="hero_stat_2_title" value="<?php echo htmlspecialchars($currentSettings['hero_stat_2_title'] ?? 'Top Instructors'); ?>">
                            <label class="form-label fw-semibold text-dark mb-1">Highlight 2 Subtitle</label>
                            <input type="text" class="form-control form-control-sm" name="hero_stat_2_desc" value="<?php echo htmlspecialchars($currentSettings['hero_stat_2_desc'] ?? 'Ivy League Experts'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-4 border">
                            <label class="form-label fw-semibold text-dark mb-1">Highlight 3 Title</label>
                            <input type="text" class="form-control form-control-sm mb-2" name="hero_stat_3_title" value="<?php echo htmlspecialchars($currentSettings['hero_stat_3_title'] ?? 'Tailored Guidance'); ?>">
                            <label class="form-label fw-semibold text-dark mb-1">Highlight 3 Subtitle</label>
                            <input type="text" class="form-control form-control-sm" name="hero_stat_3_desc" value="<?php echo htmlspecialchars($currentSettings['hero_stat_3_desc'] ?? 'Micro-group classes'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Single Hero Image Management Section -->
            <div class="col-12 mt-2">
                <div class="border-bottom pb-2 mb-3">
                    <h6 class="fw-bold text-dark"><i class="fa-regular fa-image text-primary me-2"></i>Landing Hero Banner Image</h6>
                    <small class="text-muted">Upload high-resolution single hero image banner displayed on homepage. Formats: JPG, PNG, WEBP, GIF.</small>
                </div>

                <?php 
                $heroSingleImg = 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=1000&h=600';
                if (!empty($currentSettings['hero_img_1']) && file_exists(BASE_PATH . '/uploads/' . $currentSettings['hero_img_1'])) {
                    $heroSingleImg = SITE_URL . '/uploads/' . $currentSettings['hero_img_1'];
                }
                ?>
                <div class="row align-items-center g-4">
                    <div class="col-md-6">
                        <div class="card border p-3 rounded-4 bg-light text-center">
                            <label class="form-label fw-semibold text-dark mb-2">Upload Hero Image</label>
                            <input type="file" class="form-control" name="hero_img_1" accept="image/*">
                            <span class="fs-8 text-muted mt-2 d-block">Recommended size: 1000x600px or 16:9 ratio.</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rounded-4 overflow-hidden border shadow-sm" style="max-height: 220px;">
                            <img src="<?php echo $heroSingleImg; ?>" alt="Hero Banner Preview" class="w-100 h-100" style="object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-end border-top pt-4 mt-4">
            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-semibold">
                <i class="fa-solid fa-floppy-disk me-2"></i>Save Portal Settings & Hero Image
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
