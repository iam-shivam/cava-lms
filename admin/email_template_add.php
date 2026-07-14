<?php
// Admin Email Template Add
require_once __DIR__ . '/admin_header.php';

$csrfToken = generate_csrf_token();
$errors = [];

$key = '';
$name = '';
$subject = '';
$placeholders = '';
$body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td align="center" style="background:#6D28D9;padding:40px;">
                            <img src="{{company_logo}}" width="120" alt="{{company_name}}" style="margin-bottom:15px;display:block;">
                            <h1 style="margin:0;color:#ffffff;font-size:28px;">New Notification</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px;">
                            <h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
                            <p style="font-size:16px;line-height:26px;color:#555555;">
                                This is a new custom email template. You can customize this text and add custom tags.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
                            <h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
                            <p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
                            <p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: email_templates.php");
        exit;
    }
    
    $key = trim($_POST['template_key'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $placeholders = trim($_POST['placeholders'] ?? '');
    $body = $_POST['body'] ?? '';
    
    // Validations
    if (empty($key)) {
        $errors['template_key'] = 'Template Key is required.';
    } elseif (!preg_match('/^[a-z0-9_]+$/', $key)) {
        $errors['template_key'] = 'Template Key must contain only lowercase letters, numbers, and underscores (no spaces).';
    } else {
        // Check uniqueness
        try {
            $existing = DB::fetch("SELECT id FROM email_templates WHERE template_key = ?", [$key]);
            if ($existing) {
                $errors['template_key'] = 'This template key is already taken.';
            }
        } catch (Exception $e) {
            $errors['db'] = $e->getMessage();
        }
    }
    
    if (empty($name)) {
        $errors['name'] = 'Template Name is required.';
    }
    if (empty($subject)) {
        $errors['subject'] = 'Subject Line is required.';
    }
    if (empty($body)) {
        $errors['body'] = 'HTML Body is required.';
    }
    
    if (empty($errors)) {
        try {
            $uuid = generate_uuid();
            DB::query(
                "INSERT INTO email_templates (id, template_key, name, subject, body, placeholders) VALUES (?, ?, ?, ?, ?, ?)",
                [$uuid, $key, $name, $subject, $body, $placeholders]
            );
            set_flash_message('success', 'Email template created successfully!');
            header("Location: email_templates.php");
            exit;
        } catch (Exception $e) {
            set_flash_message('danger', 'Database Error: ' . $e->getMessage());
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="email_templates.php" class="btn btn-link text-decoration-none p-0 mb-2 d-inline-flex align-items-center">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Templates
        </a>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Add New Email Template</h1>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4 p-md-5">
    <form action="email_template_add.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        
        <div class="row">
            <!-- Left Side Details -->
            <div class="col-md-6 mb-3">
                <label for="template_key" class="form-label fw-bold">Template Unique Key <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?php echo isset($errors['template_key']) ? 'is-invalid' : ''; ?>" 
                       id="template_key" name="template_key" placeholder="e.g. promotional_newsletter" 
                       value="<?php echo htmlspecialchars($key); ?>" required>
                <div class="invalid-feedback"><?php echo $errors['template_key'] ?? ''; ?></div>
                <small class="text-muted">Unique identifier used in PHP code triggers (lowercase, numbers, and underscores only).</small>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label fw-bold">Template Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                       id="name" name="name" placeholder="e.g. Monthly Promotional Email" 
                       value="<?php echo htmlspecialchars($name); ?>" required>
                <div class="invalid-feedback"><?php echo $errors['name'] ?? ''; ?></div>
                <small class="text-muted">Friendly name displayed inside the Admin Template Manager dashboard.</small>
            </div>
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label fw-bold">Default Subject Line <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo isset($errors['subject']) ? 'is-invalid' : ''; ?>" 
                   id="subject" name="subject" placeholder="e.g. Get 20% Off - Exclusive Learning Deal!" 
                   value="<?php echo htmlspecialchars($subject); ?>" required>
            <div class="invalid-feedback"><?php echo $errors['subject'] ?? ''; ?></div>
            <small class="text-muted">Supports dynamic tags (e.g. <code>Welcome to {{company_name}}</code>).</small>
        </div>
        
        <div class="mb-3">
            <label for="placeholders" class="form-label fw-bold">Custom Context Placeholders</label>
            <input type="text" class="form-control" id="placeholders" name="placeholders" 
                   placeholder="e.g. user_name, discount_code, expiry_date" 
                   value="<?php echo htmlspecialchars($placeholders); ?>">
            <small class="text-muted">Comma-separated list of placeholder tags this template uses. Global configuration tags like <code>{{company_name}}</code>, <code>{{support_email}}</code>, and <code>{{current_year}}</code> are available automatically.</small>
        </div>

        <div class="mb-4">
            <label for="template_body" class="form-label fw-bold">HTML Email Template Body <span class="text-danger">*</span></label>
            <textarea class="form-control font-monospace text-dark bg-light p-3" id="template_body" name="body" rows="15" style="font-size: 13px; line-height: 1.6;" required><?php echo htmlspecialchars($body); ?></textarea>
            <small class="text-muted">Responsive inline CSS HTML code. Make sure any context-specific placeholders are wrapped in double braces (e.g., <code>{{user_name}}</code>).</small>
        </div>

        <div class="text-end border-top pt-3">
            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2">Create Template</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
