<?php
// Admin Email Templates List
require_once __DIR__ . '/admin_header.php';

$csrfToken = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: email_templates.php");
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        set_flash_message('danger', 'Template deletion is disabled by system policy.');
        header("Location: email_templates.php");
        exit;
    }
}

try {
    $templates = DB::fetchAll("SELECT * FROM email_templates ORDER BY template_key ASC");
} catch (Exception $e) {
    $templates = [];
    set_flash_message('danger', 'Database Error: ' . $e->getMessage());
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800 fw-bold"><i class="fa-solid fa-envelope me-2 text-primary"></i>Email Template Manager</h1>
    <a href="email_template_add.php" class="btn btn-primary rounded-pill px-4">
        <i class="fa-solid fa-plus me-1"></i> Add New Template
    </a>
</div>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4">
    <p class="text-muted">Manage all database-stored HTML email templates. Templates support dynamic placeholders which are rendered at runtime before dispatching emails.</p>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Template Name</th>
                    <th>Template Key</th>
                    <th>Subject Line</th>
                    <th>Supported Placeholders</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($templates)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No email templates found in the database. Click "Add New Template" to create one.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($templates as $t): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($t['name']); ?></div>
                                <small class="text-muted">Last Updated: <?php echo date('d M Y, h:i A', strtotime($t['updated_at'])); ?></small>
                            </td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($t['template_key']); ?></span></td>
                            <td><?php echo htmlspecialchars($t['subject']); ?></td>
                            <td>
                                <?php 
                                $placeholders = explode(',', $t['placeholders'] ?? '');
                                foreach ($placeholders as $ph) {
                                    $ph = trim($ph);
                                    if ($ph !== '') {
                                        echo '<span class="badge bg-light text-primary border me-1 my-1">{{' . htmlspecialchars($ph) . '}}</span>';
                                    }
                                }
                                ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="email_template_edit.php?key=<?php echo urlencode($t['template_key']); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                    </a>
                                    <button class="btn btn-sm btn-light rounded-pill px-3 text-muted border" disabled title="Template Deletion is Disabled by System Policy">
                                        <i class="fa-solid fa-lock me-1"></i> Locked
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

