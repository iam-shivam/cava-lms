<?php
// Dedicated Support & Queries Page
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Query.php';

$userId = $_SESSION['user_id'] ?? null;
$myQueries = $userId ? Query::getByUser($userId) : [];

$pageTitle       = 'Support Center';
$pageDescription = 'Have questions about immigration, courses, or your account? Submit a support query on CAVA LMS and our team will get back to you promptly.';
?>

<?php require_once __DIR__ . '/views/layout/header.php'; ?>

<!-- Header Banner -->
<div class="bg-light py-5 mb-5 border-bottom">
    <div class="container text-center">
        <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill fw-semibold mb-2">Helpdesk Portal</span>
        <h1 class="fw-extrabold display-5 text-dark">Support Center & Queries</h1>
        <p class="text-muted col-md-6 mx-auto">Have visa, profile draw, or course syllabus doubts? Submit a query below and track your support tickets.</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Left Column: Submit Query Form -->
        <div class="col-lg-6 mb-5 mb-lg-0">
            <div class="custom-card border-0 shadow-lg p-4 p-md-5 bg-white rounded-4 animate-fade-in-up">
                <h3 class="fw-bold mb-2"><i class="fa-solid fa-paper-plane text-primary me-2"></i>Submit Support Request</h3>
                <p class="text-muted mb-4">Complete the fields below. Registered users can track query status updates in real-time.</p>
                
                <form action="submit_query.php" method="POST">
                    <div class="mb-3">
                        <label for="query_name" class="form-label fw-semibold text-muted">Full Name</label>
                        <input type="text" class="form-control bg-light" id="query_name" name="name" 
                               value="<?php echo $userId ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" placeholder="e.g. Aman Mehta" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="query_email" class="form-label fw-semibold text-muted">Email Address</label>
                        <input type="email" class="form-control bg-light" id="query_email" name="email" 
                               value="<?php echo $userId ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" placeholder="aman@example.com" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="query_mobile" class="form-label fw-semibold text-muted">Mobile Number</label>
                        <input type="tel" class="form-control bg-light" id="query_mobile" name="mobile_number" placeholder="9876543210" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="query_message" class="form-label fw-semibold text-muted">Query Message Details</label>
                        <textarea class="form-control bg-light" id="query_message" name="query_message" rows="5" placeholder="State your PR eligibility, CRS scores, or purchase queries..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold">Submit Query Details</button>
                </form>
            </div>
        </div>

        <!-- Right Column: Support Tickets / Query History (Logged-In User) -->
        <div class="col-lg-6">
            <?php if (!$userId): ?>
                <div class="card border-0 shadow-sm p-5 bg-white rounded-4 text-center h-100 d-flex flex-column justify-content-center align-items-center">
                    <i class="fa-solid fa-lock text-muted fs-1 mb-3"></i>
                    <h4 class="fw-bold text-dark">Track Your Queries</h4>
                    <p class="text-muted mb-4" style="max-width: 320px;">Log in to your account to view submission history, response status, and resolution details.</p>
                    <a href="login.php" class="btn btn-outline-primary rounded-pill px-4">Login to Track Tickets</a>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm p-4 bg-white rounded-4 h-100">
                    <h4 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-circle-question text-primary me-2"></i>My Support Tickets</h4>
                    
                    <?php if (empty($myQueries)): ?>
                        <div class="text-center py-5">
                            <i class="fa-regular fa-comment-dots fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted">You have not submitted any questions yet. Use the form on the left to ask details.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-auto" style="max-height: 480px;">
                            <?php foreach ($myQueries as $q): ?>
                                <div class="border rounded-4 p-3 mb-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted fs-8"><i class="fa-regular fa-clock me-1"></i><?php echo date('d M, Y', strtotime($q['created_at'])); ?></span>
                                        <span class="badge <?php echo $q['status'] === 'Resolved' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                            <?php echo $q['status']; ?>
                                        </span>
                                    </div>
                                    <p class="text-dark fs-7 mb-0 fw-medium">
                                        <?php echo htmlspecialchars($q['query_message']); ?>
                                    </p>
                                    <?php if ($q['resolved_at']): ?>
                                        <div class="mt-2 text-success fs-8 border-top pt-2">
                                            <i class="fa-solid fa-check-double me-1"></i> Resolved on: <?php echo date('d M, Y h:i A', strtotime($q['resolved_at'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
