<?php
// Admin Payments Audit Log
require_once __DIR__ . '/admin_header.php';
require_once dirname(__DIR__) . '/models/Payment.php';

// Pagination settings
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
if (!in_array($perPage, [10, 20, 50, 100])) $perPage = 10;
$currentPage = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;

$totalRow = DB::fetch("SELECT COUNT(*) as total FROM payments");
$totalPayments = intval($totalRow['total']);
$totalPages = max(1, ceil($totalPayments / $perPage));
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;

$payments = DB::fetchAll("
    SELECT p.*, u.full_name as user_name, u.email as user_email, u.mobile_number as user_mobile,
           CASE 
               WHEN p.item_type = 'course' THEN (SELECT title FROM courses WHERE id = p.item_id)
               WHEN p.item_type = 'webinar' THEN (SELECT title FROM webinars WHERE id = p.item_id)
           END as item_title
    FROM payments p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
    LIMIT $perPage OFFSET $offset
");
?>

<style>
    /* Table styling for one-line display */
    .payments-table {
        white-space: nowrap;
        font-size: 0.85rem;
    }
    
    /* Eye icon animation */
    tr[data-bs-toggle="collapse"] .eye-icon {
        transition: transform 0.3s ease, color 0.3s ease;
    }
    tr[data-bs-toggle="collapse"][aria-expanded="true"] .eye-icon {
        transform: scale(1.3);
        color: var(--bs-primary) !important;
    }
</style>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4">
    <h5 class="fw-bold text-dark mb-4">Payments Transaction Log</h5>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle payments-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Item Purchased</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="10" class="text-center text-muted">No transactions recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $index => $p): 
                        $collapseId = 'details_' . md5($p['id']);
                    ?>
                        <tr data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" style="cursor: pointer;" title="Click to view details">
                            <td class="text-muted fw-semibold"><?php echo $offset + $index + 1; ?></td>
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($p['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($p['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($p['user_mobile']); ?></td>
                            <td class="fw-bold text-dark">
                                <?php echo htmlspecialchars($p['item_title'] ?? 'N/A'); ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $p['item_type'] === 'course' ? 'bg-primary-light text-primary' : 'bg-secondary-light text-secondary'; ?>">
                                    <?php echo ucfirst($p['item_type']); ?>
                                </span>
                            </td>
                            <td class="fw-bold text-success">₹<?php echo number_format($p['amount'], 2); ?></td>
                            <td>
                                <?php if ($p['status'] === 'Success'): ?>
                                    <span class="badge bg-success">Success</span>
                                <?php elseif ($p['status'] === 'Pending'): ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Failed</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M, Y h:i A', strtotime($p['created_at'])); ?></td>
                            <td><i class="fa-solid fa-eye text-muted eye-icon"></i></td>
                        </tr>
                        <!-- Hidden Details Row -->
                        <tr class="collapse" id="<?php echo $collapseId; ?>">
                            <td colspan="10" class="bg-light border-bottom-0 py-3 px-4">
                                <div class="row text-muted fs-7">
                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <strong class="d-block mb-1 text-dark">Payment Method</strong>
                                        <?php if (!empty($p['payment_method'])): ?>
                                            <span class="badge bg-secondary"><?php echo strtoupper(htmlspecialchars($p['payment_method'])); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <strong class="d-block mb-1 text-dark">Currency</strong>
                                        <span class="text-dark fw-bold"><?php echo htmlspecialchars($p['payment_currency'] ?? 'INR'); ?></span>
                                    </div>
                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <strong class="d-block mb-1 text-dark">Razorpay Order ID</strong>
                                        <code class="text-dark bg-white px-2 py-1 border rounded"><?php echo htmlspecialchars($p['razorpay_order_id']); ?></code>
                                    </div>
                                    <div class="col-md-3">
                                        <strong class="d-block mb-1 text-dark">Razorpay Payment ID</strong>
                                        <?php if ($p['razorpay_payment_id']): ?>
                                            <code class="text-dark bg-white px-2 py-1 border rounded"><?php echo htmlspecialchars($p['razorpay_payment_id']); ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
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
    <?php if ($totalPayments > 0): ?>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">
        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalPayments); ?> of <?php echo $totalPayments; ?> entries
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small mb-0">Show</label>
          <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='payments.php?per_page='+this.value+'&pg=1'">
            <?php foreach ([10, 20, 50, 100] as $opt): ?>
              <option value="<?php echo $opt; ?>" <?php echo $perPage == $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
            <?php endforeach; ?>
          </select>
          <span class="text-muted small">entries</span>
        </div>
        
        <nav aria-label="Payments pagination">
          <ul class="pagination mb-0">
            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
              <a class="page-link" href="payments.php?pg=<?php echo $currentPage - 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Previous">&laquo;</a>
            </li>
            <?php
            $startP = max(1, $currentPage - 2);
            $endP = min($totalPages, $currentPage + 2);
            if ($startP > 1): ?>
              <li class="page-item"><a class="page-link" href="payments.php?pg=1&per_page=<?php echo $perPage; ?>">1</a></li>
              <?php if ($startP > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
            <?php endif; ?>
            <?php for ($p = $startP; $p <= $endP; $p++): ?>
              <li class="page-item <?php echo $p == $currentPage ? 'active' : ''; ?>">
                <a class="page-link" href="payments.php?pg=<?php echo $p; ?>&per_page=<?php echo $perPage; ?>"><?php echo $p; ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($endP < $totalPages): ?>
              <?php if ($endP < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
              <li class="page-item"><a class="page-link" href="payments.php?pg=<?php echo $totalPages; ?>&per_page=<?php echo $perPage; ?>"><?php echo $totalPages; ?></a></li>
            <?php endif; ?>
            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
              <a class="page-link" href="payments.php?pg=<?php echo $currentPage + 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Next">&raquo;</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
