<?php
// Dedicated Support & Queries Page
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Query.php';

$userId = $_SESSION['user_id'] ?? null;
$myQueries = $userId ? Query::getByUser($userId) : [];

$pageTitle       = 'Support Center';
$pageDescription = 'Get help with courses, account access, payments, or using the CAVA LMS platform.';

require_once __DIR__ . '/views/layout/header.php'; 
?>

<main class="lp-body-scope page-support studio-public-main studio-support-page">
    <!-- Page Hero Banner -->
    <section class="lp-page-hero studio-detail-hero studio-support-hero">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Support</li>
                </ol>
            </nav>
            <h1 class="lp-page-hero-title studio-detail-hero__title">How Can We Help?</h1>
            <p class="lp-page-hero-subtitle studio-support-intro">Get help with courses, account access, payments, or using the learning platform.</p>
        </div>
    </section>

    <!-- Main Container -->
    <section class="studio-support-section">
        <div class="container">
            <div class="row g-4 studio-support-grid">
                <!-- Left Column: Submit Query Form -->
                <div class="col-lg-6 lms-reveal lms-reveal-left studio-support-grid__form">
                    <div class="studio-support-card studio-support-card--form">
                        <div class="studio-support-card__header">
                            <div class="studio-support-card__icon" aria-hidden="true">
                                <i class="fa-solid fa-paper-plane"></i>
                            </div>
                            <div>
                                <h2 class="studio-support-card__title mb-1" id="support-form-title">Submit a support request</h2>
                                <p class="text-muted m-0">Share the details the support team needs to review your request.</p>
                            </div>
                        </div>
                        
                        <form action="submit_query.php" method="POST" id="queryFormSupport" class="studio-support-form" onsubmit="return validateWordLimit('queryFormSupport')">
                            <div class="mb-3 studio-support-form__field">
                                <label for="query_name" class="lp-form-label">Full Name</label>
                                <input type="text" class="form-control lp-form-control" id="query_name" name="name" 
                                       value="<?php echo $userId ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" placeholder="Your full name" required>
                            </div>
                            
                            <div class="mb-3 studio-support-form__field">
                                <label for="query_email" class="lp-form-label">Email Address</label>
                                <input type="email" class="form-control lp-form-control" id="query_email" name="email" 
                                       value="<?php echo $userId ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" placeholder="name@example.com" required>
                            </div>
                            
                            <div class="mb-3 studio-support-form__field">
                                <label for="query_mobile" class="lp-form-label">Mobile Number</label>
                                <input type="tel" class="form-control lp-form-control" id="query_mobile" name="mobile_number" placeholder="Your mobile number" required>
                            </div>
                            
                            <div class="mb-4 studio-support-form__field">
                                <label for="query_message" class="lp-form-label">Query Message Details</label>
                                <textarea class="form-control lp-form-control" id="query_message" name="query_message" rows="5" placeholder="Describe the issue or question you need help with." required oninput="updateWordCount(this)"></textarea>
                                <div class="d-flex justify-content-end align-items-center mt-1 gap-2 studio-support-form__counter">
                                    <span id="word-limit-error" class="text-danger small fw-semibold" style="display:none; margin-right:auto;">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>Word limit reached!
                                    </span>
                                    <small id="word-count-display" class="fw-semibold text-muted">0 / 100 words</small>
                                    <i class="fa-solid fa-circle-info text-muted" style="cursor:help;" data-bs-toggle="tooltip" data-bs-placement="top" title="Maximum 100 words allowed."></i>
                                </div>
                            </div>
                            
                            <button type="submit" id="querySubmitBtnSupport" class="lp-btn-primary w-100 py-3 studio-support-form__submit">
                                Submit support request <i class="fa-solid fa-paper-plane ms-2"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Support Tickets / Query History (Logged-In User) -->
                <div class="col-lg-6 lms-reveal lms-reveal-right lms-delay-1 studio-support-grid__history">
                    <?php if (!$userId): ?>
                        <div class="studio-support-card studio-support-card--guest text-center d-flex flex-column justify-content-center align-items-center">
                            <div class="studio-support-card__icon mb-3" aria-hidden="true">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <h2 class="studio-support-card__title mb-2">Track your requests</h2>
                            <p class="text-muted mb-4" style="max-width: 340px;">Log in to view your submission history and resolution status.</p>
                            <a href="login.php" class="lp-btn-pill-dark">
                                Log in to view requests <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="studio-support-card studio-support-card--history">
                            <div class="studio-support-card__header">
                                <div class="studio-support-card__icon" aria-hidden="true"><i class="fa-solid fa-ticket"></i></div>
                                <div>
                                    <h2 class="studio-support-card__title mb-1">My support requests</h2>
                                    <p class="text-muted m-0">Review the status of requests submitted from this account.</p>
                                </div>
                            </div>
                            
                            <?php if (empty($myQueries)): ?>
                                <div class="lp-empty-state py-4">
                                    <div class="lp-empty-icon mb-3" style="width:60px; height:60px; font-size:1.4rem;">
                                        <i class="fa-regular fa-comment-dots"></i>
                                    </div>
                                    <h3 class="h5 fw-bold text-dark mb-1">No requests yet</h3>
                                    <p class="text-muted fs-7">Use the form to submit your first support request.</p>
                                </div>
                            <?php else: ?>
                                <div class="studio-ticket-list">
                                    <?php foreach ($myQueries as $q): ?>
                                        <article class="studio-ticket-card">
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
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

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
    display.style.color = words >= 100 ? '#dc3545' : '#198754';

    // A message containing exactly 100 words is valid.
    var submitBtn = document.getElementById('querySubmitBtnSupport');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }
}

// Form-level validation before submit
function validateWordLimit(formId) {
    var form = document.getElementById(formId);
    var textarea = form.querySelector('textarea[name="query_message"]');
    var text = textarea.value.trim();
    var words = text === '' ? 0 : text.split(/\s+/).length;
    if (words > 100) {
        alert('Your query message must not exceed 100 words. Currently: ' + words + ' words.');
        textarea.focus();
        return false;
    }
    return true;
}

// Init Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    if (!window.bootstrap || !window.bootstrap.Tooltip) {
        return;
    }
    var tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(function(el) { new bootstrap.Tooltip(el); });
});
</script>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
