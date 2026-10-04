    </div><!-- /#studio-main-content -->

    <footer class="studio-footer lp-footer" aria-labelledby="studio-footer-title">
        <div class="container">
            <div class="studio-footer__top">
                <div class="studio-footer__brand-block">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="lp-footer-brand studio-footer__brand-link">
                        <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpeg" alt="" class="lp-footer-logo" width="52" height="52">
                        <span id="studio-footer-title">CAVA Studio</span>
                    </a>
                    <span class="studio-footer__eyebrow">CAVA Learning Studio</span>
                    <p class="studio-footer__description">
                        Structured learning for students and professionals exploring international education, career, and visa pathways.
                    </p>
                </div>

                <nav class="studio-footer__navigation" aria-label="Footer navigation">
                    <div class="studio-footer__group">
                        <h2 class="lp-footer-heading">Explore</h2>
                        <ul class="studio-footer__links" role="list">
                            <li><a href="<?php echo SITE_URL; ?>/courses.php" class="lp-footer-link">Courses</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/webinars.php" class="lp-footer-link">Webinars</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/events.php" class="lp-footer-link">Events</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/index.php#mentor" class="lp-footer-link">Our mentor</a></li>
                        </ul>
                    </div>

                    <div class="studio-footer__group">
                        <h2 class="lp-footer-heading">Account</h2>
                        <ul class="studio-footer__links" role="list">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <li><a href="<?php echo SITE_URL; ?>/dashboard.php" class="lp-footer-link">Dashboard</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/logout.php" class="lp-footer-link">Logout</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo SITE_URL; ?>/login.php" class="lp-footer-link">Student login</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/register.php" class="lp-footer-link">Create account</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="studio-footer__group">
                        <h2 class="lp-footer-heading">Help</h2>
                        <ul class="studio-footer__links" role="list">
                            <li><a href="<?php echo SITE_URL; ?>/support.php" class="lp-footer-link">Support</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/privacy-policy.php" class="lp-footer-link">Privacy</a></li>
                        </ul>
                    </div>
                </nav>
            </div>

            <div class="studio-footer__bottom">
                <p class="studio-footer__copyright">&copy; <?php echo date('Y'); ?> CAVA Studio. All rights reserved.</p>
                <nav class="studio-footer__social" aria-label="CAVA social media">
                    <a href="https://www.facebook.com/careerabroadacademy" target="_blank" rel="noopener noreferrer" class="lp-footer-social-btn" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                    <a href="https://x.com/visa_academy" target="_blank" rel="noopener noreferrer" class="lp-footer-social-btn" aria-label="X">
                        <svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true" focusable="false"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.657l-5.214-6.817-5.966 6.817H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77Z"/></svg>
                    </a>
                    <a href="https://www.linkedin.com/company/career-abroad-visa-academy/" target="_blank" rel="noopener noreferrer" class="lp-footer-social-btn" aria-label="LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
                    <a href="https://www.instagram.com/careerabroadexpert/" target="_blank" rel="noopener noreferrer" class="lp-footer-social-btn" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                </nav>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js?v=2.0" defer></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/cava-studio.js?v=2.0" defer></script>

    <?php if (($currentPage ?? '') === 'index.php'): ?>
        <!-- Motion is progressive enhancement: the page remains complete without these CDN files. -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" defer></script>
        <script src="<?php echo SITE_URL; ?>/assets/js/cava-studio-home.js?v=2.0" defer></script>
    <?php endif; ?>

    <?php if (isset($_SESSION['pending_webhook_payload']) && defined('GOOGLE_SHEETS_WEBHOOK') && GOOGLE_SHEETS_WEBHOOK): ?>
    <script>
        (function () {
            const url = <?php echo json_encode(GOOGLE_SHEETS_WEBHOOK); ?>;
            const payload = <?php echo $_SESSION['pending_webhook_payload']; ?>;
            if (url && url.indexOf('YOUR_SCRIPT_ID') === -1) {
                fetch(url, {
                    method: 'POST',
                    mode: 'no-cors',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                }).catch(function (error) {
                    console.warn('Google Sheets webhook failed:', error);
                });
            }
        })();
    </script>
    <?php
    unset($_SESSION['pending_webhook_payload']);
    endif;
    ?>
</body>
</html>
