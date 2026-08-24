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

// Pagination & Search settings
$search = trim($_GET['search'] ?? '');
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
if (!in_array($perPage, [10, 20, 50, 100])) $perPage = 10;
$currentPage = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;

$whereClause = "";
$queryParams = [];
if ($search !== '') {
    $whereClause = " WHERE ticket_number LIKE ? OR name LIKE ? OR email LIKE ? OR mobile_number LIKE ? OR query_message LIKE ?";
    $sParam = "%" . $search . "%";
    $queryParams = [$sParam, $sParam, $sParam, $sParam, $sParam];
}

$totalRow = DB::fetch("SELECT COUNT(*) as total FROM queries" . $whereClause, $queryParams);
$totalQueries = intval($totalRow['total']);
$totalPages = max(1, ceil($totalQueries / $perPage));
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;

$queries = DB::fetchAll("SELECT * FROM queries" . $whereClause . " ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $queryParams);
?>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h5 class="fw-bold text-dark mb-0">Support Queries Management</h5>
        <form method="GET" action="queries.php" class="d-flex align-items-center">
            <input type="hidden" name="per_page" value="<?php echo $perPage; ?>">
            <div class="d-flex align-items-center border rounded-3 px-3 bg-white shadow-sm" style="max-width: 380px; width: 100%; border-color: #d1d5db !important; min-height: 40px;">
                <button type="submit" class="btn p-0 border-0 text-secondary me-2 shadow-none d-flex align-items-center" title="Search">
                    <i class="fa-solid fa-magnifying-glass fs-6 text-muted"></i>
                </button>
                <input type="text" name="search" id="adminQuerySearchInput" class="form-control border-0 p-0 shadow-none bg-transparent text-dark" placeholder="Search ticket #, name, email..." value="<?php echo htmlspecialchars($search); ?>" style="font-size: 0.875rem; color: #1f2937;">
                <?php if (!empty($search)): ?>
                    <div class="border-start ms-2 ps-2 d-flex align-items-center" style="height: 20px; border-color: #e5e7eb !important;">
                        <a href="queries.php?per_page=<?php echo $perPage; ?>" class="text-secondary text-decoration-none d-flex align-items-center px-1" title="Clear Search">
                            <i class="fa-solid fa-xmark fs-6"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Ticket #</th>
                    <th>From</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th style="max-width: 280px;">Query Message</th>
                    <th>Status</th>
                    <th style="font-size: 0.8rem;">Created Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($queries)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No support queries received yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($queries as $index => $q): ?>
                        <tr class="<?php echo $q['status'] === 'Resolved' ? 'table-light' : ''; ?>">
                            <td class="text-muted fw-semibold" style="font-size:0.8rem;"><?php echo $offset + $index + 1; ?></td>
                            <td>
                                <span class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-2 px-2 py-1 fw-bold font-monospace" style="font-size: 0.78rem; background-color: #f3e8ff; color: #6d28d9; border: 1px solid #ddd6fe;">
                                    <i class="fa-solid fa-ticket me-1 opacity-75"></i><?php echo htmlspecialchars($q['ticket_number'] ?? '------'); ?>
                                </span>
                            </td>
                            <td class="fw-semibold text-dark" style="font-size:0.875rem;"><?php echo htmlspecialchars($q['name']); ?></td>
                            <td style="font-size:0.85rem;"><a href="mailto:<?php echo htmlspecialchars($q['email']); ?>" class="text-decoration-none text-body"><?php echo htmlspecialchars($q['email']); ?></a></td>
                            <td style="font-size:0.85rem;"><span class="text-muted"><?php echo htmlspecialchars($q['mobile_number']); ?></span></td>
                            <td style="max-width: 280px;">
                                <div class="text-secondary" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; font-size: 0.85rem;"><?php echo htmlspecialchars($q['query_message']); ?></div>
                                <?php if (strlen($q['query_message']) > 80): ?>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none fw-semibold text-purple mt-1" data-bs-toggle="modal" data-bs-target="#queryModal<?php echo $q['id']; ?>" style="color: #6d28d9; font-size: 0.78rem;">
                                        View More &raquo;
                                    </button>
                                <?php endif; ?>
                                <?php if ($q['resolved_at']): ?>
                                    <span class="d-block text-success mt-1" style="font-size:0.75rem;"><i class="fa-solid fa-check-double me-1"></i>Resolved: <?php echo date('d M, Y h:i A', strtotime($q['resolved_at'])); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($q['status'] === 'Resolved'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold" style="background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; font-size: 0.75rem;">
                                        <i class="fa-solid fa-circle-check me-1"></i>Resolved
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fw-semibold" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 0.75rem;">
                                        <i class="fa-regular fa-clock me-1"></i>Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted" style="font-size: 0.75rem; white-space: nowrap;"><?php echo date('d M, Y', strtotime($q['created_at'])); ?><br><span class="text-muted" style="font-size:0.7rem;"><?php echo date('h:i A', strtotime($q['created_at'])); ?></span></td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <?php if ($q['status'] === 'Pending'): ?>
                                        <form method="POST" action="queries.php?action=resolve&id=<?php echo $q['id']; ?>" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 py-1 text-nowrap" style="font-size: 0.78rem;" onclick="return confirm('Mark this query as resolved?');">
                                                <i class="fa-solid fa-circle-check me-1"></i>Resolve
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-nowrap" data-bs-toggle="modal" data-bs-target="#queryModal<?php echo $q['id']; ?>" style="font-size: 0.78rem;">
                                            <i class="fa-regular fa-folder-open me-1"></i>Details
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <!-- Modal for Full Query Message -->
                                <div class="modal fade" id="queryModal<?php echo $q['id']; ?>" tabindex="-1" aria-labelledby="queryModalLabel<?php echo $q['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge rounded-2 px-2 py-1 font-monospace" style="background-color: #f3e8ff; color: #6d28d9; border: 1px solid #ddd6fe;">
                                                        Ticket #<?php echo htmlspecialchars($q['ticket_number'] ?? '------'); ?>
                                                    </span>
                                                    <span class="badge <?php echo $q['status'] === 'Resolved' ? 'bg-success' : 'bg-warning text-dark'; ?> rounded-pill">
                                                        <?php echo $q['status']; ?>
                                                    </span>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start pt-3">
                                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($q['name']); ?></h6>
                                                <p class="text-muted small mb-3">
                                                    <i class="fa-regular fa-envelope me-1"></i><?php echo htmlspecialchars($q['email']); ?> | 
                                                    <i class="fa-solid fa-phone me-1"></i><?php echo htmlspecialchars($q['mobile_number']); ?>
                                                </p>
                                                
                                                <label class="fw-semibold small text-muted mb-1">Query Message:</label>
                                                <div class="p-3 bg-light rounded-3 border text-dark fs-7" style="line-height: 1.6; white-space: pre-wrap;"><?php echo trim(htmlspecialchars($q['query_message'])); ?></div>
                                                
                                                <div class="mt-2 text-muted text-end" style="font-size: 0.75rem;">
                                                    <i class="fa-regular fa-clock me-1"></i>Submitted on <?php echo date('d M, Y \a\t h:i A', strtotime($q['created_at'])); ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <?php if ($q['status'] === 'Pending'): ?>
                                                    <form method="POST" action="queries.php?action=resolve&id=<?php echo $q['id']; ?>" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                        <button type="submit" class="btn btn-success rounded-pill px-4">
                                                            <i class="fa-solid fa-circle-check me-1"></i> Mark as Resolved
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if ($totalQueries > 0): ?>
    <?php $searchQuery = !empty($search) ? '&search=' . urlencode($search) : ''; ?>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">
        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalQueries); ?> of <?php echo $totalQueries; ?> entries
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small mb-0">Show</label>
          <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='queries.php?per_page='+this.value+'&pg=1<?php echo $searchQuery; ?>'">
            <?php foreach ([10, 20, 50, 100] as $opt): ?>
              <option value="<?php echo $opt; ?>" <?php echo $perPage == $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
            <?php endforeach; ?>
          </select>
          <span class="text-muted small">entries</span>
        </div>
        
        <nav aria-label="Queries pagination">
          <ul class="pagination mb-0">
            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
              <a class="page-link" href="queries.php?pg=<?php echo $currentPage - 1; ?>&per_page=<?php echo $perPage . $searchQuery; ?>" aria-label="Previous">&laquo;</a>
            </li>
            <?php
            $startP = max(1, $currentPage - 2);
            $endP = min($totalPages, $currentPage + 2);
            if ($startP > 1): ?>
              <li class="page-item"><a class="page-link" href="queries.php?pg=1&per_page=<?php echo $perPage . $searchQuery; ?>">1</a></li>
              <?php if ($startP > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
            <?php endif; ?>
            <?php for ($p = $startP; $p <= $endP; $p++): ?>
              <li class="page-item <?php echo $p == $currentPage ? 'active' : ''; ?>">
                <a class="page-link" href="queries.php?pg=<?php echo $p; ?>&per_page=<?php echo $perPage . $searchQuery; ?>"><?php echo $p; ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($endP < $totalPages): ?>
              <?php if ($endP < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
              <li class="page-item"><a class="page-link" href="queries.php?pg=<?php echo $totalPages; ?>&per_page=<?php echo $perPage . $searchQuery; ?>"><?php echo $totalPages; ?></a></li>
            <?php endif; ?>
            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
              <a class="page-link" href="queries.php?pg=<?php echo $currentPage + 1; ?>&per_page=<?php echo $perPage . $searchQuery; ?>" aria-label="Next">&raquo;</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
