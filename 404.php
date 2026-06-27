<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

// Include the header
require_once __DIR__ . '/views/layout/header.php';
?>

<div class="container my-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="custom-card border-0 shadow-lg p-5 bg-white rounded-4 animate-fade-in-up d-flex flex-column align-items-center justify-content-center">
                <div class="mb-4">
                    <img src="<?php echo SITE_URL; ?>/assets/images/plug_404_illustration.png" alt="404 Illustration" class="img-fluid" style="max-height: 250px; object-fit: contain;">
                </div>
                
                <h2 class="fw-bold text-dark mb-4">Sorry, Page Not Found</h2>
                
                <div class="d-flex justify-content-center">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-primary btn-lg px-5 rounded-pill fw-bold" style="padding: 12px 36px; border-radius: 50px;">
                        Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
require_once __DIR__ . '/views/layout/footer.php';
?>
