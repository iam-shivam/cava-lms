<?php
// Admin Support Queries
require_once __DIR__ . '/admin_header.php';
require_once dirname(__DIR__) . '/models/Query.php';

$action = trim($_GET['action'] ?? '');
$id = trim($_GET['id'] ?? '');

if ($action === 'resolve' && !empty($id)) {
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

// Pagination settings
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
if (!in_array($perPage, [10, 20, 50, 100])) $perPage = 10;
$currentPage = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;

$totalRow = DB::fetch("SELECT COUNT(*) as total FROM queries");
$totalQueries = intval($totalRow['total']);
$totalPages = max(1, ceil($totalQueries / $perPage));
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;

$queries = DB::fetchAll("SELECT * FROM queries ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
?>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4">
    <h5 class="fw-bold text-dark mb-4">Support Queries Management</h5>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>From</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th style="max-width: 300px;">Query Message</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($queries)): ?>
                    <tr><td colspan="8" class="text-center text-muted">No support queries received yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($queries as $index => $q): ?>
                        <tr class="<?php echo $q['status'] === 'Resolved' ? 'table-light text-muted' : ''; ?>">
                            <td class="text-muted fw-semibold"><?php echo $offset + $index + 1; ?></td>
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

    <!-- Pagination Controls -->
    <?php if ($totalQueries > 0): ?>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">
        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalQueries); ?> of <?php echo $totalQueries; ?> entries
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small mb-0">Show</label>
          <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='queries.php?per_page='+this.value+'&pg=1'">
            <?php foreach ([10, 20, 50, 100] as $opt): ?>
              <option value="<?php echo $opt; ?>" <?php echo $perPage == $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
            <?php endforeach; ?>
          </select>
          <span class="text-muted small">entries</span>
        </div>
        
        <nav aria-label="Queries pagination">
          <ul class="pagination mb-0">
            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
              <a class="page-link" href="queries.php?pg=<?php echo $currentPage - 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Previous">&laquo;</a>
            </li>
            <?php
            $startP = max(1, $currentPage - 2);
            $endP = min($totalPages, $currentPage + 2);
            if ($startP > 1): ?>
              <li class="page-item"><a class="page-link" href="queries.php?pg=1&per_page=<?php echo $perPage; ?>">1</a></li>
              <?php if ($startP > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
            <?php endif; ?>
            <?php for ($p = $startP; $p <= $endP; $p++): ?>
              <li class="page-item <?php echo $p == $currentPage ? 'active' : ''; ?>">
                <a class="page-link" href="queries.php?pg=<?php echo $p; ?>&per_page=<?php echo $perPage; ?>"><?php echo $p; ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($endP < $totalPages): ?>
              <?php if ($endP < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
              <li class="page-item"><a class="page-link" href="queries.php?pg=<?php echo $totalPages; ?>&per_page=<?php echo $perPage; ?>"><?php echo $totalPages; ?></a></li>
            <?php endif; ?>
            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
              <a class="page-link" href="queries.php?pg=<?php echo $currentPage + 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Next">&raquo;</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
