<?php
// Payment Processing Gateway Handler
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/Webinar.php';
require_once __DIR__ . '/controllers/PaymentController.php';

if (!isset($_SESSION['user_id'])) {
    set_flash_message('warning', 'Please login to make purchases.');
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$itemType = trim($_POST['item_type'] ?? '');
$itemId = trim($_POST['item_id'] ?? '');
$amountToPay = isset($_POST['amount_to_pay']) ? floatval($_POST['amount_to_pay']) : null;

if (empty($itemType) || empty($itemId)) {
    set_flash_message('danger', 'Invalid purchase request.');
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

try {
    $order = PaymentController::initiatePayment($userId, $itemType, $itemId, $amountToPay);
} catch (Exception $e) {
    set_flash_message('danger', 'Payment Initialization Failed: ' . $e->getMessage());
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$pageTitle = 'Secure checkout';
require_once __DIR__ . '/views/layout/header.php';
?>

<main class="studio-payment-shell" aria-labelledby="payment-status-title">
    <section class="studio-payment-card text-center">
        <div class="studio-payment-mark" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></div>
        <div class="studio-payment-pulse" aria-hidden="true"></div>
        <p class="studio-kicker justify-content-center mb-2">Secure checkout</p>
        <h1 class="h3 mb-3" id="payment-status-title">Connecting to the payment gateway</h1>
        <p class="text-muted mb-0">Keep this page open while the secure checkout window is prepared.</p>

        <div class="studio-order-summary" aria-label="Order summary">
            <h2 class="h6 mb-2">Order summary</h2>
            <div class="studio-order-summary__row">
                <span class="text-muted">Item</span>
                <strong class="text-end"><?php echo htmlspecialchars($order['title']); ?></strong>
            </div>
            <div class="studio-order-summary__row">
                <span class="text-muted">Amount due</span>
                <strong class="text-primary">&#8377;<?php echo number_format($order['price'], 2); ?></strong>
            </div>
        </div>
        <p class="small text-muted mt-3 mb-0"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Payment details are entered in Razorpay's secure checkout.</p>
    </section>
</main>

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
