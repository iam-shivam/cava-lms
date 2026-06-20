<?php
// Admin Payments Audit Log
require_once __DIR__ . '/admin_header.php';
require_once dirname(__DIR__) . '/models/Payment.php';

$payments = Payment::getAllPayments();
?>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4">
    <h5 class="fw-bold text-dark mb-4">Payments Transaction Log</h5>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Item Purchased</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Razorpay Order ID</th>
                    <th>Razorpay Payment ID</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="9" class="text-center text-muted">No transactions recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($p['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($p['user_email']); ?></td>
                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($p['item_title'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge <?php echo $p['item_type'] === 'course' ? 'bg-primary-light text-primary' : 'bg-secondary-light text-secondary'; ?>">
                                    <?php echo ucfirst($p['item_type']); ?>
                                </span>
                            </td>
                            <td class="fw-bold text-success">₹<?php echo number_format($p['amount'], 2); ?></td>
                            <td><code><?php echo htmlspecialchars($p['razorpay_order_id']); ?></code></td>
                            <td>
                                <?php if ($p['razorpay_payment_id']): ?>
                                    <code><?php echo htmlspecialchars($p['razorpay_payment_id']); ?></code>
                                <?php else: ?>
                                    <span class="text-muted fs-8">N/A</span>
                                <?php endif; ?>
                            </td>
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
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
