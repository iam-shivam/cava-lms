<?php
// Webinar Card Component
// Expected: $webinar (array), $isRegistered (boolean), $userId (int|null)

$dateFormatted = date('d M, Y', strtotime($webinar['date']));
$timeFormatted = date('h:i A', strtotime($webinar['time']));
$webinarTimestamp = strtotime($webinar['date'] . ' ' . $webinar['time']);
$isPastWebinar = $webinarTimestamp < time();
?>
<div class="col-md-6 col-lg-6 mb-4">
    <div class="custom-card webinar-prism-card border-0 p-4 p-md-4 bg-white shadow-sm rounded-4 h-100 d-flex flex-column">
        <?php if (!empty($webinar['thumbnail'])): 
            $webinarImgUrl = (file_exists(BASE_PATH . '/uploads/' . $webinar['thumbnail'])) ? SITE_URL . '/uploads/' . $webinar['thumbnail'] : SITE_URL . '/assets/images/' . $webinar['thumbnail'];
        ?>
            <div class="mb-3 rounded-4 overflow-hidden border" style="height: 160px;">
                <img src="<?php echo $webinarImgUrl; ?>" alt="<?php echo htmlspecialchars($webinar['title']); ?>" class="w-100 h-100" style="object-fit: cover;" onerror="this.parentElement.style.display='none';">
            </div>
        <?php endif; ?>
        
        <div class="d-flex align-items-center justify-content-between mb-3">
            <?php if ($isPastWebinar): ?>
                <span class="badge bg-secondary-light text-secondary px-3 py-2 rounded-pill fw-semibold fs-8">
                    <i class="fa-solid fa-video-slash me-1"></i> Closed Webinar
                </span>
            <?php else: ?>
                <span class="badge bg-danger-light text-danger px-3 py-2 rounded-pill fw-semibold fs-8 d-inline-flex align-items-center gap-1">
                    <span class="spinner-grow spinner-grow-sm text-danger" role="status" style="width: 8px; height: 8px;"></span> Live Session
                </span>
            <?php endif; ?>
            <span class="text-muted fs-7"><i class="fa-regular fa-clock me-1 text-primary"></i><?php echo $timeFormatted; ?></span>
        </div>
        
        <h4 class="fw-bold mb-2 text-dark fs-5"><?php echo htmlspecialchars($webinar['title']); ?></h4>
        <p class="text-muted fs-7 mb-4"><?php echo htmlspecialchars($webinar['description'] ?? ''); ?></p>
        
        <div class="bg-light p-3 rounded-4 mb-4 d-flex align-items-center justify-content-between mt-auto">
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
            <?php if ($isPastWebinar): ?>
                <?php if ($isRegistered): ?>
                    <div class="alert alert-secondary m-0 py-2 px-3 border-0 d-inline-flex align-items-center gap-2 rounded-pill fs-7">
                        <i class="fa-solid fa-circle-check"></i> Registered (Past)
                    </div>
                <?php else: ?>
                    <span class="badge bg-secondary-light text-secondary px-3 py-2 rounded-pill fw-semibold fs-7">
                        Closed
                    </span>
                <?php endif; ?>
                <button class="btn btn-secondary btn-sm rounded-pill px-4" disabled>Closed</button>
            <?php elseif ($isRegistered): ?>
                <div class="alert alert-success m-0 py-2 px-3 border-0 d-inline-flex align-items-center gap-2 rounded-pill fs-7">
                    <i class="fa-solid fa-circle-check"></i> Registered
                </div>
                <?php if (!empty($webinar['join_url'])): ?>
                    <a href="<?php echo htmlspecialchars($webinar['join_url']); ?>" target="_blank" class="btn btn-success btn-sm rounded-pill px-4 fw-semibold"><i class="fa-solid fa-video me-1"></i>Join Webinar</a>
                <?php else: ?>
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-4" disabled>Join Link Emailed</button>
                <?php endif; ?>
            <?php else: ?>
                <span class="fw-bold text-primary fs-4">₹<?php echo number_format($webinar['price'], 2); ?></span>
                
                <?php if ($userId): ?>
                    <form action="payment_process.php" method="POST" class="m-0">
                        <input type="hidden" name="item_type" value="webinar">
                        <input type="hidden" name="item_id" value="<?php echo $webinar['id']; ?>">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Register Now</button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary rounded-pill px-4 fw-semibold">Login to Register</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
