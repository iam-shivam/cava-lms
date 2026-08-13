<?php
// Admin Payments Audit Log
require_once __DIR__ . '/admin_header.php';
require_once dirname(__DIR__) . '/models/Payment.php';

$payments = Payment::getAllPayments();
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
                    <tr><td colspan="9" class="text-center text-muted">No transactions recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): 
                        $collapseId = 'details_' . md5($p['id']);
                    ?>
                        <tr data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" style="cursor: pointer;" title="Click to view details">
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
                            <td colspan="9" class="bg-light border-bottom-0 py-3 px-4">
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
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
