<?php
// Layout Footer Component
?>
    <!-- Hero-Matched Premium Footer -->
    <footer class="lp-footer pt-5 pb-4 mt-0">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <!-- Brand Identity Column -->
                <div class="col-lg-4 col-md-6 mb-3 mb-md-0">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="lp-footer-brand text-decoration-none mb-3 d-inline-flex align-items-center gap-2">
                        <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpeg" alt="CAVA LMS Logo" class="lp-footer-logo" style="height: 48px; width: 48px; object-fit: contain; border-radius: 50%; box-shadow: 0 4px 14px rgba(0,0,0,0.35); border: 2px solid rgba(255,255,255,0.25);">
                        <span class="fw-bold text-white fs-4">CAVA LMS</span>
                    </a>
                    <p class="text-white-50 fs-7" style="max-width: 320px; line-height: 1.65;">
                        A modern and premium learning management system offering expert-led courses, webinars, and masterclasses to help you level up your career.
                    </p>
                </div>

                <!-- Navigation Quick Links -->
                <div class="col-lg-3 col-md-6 mb-3 mb-md-0">
                    <h6 class="lp-footer-heading">Quick Links</h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li><a href="<?php echo SITE_URL; ?>/index.php" class="lp-footer-link"><i class="fa-solid fa-chevron-right fs-8 me-2 opacity-50"></i>Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/courses.php" class="lp-footer-link"><i class="fa-solid fa-chevron-right fs-8 me-2 opacity-50"></i>Courses</a></li>
                        <?php if (false): ?>
                        <li><a href="<?php echo SITE_URL; ?>/webinars.php" class="lp-footer-link"><i class="fa-solid fa-chevron-right fs-8 me-2 opacity-50"></i>Webinars</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo SITE_URL; ?>/events.php" class="lp-footer-link"><i class="fa-solid fa-chevron-right fs-8 me-2 opacity-50"></i>Events</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/support.php" class="lp-footer-link"><i class="fa-solid fa-chevron-right fs-8 me-2 opacity-50"></i>Support Center</a></li>
                    </ul>
                </div>

                <!-- Contact & Location Info -->
                <div class="col-lg-4 col-md-12">
                    <h6 class="lp-footer-heading">Contact & Support</h6>
                    <div class="d-flex flex-column gap-2 text-white-50 fs-7">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-home text-primary me-1"></i> Bengaluru, India
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-envelope text-primary me-1"></i> support@cavalms.com
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Divider -->
            <div class="lp-footer-divider"></div>

            <div class="row align-items-center">
                <div class="col-md-7 text-center text-md-start mb-3 mb-md-0">
                    <p class="text-white-50 fs-7 mb-0">Copyright © 2026 All rights reserved by 
                        <strong class="text-white">CAVA LMS Portal</strong>.
                        <span class="mx-1">|</span>
                        <a href="<?php echo SITE_URL; ?>/privacy-policy.php" class="text-white-50 text-decoration-none" style="transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color=''">Privacy Policy</a>
                    </p>
                </div>
                <div class="col-md-5 text-center text-md-end">
                    <div class="d-inline-flex gap-2">
                        <a href="https://www.facebook.com/careerabroadacademy" target="_blank" rel="noopener noreferrer" class="lp-footer-social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://x.com/visa_academy" target="_blank" rel="noopener noreferrer" class="lp-footer-social-btn"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 300 300" fill="currentColor" style="vertical-align: middle;"><path d="M178.57 127.15 290.27 0h-26.46l-96.97 110.38L89.34 0H0l117.13 166.93L0 300h26.46l102.4-116.59L209.66 300H299L178.57 127.15ZM140.55 170.08l-11.87-16.63L37.38 19.5h40.66l76.2 106.73 11.87 16.63 99.03 138.68h-40.66l-84.93-118.46Z"/></svg></a>
                        <a href="https://www.linkedin.com/company/career-abroad-visa-academy/" target="_blank" rel="noopener noreferrer" class="lp-footer-social-btn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://www.instagram.com/careerabroadexpert/" target="_blank" rel="noopener noreferrer" class="lp-footer-social-btn"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>

    <!-- GSAP and ScrollTrigger CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <!-- Premium Interactive Page Animations Script -->
    <script src="<?php echo SITE_URL; ?>/assets/js/landing-premium.js"></script>
</body>
</html>
