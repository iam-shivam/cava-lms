<?php
// Course Card Component
// Expected variable: $course (array), $isEnrolled (boolean)

$thumbnailUrl = SITE_URL . '/assets/images/default_course.jpg';
if (!empty($course['thumbnail'])) {
    if (file_exists(BASE_PATH . '/uploads/' . $course['thumbnail'])) {
        $thumbnailUrl = SITE_URL . '/uploads/' . $course['thumbnail'];
    } else {
        // Fallback to demo images or direct
        $thumbnailUrl = SITE_URL . '/assets/images/' . $course['thumbnail'];
    }
}

$delayClass = '';
if (isset($cardIndex)) {
    $delayMod = $cardIndex % 3;
    if ($delayMod === 1) {
        $delayClass = ' lms-delay-1';
    } elseif ($delayMod === 2) {
        $delayClass = ' lms-delay-2';
    }
}
?>
<div class="col-md-6 col-lg-4 mb-4 lms-reveal-card<?php echo $delayClass; ?>">
    <div class="custom-card">
        <div class="card-img-wrapper">
            <img src="<?php echo $thumbnailUrl; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" loading="lazy" onerror="this.src='https://placehold.co/600x340/6f42c1/ffffff?text=Course+Thumbnail'">
            <span class="card-badge"><?php echo htmlspecialchars($course['category_name'] ?? 'Course'); ?></span>
            
            <div class="card-status-badge">
                <?php if ($isEnrolled): ?>
                    <i class="fa-solid fa-lock-open text-success" title="Unlocked"></i>
                <?php else: ?>
                    <i class="fa-solid fa-lock text-warning" title="Locked"></i>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card-content">
            <h4 class="card-title">
                <a href="<?php echo SITE_URL; ?>/course.php?slug=<?php echo $course['slug']; ?>">
                    <?php echo htmlspecialchars($course['title']); ?>
                </a>
            </h4>
            <p class="card-text text-muted mb-4 fs-7" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 3rem;">
                <?php echo htmlspecialchars(strip_tags($course['description'] ?? '')); ?>
            </p>
            
            <?php 
            $lessonsCount = isset($course['lessons_count']) ? intval($course['lessons_count']) : Course::countLessons($course['id']);
            $hasDuration = isset($course['course_duration']) && intval($course['course_duration']) > 0;
            ?>
            <div class="card-meta">
                <span><i class="fa-regular fa-file-video"></i> <?php echo $lessonsCount; ?> Lessons</span>
                <?php if ($hasDuration): ?>
                    <span><i class="fa-regular fa-clock"></i> <?php echo intval($course['course_duration']); ?> Hours</span>
                <?php endif; ?>
                <span><i class="fa-solid fa-infinity"></i> Lifetime Access</span>
            </div>
            
            <div class="card-price-row">
                <div>
                    <?php if ($isEnrolled): ?>
                        <span class="badge bg-success-light text-success px-3 py-2 rounded-pill fw-semibold">
                            <i class="fa-solid fa-circle-check me-1"></i>Enrolled
                        </span>
                    <?php else: ?>
                        <span class="card-price text-primary">₹<?php echo number_format($course['price'], 2); ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($isEnrolled): ?>
                        <a href="<?php echo SITE_URL; ?>/course_play.php?slug=<?php echo $course['slug']; ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="fa-solid fa-play me-1"></i>Start Learning
                        </a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/course.php?slug=<?php echo $course['slug']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            View Details
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
