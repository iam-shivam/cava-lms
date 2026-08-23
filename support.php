<?php
// Dedicated Support & Queries Page
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Query.php';

$userId = $_SESSION['user_id'] ?? null;
$myQueries = $userId ? Query::getByUser($userId) : [];

$pageTitle       = 'Support Center';
$pageDescription = 'Have questions about immigration, courses, or your account? Submit a support query on CAVA LMS and our team will get back to you promptly.';

require_once __DIR__ . '/views/layout/header.php'; 
?>

<div class="lp-body-scope">
    <!-- Page Hero Banner -->
    <section class="lp-page-hero">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Support</li>
                </ol>
            </nav>
            <h1 class="lp-page-hero-title">Support Center</h1>
            <p class="lp-page-hero-subtitle">Have visa, profile draw, or course syllabus doubts? Submit a query below and track your support tickets in real-time.</p>
        </div>
    </section>

    <!-- Main Container -->
    <section class="py-5 bg-white">
        <div class="container">
            <!-- Support Category Spring Cards -->
            <?php if (false): ?>
            <div class="row g-4 mb-5 text-start">
                <div class="col-md-4">
                    <div class="custom-card border p-4 rounded-4 bg-white support-cat-card">
                        <div class="lp-empty-icon mb-3">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Academic & Courses</h5>
                        <p class="text-muted fs-7 mb-0">Help with locked syllabus, video player, resources & certificates.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-card border p-4 rounded-4 bg-white support-cat-card">
                        <div class="lp-empty-icon mb-3">
                            <i class="fa-solid fa-passport"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Visa & Immigration</h5>
                        <p class="text-muted fs-7 mb-0">Doubts on ECA, CRS calculation, FSW draws, and profile evaluation.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-card border p-4 rounded-4 bg-white support-cat-card">
                        <div class="lp-empty-icon mb-3">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Payments & Invoices</h5>
                        <p class="text-muted fs-7 mb-0">Razorpay transaction status, GST receipts, and installment support.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Left Column: Submit Query Form -->
                <div class="col-lg-6">
                    <div class="custom-card border-0 shadow-sm p-4 p-md-5 bg-white rounded-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="lp-avatar-initials rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="fa-solid fa-paper-plane"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-1 text-dark">Submit Support Request</h3>
                                <p class="text-muted m-0 fs-7">Our support team responds within 24 hours.</p>
                            </div>
                        </div>
                        
                        <form action="submit_query.php" method="POST">
                            <div class="mb-3">
                                <label for="query_name" class="lp-form-label">Full Name</label>
                                <input type="text" class="form-control lp-form-control" id="query_name" name="name" 
                                       value="<?php echo $userId ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" placeholder="e.g. Aman Mehta" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="query_email" class="lp-form-label">Email Address</label>
                                <input type="email" class="form-control lp-form-control" id="query_email" name="email" 
                                       value="<?php echo $userId ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" placeholder="aman@example.com" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="query_mobile" class="lp-form-label">Mobile Number</label>
                                <input type="tel" class="form-control lp-form-control" id="query_mobile" name="mobile_number" placeholder="9876543210" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="query_message" class="lp-form-label">Query Message Details</label>
                                <textarea class="form-control lp-form-control" id="query_message" name="query_message" rows="4" placeholder="State your PR eligibility, CRS scores, or purchase queries in detail..." required oninput="updateWordCount(this)" style="resize: none; height: 120px;"></textarea>
                                <div class="d-flex justify-content-end align-items-center mt-1 gap-2">
                                    <span id="word-limit-error" class="text-danger small fw-semibold" style="display:none; margin-right:auto;">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>Word limit reached!
                                    </span>
                                    <small id="word-count-display" class="fw-semibold text-muted">0 / 100 words</small>
                                    <i class="fa-solid fa-circle-info text-muted" style="cursor:help;" data-bs-toggle="tooltip" data-bs-placement="top" title="Maximum 100 words allowed."></i>
                                </div>
                            </div>
                            
                            <button type="submit" class="lp-btn-primary w-100 py-3" style="border-radius: 100px;">
                                Submit Query Details <i class="fa-solid fa-paper-plane ms-2"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Support Tickets / Query History (Logged-In User) -->
                <div class="col-lg-6">
                    <?php if (!$userId): ?>
                        <div class="custom-card border-0 shadow-sm p-5 bg-white rounded-4 text-center h-100 d-flex flex-column justify-content-center align-items-center">
                            <div class="lp-empty-icon mb-3">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Track Your Queries</h4>
                            <p class="text-muted mb-4" style="max-width: 340px;">Log in to your account to view submission history, response status, and resolution details.</p>
                            <a href="login.php" class="lp-btn-pill-dark">
                                Login to Track Tickets <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="custom-card border-0 shadow-sm p-4 p-md-5 bg-white rounded-4 h-100">
                            <h4 class="fw-bold mb-4 text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-ticket text-primary"></i> My Support Tickets
                            </h4>
                            
                            <?php if (empty($myQueries)): ?>
                                <div class="lp-empty-state py-4">
                                    <div class="lp-empty-icon mb-3" style="width:60px; height:60px; font-size:1.4rem;">
                                        <i class="fa-regular fa-comment-dots"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">No Tickets Found</h5>
                                    <p class="text-muted fs-7">You haven't submitted any questions yet. Fill out the form on the left to get support.</p>
                                </div>
                            <?php else: ?>
                                <div class="overflow-auto pe-1" style="max-height: 480px;">
                                    <?php foreach ($myQueries as $q): ?>
                                        <div class="border rounded-4 p-3 mb-3 bg-light">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-dark rounded-pill px-2 py-1 fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                                        <i class="fa-solid fa-ticket me-1"></i><?php echo htmlspecialchars($q['ticket_number'] ?? '------'); ?>
                                                    </span>
                                                    <span class="text-muted fs-8"><i class="fa-regular fa-clock me-1"></i><?php echo date('d M, Y', strtotime($q['created_at'])); ?></span>
                                                </div>
                                                <span class="badge <?php echo $q['status'] === 'Resolved' ? 'bg-success' : 'bg-warning text-dark'; ?> rounded-pill px-3 py-1">
                                                    <?php echo $q['status']; ?>
                                                </span>
                                            </div>
                                            <p class="text-dark fs-7 mb-0 fw-medium">
                                                <?php echo htmlspecialchars($q['query_message']); ?>
                                            </p>
                                            <?php if (!empty($q['resolved_at'])): ?>
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
    </section>
</div>

<script>
function updateWordCount(textarea) {
    var text = textarea.value.trim();
    var wordsArr = text === '' ? [] : text.split(/\s+/);
    var words = wordsArr.length;
    var display = document.getElementById('word-count-display');
    var errorDiv = document.getElementById('word-limit-error');

    // Hard-cap at 100 words: truncate extra words
    if (words > 100) {
        textarea.value = wordsArr.slice(0, 100).join(' ');
        words = 100;
        errorDiv.style.display = 'inline';
        textarea.style.borderColor = '#dc3545';
    } else {
        errorDiv.style.display = 'none';
        textarea.style.borderColor = '';
    }

    display.textContent = words + ' / 100 words';
}
// Init Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(function(el) { new bootstrap.Tooltip(el); });
});
</script>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
