<?php
// Course Card Component
// Expected variables: $course (array), $isEnrolled (boolean), optional $cardIndex.

$thumbnailUrl = SITE_URL . '/assets/images/default_course.jpg';
if (!empty($course['thumbnail'])) {
    if (file_exists(BASE_PATH . '/uploads/' . $course['thumbnail'])) {
        $thumbnailUrl = SITE_URL . '/uploads/' . $course['thumbnail'];
    } else {
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

$lessonsCount = isset($course['lessons_count'])
    ? intval($course['lessons_count'])
    : Course::countLessons($course['id']);
$courseUrl = SITE_URL . '/course.php?slug=' . urlencode($course['slug']);
$playerUrl = SITE_URL . '/course_play.php?slug=' . urlencode($course['slug']);
?>
<div class="col-md-6 col-lg-4 mb-4 lms-reveal-card<?php echo $delayClass; ?>" data-studio-reveal>
    <article class="studio-course-card">
        <div class="studio-course-card__media">
            <img src="<?php echo htmlspecialchars($thumbnailUrl); ?>"
                 alt="<?php echo htmlspecialchars($course['title']); ?>"
                 loading="lazy"
                 onerror="this.onerror=null;this.src='<?php echo SITE_URL; ?>/assets/images/study_abroad.jpeg';">
            <span class="studio-course-card__category">
                <?php echo htmlspecialchars($course['category_name'] ?? 'Course'); ?>
            </span>
            <span class="studio-course-card__state" title="<?php echo $isEnrolled ? 'Enrolled' : 'Course details'; ?>">
                <i class="fa-solid <?php echo $isEnrolled ? 'fa-circle-check' : 'fa-book-open'; ?>" aria-hidden="true"></i>
                <?php echo $isEnrolled ? 'Enrolled' : 'Course'; ?>
            </span>
        </div>

        <div class="studio-course-card__body">
            <h3 class="studio-course-card__title">
                <a href="<?php echo htmlspecialchars($courseUrl); ?>">
                    <?php echo htmlspecialchars($course['title']); ?>
                </a>
            </h3>
            <p class="studio-course-card__copy">
                <?php echo htmlspecialchars(strip_tags($course['description'] ?? '')); ?>
            </p>

            <div class="studio-course-card__meta">
                <span><i class="fa-regular fa-circle-play" aria-hidden="true"></i><?php echo $lessonsCount; ?> lesson<?php echo $lessonsCount === 1 ? '' : 's'; ?></span>
                <span><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>Online</span>
            </div>

            <div class="studio-course-card__footer">
                <?php if ($isEnrolled): ?>
                    <span class="studio-course-card__enrolled">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Ready to continue
                    </span>
                    <a href="<?php echo htmlspecialchars($playerUrl); ?>" class="btn btn-primary btn-sm studio-course-card__action">
                        Continue <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <span class="studio-course-card__price">&#8377;<?php echo number_format((float) $course['price'], 2); ?></span>
                    <a href="<?php echo htmlspecialchars($courseUrl); ?>" class="btn btn-outline-primary btn-sm studio-course-card__action">
                        View course <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </article>
</div>
