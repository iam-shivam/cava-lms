<?php
// Privacy Policy Page
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pageTitle       = 'Privacy Policy';
$pageDescription = 'Privacy Policy of Career Abroad Visa Academy (CAVA LMS). Learn how we collect, use, and protect your personal information.';

require_once __DIR__ . '/views/layout/header.php'; 
?>

<div class="lp-body-scope">
    <!-- Page Hero Banner -->
    <section class="lp-page-hero">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
                </ol>
            </nav>
            <h1 class="lp-page-hero-title">Privacy Policy</h1>
            <p class="lp-page-hero-subtitle">How we collect, use, and protect your personal information.</p>
        </div>
    </section>

    <!-- Privacy Policy Content -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="custom-card border-0 shadow-sm p-4 p-md-5 bg-white rounded-4">
                        
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="lp-avatar-initials rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-1 text-dark">Privacy Policy</h3>
                                <p class="text-muted m-0 fs-7">Career Abroad Visa Academy</p>
                            </div>
                        </div>

                        <div class="privacy-content" style="line-height: 1.85; color: #444;">

                            <p class="fs-6">
                                Career Abroad Visa Academy respects your privacy and is committed to protecting your personal information. This Privacy Policy explains what we collect, why we collect it, how we use it, and the choices you have.
                            </p>

                            <h5 class="fw-bold text-dark mt-4 mb-3">
                                <i class="fa-solid fa-database text-primary me-2 fs-6"></i>Information We Collect
                            </h5>
                            <p class="fs-7">
                                We may collect your name, email address, phone number, location details, professional information, payment details processed via secure third parties, and usage data through cookies.
                            </p>

                            <h5 class="fw-bold text-dark mt-4 mb-3">
                                <i class="fa-solid fa-bullseye text-primary me-2 fs-6"></i>How We Use Your Information
                            </h5>
                            <p class="fs-7">
                                We use this information to deliver webinars and courses, process payments, send updates, improve our services, and communicate with you.
                            </p>

                            <h5 class="fw-bold text-dark mt-4 mb-3">
                                <i class="fa-solid fa-handshake text-primary me-2 fs-6"></i>Data Sharing
                            </h5>
                            <p class="fs-7">
                                We don't sell your personal information. We may share data only with trusted service providers, like payment gateways and email platforms, who support our operations.
                            </p>

                            <h5 class="fw-bold text-dark mt-4 mb-3">
                                <i class="fa-solid fa-lock text-primary me-2 fs-6"></i>Data Security
                            </h5>
                            <p class="fs-7">
                                We use reasonable safeguards to protect your data, but no online system is 100 percent secure.
                            </p>

                            <h5 class="fw-bold text-dark mt-4 mb-3">
                                <i class="fa-solid fa-user-check text-primary me-2 fs-6"></i>Your Rights
                            </h5>
                            <p class="fs-7">
                                You may request access, correction, or deletion of your personal data or opt out of marketing at any time by contacting us.
                            </p>

                            <h5 class="fw-bold text-dark mt-4 mb-3">
                                <i class="fa-solid fa-child text-primary me-2 fs-6"></i>Children's Privacy
                            </h5>
                            <p class="fs-7">
                                Our services are not intended for children, and we don't knowingly collect data from minors.
                            </p>

                            <h5 class="fw-bold text-dark mt-4 mb-3">
                                <i class="fa-solid fa-envelope text-primary me-2 fs-6"></i>Contact Us
                            </h5>
                            <p class="fs-7">
                                If you have any questions about this policy or want to exercise your data rights, contact Career Abroad Visa Academy at 
                                <a href="mailto:abroadcareerexpert9@gmail.com" class="text-primary fw-semibold text-decoration-none">abroadcareerexpert9@gmail.com</a>.
                            </p>

                            <div class="alert alert-light border rounded-4 mt-4 fs-7" style="background: #f8f6ff;">
                                <i class="fa-solid fa-circle-info text-primary me-2"></i>
                                This policy may be updated periodically, and continued use of our website means you accept the updated terms.
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
