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
    $heroImgKeys  = ['hero_img_1', 'hero_img_2', 'hero_img_3', 'hero_img_4', 'hero_img_5'];
    
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

        // Process Hero Image Uploads
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
            set_flash_message('success', 'Portal configurations and hero images updated successfully!');
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
                    <h6 class="fw-bold text-dark"><i class="fa-solid fa-pager text-primary me-2"></i>Landing Hero Content</h6>
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

            <!-- Hero Collage Images Management Section -->
            <div class="col-12 mt-3">
                <div class="border-bottom pb-2 mb-4">
                    <h6 class="fw-bold text-dark"><i class="fa-regular fa-images text-primary me-2"></i>Landing Hero Collage Student Images (5 Capsules)</h6>
                    <small class="text-muted">Upload custom photos for each capsule in the homepage hero collage. Supported formats: JPG, PNG, WEBP, GIF.</small>
                </div>

                <div class="row g-4">
                    <?php 
                    $capsuleLabels = [
                        'hero_img_1' => ['label' => 'Capsule 1 (Left Tall Pill)', 'default' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&q=80&w=500&h=700'],
                        'hero_img_2' => ['label' => 'Capsule 2 (Top Circle)', 'default' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=400&h=400'],
                        'hero_img_3' => ['label' => 'Capsule 3 (Center Tall Pill)', 'default' => 'https://images.unsplash.com/photo-1603415526960-f7e0328c63b1?auto=format&fit=crop&q=80&w=500&h=700'],
                        'hero_img_4' => ['label' => 'Capsule 4 (Bottom Circle)', 'default' => 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?auto=format&fit=crop&q=80&w=400&h=400'],
                        'hero_img_5' => ['label' => 'Capsule 5 (Right Tall Pill)', 'default' => 'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?auto=format&fit=crop&q=80&w=500&h=700'],
                    ];

                    foreach ($capsuleLabels as $key => $meta):
                        $currentImg = $meta['default'];
                        if (!empty($currentSettings[$key])) {
                            if (file_exists(BASE_PATH . '/uploads/' . $currentSettings[$key])) {
                                $currentImg = SITE_URL . '/uploads/' . $currentSettings[$key];
                            }
                        }
                    ?>
                        <div class="col-md-4 col-lg-2-4 mb-3">
                            <div class="card h-100 border p-3 rounded-4 bg-light text-center">
                                <label class="form-label fw-semibold fs-7 mb-2 text-dark"><?php echo $meta['label']; ?></label>
                                <div class="mb-3 d-flex align-items-center justify-content-center overflow-hidden rounded-3 bg-white border" style="height: 120px;">
                                    <img src="<?php echo $currentImg; ?>" alt="Hero preview" class="img-fluid h-100 w-100" style="object-fit: cover;">
                                </div>
                                <input type="file" class="form-control form-control-sm" name="<?php echo $key; ?>" accept="image/*">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="text-end border-top pt-4 mt-4">
            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-semibold">
                <i class="fa-solid fa-floppy-disk me-2"></i>Save Portal Settings & Images
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
