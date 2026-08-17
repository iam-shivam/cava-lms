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
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    if ($action === 'delete') {
        set_flash_message('danger', 'Template deletion is disabled by system policy.');
        header("Location: email_templates.php");
        exit;
    }
}

// Pagination settings
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
if (!in_array($perPage, [10, 20, 50, 100])) $perPage = 10;
$currentPage = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;

$totalRow = DB::fetch("SELECT COUNT(*) as total FROM email_templates");
$totalTemplates = intval($totalRow['total']);
$totalPages = max(1, ceil($totalTemplates / $perPage));
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;

try {
    $templates = DB::fetchAll("SELECT * FROM email_templates ORDER BY template_key ASC LIMIT $perPage OFFSET $offset");
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
                    <th style="width: 50px;">#</th>
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
                        <td colspan="6" class="text-center py-4 text-muted">No email templates found in the database. Click "Add New Template" to create one.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($templates as $index => $t): ?>
                        <tr>
                            <td class="text-muted fw-semibold"><?php echo $offset + $index + 1; ?></td>
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
                                <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                    <a href="email_template_edit.php?key=<?php echo urlencode($t['template_key']); ?>" 
                                       class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center" 
                                       style="width: 38px; height: 38px; padding: 0; border-radius: 10px; font-size: 1.05rem;" 
                                       title="Edit Template">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="email_templates.php?action=delete&key=<?php echo urlencode($t['template_key']); ?>" 
                                       class="btn btn-outline-danger d-inline-flex align-items-center justify-content-center" 
                                       style="width: 38px; height: 38px; padding: 0; border-radius: 10px; font-size: 1.05rem;" 
                                       onclick="confirmAction(event, 'Are you sure you want to delete this email template?', this.href);"
                                       title="Delete Template">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if ($totalTemplates > 0): ?>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">
        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalTemplates); ?> of <?php echo $totalTemplates; ?> entries
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small mb-0">Show</label>
          <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='email_templates.php?per_page='+this.value+'&pg=1'">
            <?php foreach ([10, 20, 50, 100] as $opt): ?>
              <option value="<?php echo $opt; ?>" <?php echo $perPage == $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
            <?php endforeach; ?>
          </select>
          <span class="text-muted small">entries</span>
        </div>
        
        <nav aria-label="Email templates pagination">
          <ul class="pagination mb-0">
            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
              <a class="page-link" href="email_templates.php?pg=<?php echo $currentPage - 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Previous">&laquo;</a>
            </li>
            <?php
            $startP = max(1, $currentPage - 2);
            $endP = min($totalPages, $currentPage + 2);
            if ($startP > 1): ?>
              <li class="page-item"><a class="page-link" href="email_templates.php?pg=1&per_page=<?php echo $perPage; ?>">1</a></li>
              <?php if ($startP > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
            <?php endif; ?>
            <?php for ($p = $startP; $p <= $endP; $p++): ?>
              <li class="page-item <?php echo $p == $currentPage ? 'active' : ''; ?>">
                <a class="page-link" href="email_templates.php?pg=<?php echo $p; ?>&per_page=<?php echo $perPage; ?>"><?php echo $p; ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($endP < $totalPages): ?>
              <?php if ($endP < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
              <li class="page-item"><a class="page-link" href="email_templates.php?pg=<?php echo $totalPages; ?>&per_page=<?php echo $perPage; ?>"><?php echo $totalPages; ?></a></li>
            <?php endif; ?>
            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
              <a class="page-link" href="email_templates.php?pg=<?php echo $currentPage + 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Next">&raquo;</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

