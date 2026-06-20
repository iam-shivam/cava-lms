<?php
// Webinar Card Component
// Expected: $webinar (array), $isRegistered (boolean), $userId (int|null)

$dateFormatted = date('d M, Y', strtotime($webinar['date']));
$timeFormatted = date('h:i A', strtotime($webinar['time']));
?>
<div class="col-md-6 col-lg-6 mb-4">
    <div class="custom-card border-0 p-4 bg-white shadow-sm rounded-4 animate-fade-in-up">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill fw-semibold">
                <i class="fa-solid fa-video me-1"></i> Live Webinar
            </span>
            <span class="text-muted"><i class="fa-regular fa-clock me-1"></i><?php echo $timeFormatted; ?></span>
        </div>
        
        <h4 class="fw-bold mb-2 text-dark"><?php echo htmlspecialchars($webinar['title']); ?></h4>
        <p class="text-muted mb-4"><?php echo htmlspecialchars($webinar['description'] ?? ''); ?></p>
        
        <div class="bg-light p-3 rounded-3 mb-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-regular fa-calendar text-primary fs-5"></i>
                <div>
                    <span class="text-muted d-block fs-8">Date</span>
                    <span class="fw-bold text-dark fs-7"><?php echo $dateFormatted; ?></span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-ticket text-primary fs-5"></i>
                <div>
                    <span class="text-muted d-block fs-8">Price</span>
                    <span class="fw-bold text-dark fs-7">₹<?php echo number_format($webinar['price'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="d-flex align-items-center justify-content-between">
            <?php if ($isRegistered): ?>
                <div class="alert alert-success m-0 py-2 px-3 border-0 d-inline-flex align-items-center gap-2 rounded-pill fs-7">
                    <i class="fa-solid fa-circle-check"></i> Registered
                </div>
                <button class="btn btn-outline-primary btn-sm rounded-pill px-4" disabled>Join Link Emailed</button>
            <?php else: ?>
                <span class="fw-bold text-primary fs-4">₹<?php echo number_format($webinar['price'], 2); ?></span>
                
                <?php if ($userId): ?>
                    <form action="payment_process.php" method="POST" class="m-0">
                        <input type="hidden" name="item_type" value="webinar">
                        <input type="hidden" name="item_id" value="<?php echo $webinar['id']; ?>">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Register Now</button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary rounded-pill px-4">Login to Register</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
