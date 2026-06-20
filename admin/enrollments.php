<?php
// Admin Enrollments List
require_once __DIR__ . '/admin_header.php';

// Fetch Enrollments joining user and course details
$enrollments = DB::fetchAll("
    SELECT e.*, u.full_name as user_name, u.email as user_email, c.title as course_title, p.razorpay_payment_id
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN payments p ON e.payment_id = p.id
    ORDER BY e.enrolled_at DESC
");
?>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4">
    <h5 class="fw-bold text-dark mb-4">Course Enrollments</h5>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Course Title</th>
                    <th>Razorpay Payment ID</th>
                    <th>Enrolled At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($enrollments)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No enrollments recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($enrollments as $e): ?>
                        <tr>
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
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
