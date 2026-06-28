<?php
// Admin Support Queries
require_once __DIR__ . '/admin_header.php';
require_once dirname(__DIR__) . '/models/Query.php';

$action = trim($_GET['action'] ?? '');
$id = intval($_GET['id'] ?? 0);

if ($action === 'resolve' && $id > 0) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid or unauthorized request.');
        header("Location: queries.php");
        exit;
    }
    try {
        Query::resolve($id);
        set_flash_message('success', 'Query marked as Resolved.');
    } catch (Exception $e) {
        set_flash_message('danger', 'Database Error: ' . $e->getMessage());
    }
    header("Location: queries.php");
    exit;
}

$queries = Query::getAll();
?>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4">
    <h5 class="fw-bold text-dark mb-4">Support Queries Management</h5>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th style="max-width: 300px;">Query Message</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($queries)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No support queries received yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($queries as $q): ?>
                        <tr class="<?php echo $q['status'] === 'Resolved' ? 'table-light text-muted' : ''; ?>">
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($q['name']); ?></td>
                            <td><?php echo htmlspecialchars($q['email']); ?></td>
                            <td><?php echo htmlspecialchars($q['mobile_number']); ?></td>
                            <td style="max-width: 300px; white-space: normal; word-wrap: break-word;">
                                <div class="fs-7"><?php echo htmlspecialchars($q['query_message']); ?></div>
                                <?php if ($q['resolved_at']): ?>
                                    <span class="d-block text-success fs-8 mt-1"><i class="fa-solid fa-check-double me-1"></i>Resolved on: <?php echo date('d M, Y h:i A', strtotime($q['resolved_at'])); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $q['status'] === 'Resolved' ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo $q['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d M, Y h:i A', strtotime($q['created_at'])); ?></td>
                            <td class="text-end">
                                <?php if ($q['status'] === 'Pending'): ?>
                                    <a href="queries.php?action=resolve&id=<?php echo $q['id']; ?>" 
                                       class="btn btn-sm btn-success rounded-pill px-3" 
                                       onclick="confirmAction(event, 'Mark this query as resolved?', this.href)">
                                        <i class="fa-solid fa-check me-1"></i> Resolve
                                    </a>
                                <?php else: ?>
                                    <span class="text-success"><i class="fa-solid fa-circle-check fs-5"></i></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
