<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

// Include the header
require_once __DIR__ . '/views/layout/header.php';
?>

<div class="lp-body-scope">
    <section class="py-5 min-vh-75 d-flex align-items-center bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="custom-card border-0 shadow-sm p-5 bg-white rounded-4 text-center">
                        <div class="mb-4">
                            <img src="<?php echo SITE_URL; ?>/assets/images/plug_404_illustration.png" alt="404 Illustration" class="img-fluid" style="max-height: 220px; object-fit: contain;">
                        </div>
                        
                        <h2 class="fw-bold text-dark mb-2">Page Not Found</h2>
                        <p class="text-muted mb-4" style="max-width: 420px; margin: 0 auto;">The page you are looking for doesn't exist, has been removed, or is temporarily unavailable.</p>
                        
                        <div class="d-flex justify-content-center">
                            <a href="<?php echo SITE_URL; ?>/index.php" class="lp-btn-primary px-5 text-center" style="border-radius: 100px;">
                                <i class="fa-solid fa-house me-2"></i>Back to Homepage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
// Include the footer
require_once __DIR__ . '/views/layout/footer.php';
?>
