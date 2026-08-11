<?php
// Admin Email Template Editor
require_once __DIR__ . '/admin_header.php';
require_once dirname(__DIR__) . '/helpers/EmailHelper.php';

$key = $_GET['key'] ?? '';
if (empty($key)) {
    set_flash_message('danger', 'Template key is required.');
    header("Location: email_templates.php");
    exit;
}

// Fetch template details
try {
    $template = DB::fetch("SELECT * FROM email_templates WHERE template_key = ?", [$key]);
    if (!$template) {
        set_flash_message('danger', 'Template not found.');
        header("Location: email_templates.php");
        exit;
    }
} catch (Exception $e) {
    set_flash_message('danger', 'Database Error: ' . $e->getMessage());
    header("Location: email_templates.php");
    exit;
}

$csrfToken = generate_csrf_token();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: email_template_edit.php?key=" . urlencode($key));
        exit;
    }
    
    $action = $_POST['action'] ?? 'save';
    
    if ($action === 'save') {
        $subject = trim($_POST['subject'] ?? '');
        $body = $_POST['body'] ?? '';
        
        if (empty($subject) || empty($body)) {
            set_flash_message('danger', 'Subject and Body cannot be empty.');
        } else {
            try {
                DB::query(
                    "UPDATE email_templates SET subject = ?, body = ? WHERE template_key = ?",
                    [$subject, $body, $key]
                );
                set_flash_message('success', 'Email template updated successfully!');
            } catch (Exception $e) {
                set_flash_message('danger', 'Failed to update template: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'send_test') {
        $testEmail = trim($_POST['test_email'] ?? '');
        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            set_flash_message('danger', 'Please enter a valid email address.');
        } else {
            // Seed mock data for testing
            $mockData = [
                'user_name' => 'John Doe (Test)',
                'course_title' => 'Advanced Full-Stack Web Development',
                'webinar_title' => 'Vite & Next.js Core Concepts',
                'webinar_date' => date('d M, Y', strtotime('+3 days')),
                'webinar_time' => '06:30 PM',
                'otp' => '123456',
                'video_title' => 'Email Template System Architecture'
            ];
            
            $sent = EmailHelper::sendTemplateEmail($testEmail, 'Test recipient', $key, $mockData);
            if ($sent) {
                set_flash_message('success', 'Test email sent successfully to ' . htmlspecialchars($testEmail));
            } else {
                set_flash_message('danger', 'Failed to send test email. Check SMTP logs.');
            }
        }
    }
    
    header("Location: email_template_edit.php?key=" . urlencode($key));
    exit;
}

// Convert placeholders string to array for display
$supportedPlaceholders = array_map('trim', explode(',', $template['placeholders'] ?? ''));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="email_templates.php" class="btn btn-link text-decoration-none p-0 mb-2 d-inline-flex align-items-center">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Templates
        </a>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Edit Template: <?php echo htmlspecialchars($template['name']); ?></h1>
    </div>
</div>

<div class="row">
    <!-- Editor and Preview Column -->
    <div class="col-lg-9 mb-4">
        <div class="card shadow-sm border-0 rounded-4 bg-white">
            <div class="card-header bg-white border-bottom pt-3 pb-0 px-4">
                <!-- Nav Tabs for Editor / Preview -->
                <ul class="nav nav-tabs border-bottom-0" id="templateTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold text-muted px-4 py-3" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit-pane" type="button" role="tab" aria-controls="edit-pane" aria-selected="true">
                            <i class="fa-solid fa-code me-2"></i>Edit Template
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold text-muted px-4 py-3" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview-pane" type="button" role="tab" aria-controls="preview-pane" aria-selected="false" onclick="updateLivePreview()">
                            <i class="fa-solid fa-eye me-2"></i>Live Preview
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="tab-content" id="templateTabsContent">
                <!-- Edit Pane -->
                <div class="tab-pane fade show active p-4" id="edit-pane" role="tabpanel" aria-labelledby="edit-tab">
                    <form action="email_template_edit.php?key=<?php echo urlencode($key); ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="action" value="save">
                        
                        <div class="mb-3">
                            <label for="template_key" class="form-label fw-semibold text-muted">Template Unique Key</label>
                            <input type="text" class="form-control bg-light" id="template_key" value="<?php echo htmlspecialchars($template['template_key']); ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label fw-semibold text-dark">Subject Line</label>
                            <input type="text" class="form-control border-secondary-subtle" id="subject" name="subject" value="<?php echo htmlspecialchars($template['subject']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="template_body" class="form-label fw-semibold text-dark">HTML Template Body</label>
                            <textarea class="form-control font-monospace text-dark bg-light p-3" id="template_body" name="body" rows="18" style="font-size: 13px; line-height: 1.6;" required><?php echo htmlspecialchars($template['body']); ?></textarea>
                        </div>
                        
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-5">Save Changes</button>
                        </div>
                    </form>
                </div>
                
                <!-- Preview Pane -->
                <div class="tab-pane fade p-4" id="preview-pane" role="tabpanel" aria-labelledby="preview-tab">
                    <div class="bg-light border rounded-4 overflow-hidden" style="height: 600px;">
                        <iframe id="preview_frame" class="w-100 h-100 border-0 bg-white"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar documentation & Test Tools -->
    <div class="col-lg-3">
        <!-- Test Email Card -->
        <div class="card shadow-sm border-0 rounded-4 bg-white p-4 mb-4">
            <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-paper-plane me-2"></i>Send Test Email</h5>
            <p class="text-muted small">Send a copy of this email to a custom address populated with demo values.</p>
            
            <form action="email_template_edit.php?key=<?php echo urlencode($key); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="send_test">
                
                <div class="mb-3">
                    <label for="test_email" class="form-label fw-semibold small text-muted">Recipient Email</label>
                    <input type="email" class="form-control form-control-sm" id="test_email" name="test_email" placeholder="e.g. admin@example.com" required>
                </div>
                
                <button type="submit" class="btn btn-sm btn-outline-primary w-100 rounded-pill py-2">
                    <i class="fa-solid fa-envelope-circle-check me-1"></i> Send Test
                </button>
            </form>
        </div>

        <!-- Placeholders Card -->
        <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-circle-question text-primary me-2"></i>Supported Tags</h5>
            <p class="text-muted small">You can insert these placeholders in the subject line or template HTML body. They will be parsed dynamically:</p>
            
            <div class="mb-3">
                <div class="fw-bold text-secondary small mb-2">Context Specific:</div>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($supportedPlaceholders as $ph): ?>
                        <?php if (!in_array($ph, ['company_logo', 'company_name', 'dashboard_url', 'support_email', 'website', 'phone', 'current_year'])): ?>
                            <span class="badge bg-light text-primary border cursor-pointer" onclick="insertAtCursor('{{<?php echo htmlspecialchars($ph); ?>}}')">{{<?php echo htmlspecialchars($ph); ?>}}</span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div>
                <div class="fw-bold text-secondary small mb-2">Global Settings:</div>
                <div class="d-flex flex-wrap gap-1">
                    <span class="badge bg-light text-success border cursor-pointer" onclick="insertAtCursor('{{company_name}}')">{{company_name}}</span>
                    <span class="badge bg-light text-success border cursor-pointer" onclick="insertAtCursor('{{support_email}}')">{{support_email}}</span>
                    <span class="badge bg-light text-success border cursor-pointer" onclick="insertAtCursor('{{phone}}')">{{phone}}</span>
                    <span class="badge bg-light text-success border cursor-pointer" onclick="insertAtCursor('{{website}}')">{{website}}</span>
                    <span class="badge bg-light text-success border cursor-pointer" onclick="insertAtCursor('{{dashboard_url}}')">{{dashboard_url}}</span>
                    <span class="badge bg-light text-success border cursor-pointer" onclick="insertAtCursor('{{company_logo}}')">{{company_logo}}</span>
                    <span class="badge bg-light text-success border cursor-pointer" onclick="insertAtCursor('{{current_year}}')">{{current_year}}</span>
                </div>
            </div>
            
            <hr class="my-3">
            <div class="bg-light p-2 rounded text-muted small">
                <i class="fa-solid fa-lightbulb text-warning me-1"></i><strong>Tip:</strong> Click any badge above to insert it into the HTML code editor at your cursor position!
            </div>
        </div>
    </div>
</div>

<script>
// Insert tag at cursor position inside body textarea
function insertAtCursor(text) {
    const textarea = document.getElementById('template_body');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const currentVal = textarea.value;
    
    textarea.value = currentVal.substring(0, start) + text + currentVal.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + text.length;
}

// Render dynamic preview inside iframe
function updateLivePreview() {
    let body = document.getElementById('template_body').value;
    
    // Global mockup values
    const mocks = {
        'company_name': 'CAVA LMS Portal',
        'support_email': 'support@cavalms.com',
        'phone': '+91 98765 43210',
        'website': '<?php echo SITE_URL; ?>',
        'dashboard_url': '<?php echo SITE_URL; ?>/login.php',
        'company_logo': '<?php echo SITE_URL; ?>/assets/images/logo.jpeg',
        'current_year': new Date().getFullYear(),
        
        // Context mockup values
        'user_name': 'John Doe',
        'course_title': 'Advanced Software Engineering Masterclass',
        'webinar_title': 'Global Career Transitions in Tech',
        'webinar_date': '<?php echo date('d M, Y', strtotime('+3 days')); ?>',
        'webinar_time': '06:30 PM',
        'otp': '984021',
        'video_title': 'Understanding MVC Architecture in PHP'
    };
    
    // Replace all placeholders in body
    for (const [key, val] of Object.entries(mocks)) {
        const regex = new RegExp(`\\{\\{${key}\\}\\}`, 'g');
        body = body.replace(regex, val);
    }
    
    const iframe = document.getElementById('preview_frame');
    iframe.srcdoc = body;
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
