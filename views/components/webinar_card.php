<?php
// Webinar Card Component
// Expected: $webinar (array), $isRegistered (boolean), $userId (string|null).
// Optional: $webinarHeadingLevel (2 or 3; defaults to 2 for the catalog page).

$webinarHeadingLevel = isset($webinarHeadingLevel) && (int) $webinarHeadingLevel === 3 ? 3 : 2;
$webinarHeadingTag = 'h' . $webinarHeadingLevel;

$dateFormatted = date('d M Y', strtotime($webinar['date']));
$timeFormatted = date('h:i A', strtotime($webinar['time']));
$webinarTimestamp = strtotime($webinar['date'] . ' ' . $webinar['time']);
$isPastWebinar = $webinarTimestamp < time();
$webinarImgUrl = '';
if (!empty($webinar['thumbnail'])) {
    $webinarImgUrl = file_exists(BASE_PATH . '/uploads/' . $webinar['thumbnail'])
        ? SITE_URL . '/uploads/' . $webinar['thumbnail']
        : SITE_URL . '/assets/images/' . $webinar['thumbnail'];
}
?>
<div class="col-md-6 col-lg-6 mb-4" data-studio-reveal>
    <article class="studio-webinar-card<?php echo $webinarImgUrl ? '' : ' studio-webinar-card--no-media'; ?>">
        <?php if ($webinarImgUrl): ?>
            <div class="studio-webinar-card__media">
                <img src="<?php echo htmlspecialchars($webinarImgUrl); ?>"
                     alt=""
                     loading="lazy"
                     onerror="this.parentElement.hidden=true;">
            </div>
        <?php endif; ?>

        <div class="studio-webinar-card__body">
            <div class="studio-webinar-card__topline">
                <span class="studio-webinar-card__status <?php echo $isPastWebinar ? 'is-closed' : 'is-upcoming'; ?>">
                    <i class="fa-solid <?php echo $isPastWebinar ? 'fa-circle' : 'fa-signal'; ?>" aria-hidden="true"></i>
                    <?php echo $isPastWebinar ? 'Closed' : 'Upcoming'; ?>
                </span>
                <span class="studio-webinar-card__time"><i class="fa-regular fa-clock" aria-hidden="true"></i><?php echo htmlspecialchars($timeFormatted); ?></span>
            </div>

            <<?php echo $webinarHeadingTag; ?> class="studio-webinar-card__title"><?php echo htmlspecialchars($webinar['title']); ?></<?php echo $webinarHeadingTag; ?>>
            <?php if (!empty($webinar['description'])): ?>
                <p class="studio-webinar-card__copy"><?php echo htmlspecialchars($webinar['description']); ?></p>
            <?php endif; ?>

            <dl class="studio-webinar-card__details">
                <div><dt>Date</dt><dd><?php echo htmlspecialchars($dateFormatted); ?></dd></div>
                <div><dt>Price</dt><dd>&#8377;<?php echo number_format((float) $webinar['price'], 2); ?></dd></div>
            </dl>

            <div class="studio-webinar-card__footer">
                <?php if ($isPastWebinar): ?>
                    <span class="studio-webinar-card__note"><?php echo $isRegistered ? 'Previously registered' : 'Registration closed'; ?></span>
                    <button class="btn btn-secondary btn-sm" type="button" disabled>Closed</button>
                <?php elseif ($isRegistered): ?>
                    <span class="studio-webinar-card__note is-success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Registered</span>
                    <?php if (!empty($webinar['join_url'])): ?>
                        <a href="<?php echo htmlspecialchars($webinar['join_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm"><i class="fa-solid fa-video" aria-hidden="true"></i>Join webinar</a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-sm" type="button" disabled>Link unavailable</button>
                    <?php endif; ?>
                <?php elseif ($userId): ?>
                    <span class="studio-webinar-card__price">&#8377;<?php echo number_format((float) $webinar['price'], 2); ?></span>
                    <form action="payment_process.php" method="POST" class="m-0">
                        <input type="hidden" name="item_type" value="webinar">
                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($webinar['id']); ?>">
                        <button type="submit" class="btn btn-primary btn-sm">Register <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                    </form>
                <?php else: ?>
                    <span class="studio-webinar-card__price">&#8377;<?php echo number_format((float) $webinar['price'], 2); ?></span>
                    <a href="login.php" class="btn btn-primary btn-sm">Login to register</a>
                <?php endif; ?>
            </div>
        </div>
    </article>
</div>
