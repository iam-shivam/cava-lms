<?php
// Admin Enrollments List
require_once __DIR__ . '/admin_header.php';

// Pagination settings
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
if (!in_array($perPage, [10, 20, 50, 100])) $perPage = 10;
$currentPage = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;

$totalRow = DB::fetch("SELECT COUNT(*) as total FROM enrollments");
$totalEnrollments = intval($totalRow['total']);
$totalPages = max(1, ceil($totalEnrollments / $perPage));
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;

// Fetch Enrollments joining user and course details
$enrollments = DB::fetchAll("
    SELECT e.*, u.full_name as user_name, u.email as user_email, c.title as course_title, p.razorpay_payment_id
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN payments p ON e.payment_id = p.id
    ORDER BY e.enrolled_at DESC
    LIMIT $perPage OFFSET $offset
");
?>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4">
    <h5 class="fw-bold text-dark mb-4">Course Enrollments</h5>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Course Title</th>
                    <th>Razorpay Payment ID</th>
                    <th>Enrolled At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($enrollments)): ?>
                    <tr><td colspan="6" class="text-center text-muted">No enrollments recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($enrollments as $index => $e): ?>
                        <tr>
                            <td class="text-muted fw-semibold"><?php echo $offset + $index + 1; ?></td>
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($e['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($e['user_email']); ?></td>
                            <td><div class="fw-bold text-primary"><?php echo htmlspecialchars($e['course_title']); ?></div></td>
                            <td>
                                <?php if ($e['razorpay_payment_id']): ?>
                                    <code class="text-secondary"><?php echo htmlspecialchars($e['razorpay_payment_id']); ?></code>
                                <?php else: ?>
                                    <span class="text-muted fs-8">Manual/None</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M, Y h:i A', strtotime($e['enrolled_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if ($totalEnrollments > 0): ?>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">
        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalEnrollments); ?> of <?php echo $totalEnrollments; ?> entries
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small mb-0">Show</label>
          <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='enrollments.php?per_page='+this.value+'&pg=1'">
            <?php foreach ([10, 20, 50, 100] as $opt): ?>
              <option value="<?php echo $opt; ?>" <?php echo $perPage == $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
            <?php endforeach; ?>
          </select>
          <span class="text-muted small">entries</span>
        </div>
        
        <nav aria-label="Enrollments pagination">
          <ul class="pagination mb-0">
            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
              <a class="page-link" href="enrollments.php?pg=<?php echo $currentPage - 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Previous">&laquo;</a>
            </li>
            <?php
            $startP = max(1, $currentPage - 2);
            $endP = min($totalPages, $currentPage + 2);
            if ($startP > 1): ?>
              <li class="page-item"><a class="page-link" href="enrollments.php?pg=1&per_page=<?php echo $perPage; ?>">1</a></li>
              <?php if ($startP > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
            <?php endif; ?>
            <?php for ($p = $startP; $p <= $endP; $p++): ?>
              <li class="page-item <?php echo $p == $currentPage ? 'active' : ''; ?>">
                <a class="page-link" href="enrollments.php?pg=<?php echo $p; ?>&per_page=<?php echo $perPage; ?>"><?php echo $p; ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($endP < $totalPages): ?>
              <?php if ($endP < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
              <li class="page-item"><a class="page-link" href="enrollments.php?pg=<?php echo $totalPages; ?>&per_page=<?php echo $perPage; ?>"><?php echo $totalPages; ?></a></li>
            <?php endif; ?>
            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
              <a class="page-link" href="enrollments.php?pg=<?php echo $currentPage + 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Next">&raquo;</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
