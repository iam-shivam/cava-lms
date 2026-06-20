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

// Check if image exists, otherwise write placeholder placeholder later or use custom inline svg
?>
<div class="col-md-6 col-lg-4 mb-4">
    <div class="custom-card">
        <div class="card-img-wrapper">
            <img src="<?php echo $thumbnailUrl; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" onerror="this.src='https://placehold.co/600x340/6f42c1/ffffff?text=Course+Thumbnail'">
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
