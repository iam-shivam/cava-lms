<?php
// Payment Processing Gateway Handler
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/Webinar.php';
require_once __DIR__ . '/controllers/PaymentController.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    set_flash_message('warning', 'Please login to make purchases.');
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$itemType = trim($_POST['item_type'] ?? '');
$itemId = intval($_POST['item_id'] ?? 0);
$amountToPay = isset($_POST['amount_to_pay']) ? floatval($_POST['amount_to_pay']) : null;

if (empty($itemType) || $itemId <= 0) {
    set_flash_message('danger', 'Invalid purchase request.');
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

try {
    // Initiate payment order via Razorpay
    $order = PaymentController::initiatePayment($userId, $itemType, $itemId, $amountToPay);
} catch (Exception $e) {
    set_flash_message('danger', 'Payment Initialization Failed: ' . $e->getMessage());
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

// Render secure Checkout overlay
require_once __DIR__ . '/views/layout/header.php';
?>

<div class="container my-5 py-5 text-center">
    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <h3 class="fw-bold">Connecting to Secure Payment Gateway...</h3>
    <p class="text-muted">Please do not close or refresh this page. You will be redirected shortly.</p>
    
    <div class="col-md-6 mx-auto mt-4">
        <div class="card shadow-sm border-0 p-4 bg-light rounded-4">
            <h5 class="fw-bold mb-2">Order Summary</h5>
            <div class="d-flex justify-content-between border-bottom py-2">
                <span>Item Name:</span>
                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($order['title']); ?></span>
            </div>
            <div class="d-flex justify-content-between py-2">
                <span>Amount Due:</span>
                <span class="fw-bold text-primary">₹<?php echo number_format($order['price'], 2); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay JavaScript Checkout library -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<div id="razorpay-data" 
     data-key="<?php echo RAZORPAY_KEY_ID; ?>" 
     data-amount="<?php echo $order['amount']; ?>" 
     data-title="<?php echo htmlspecialchars($order['title']); ?>" 
     data-orderid="<?php echo $order['order_id']; ?>" 
     data-name="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" 
     data-email="<?php echo htmlspecialchars($_SESSION['user_email']); ?>">
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
