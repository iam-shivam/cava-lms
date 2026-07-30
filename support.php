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
                                <textarea class="form-control lp-form-control" id="query_message" name="query_message" rows="4" placeholder="State your PR eligibility, CRS scores, or purchase queries..." required></textarea>
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
                                                <span class="text-muted fs-8"><i class="fa-regular fa-clock me-1"></i><?php echo date('d M, Y', strtotime($q['created_at'])); ?></span>
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

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
