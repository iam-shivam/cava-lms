<?php
// Dedicated Webinars Page with Filters
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Webinar.php';

// Filter Inputs
$search = trim($_GET['search'] ?? '');
$sortBy = trim($_GET['sort'] ?? 'soonest'); // soonest, latest

// Base query
$sql = "SELECT * FROM webinars WHERE status = 'Active'";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($sortBy === 'latest') {
    $sql .= " ORDER BY date DESC, time DESC";
} else {
    // Default to soonest first
    $sql .= " ORDER BY date ASC, time ASC";
}

try {
    $webinarsList = DB::fetchAll($sql, $params);
} catch (Exception $e) {
    $webinarsList = [];
}

$userId = $_SESSION['user_id'] ?? null;
require_once __DIR__ . '/views/layout/header.php';
?>

<!-- Header Banner -->
<div class="bg-light py-5 mb-5 border-bottom">
    <div class="container text-center">
        <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill fw-semibold mb-2">Live Masterclasses</span>
        <h1 class="fw-extrabold display-5 text-dark">Live Interactive Webinars</h1>
        <p class="text-muted col-md-6 mx-auto">Join live interactive sessions with certified consultants and visa advisors to resolve your queries instantly.</p>
    </div>
</div>

<div class="container mb-5">
    <!-- Filter Panel -->
    <div class="card border-0 shadow-sm p-4 bg-white rounded-4 mb-5">
        <form action="webinars.php" method="GET" class="row g-3">
            <div class="col-md-5">
                <label for="search" class="form-label fw-semibold fs-7 text-muted">Search Webinars</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" class="form-control bg-light border-start-0 ps-0" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" placeholder="e.g. CRS score, IELTS mock...">
                </div>
            </div>
            
            <div class="col-md-4">
                <label for="sort" class="form-label fw-semibold fs-7 text-muted">Sort by Date</label>
                <select class="form-select bg-light border" id="sort" name="sort">
                    <option value="soonest" <?php echo $sortBy === 'soonest' ? 'selected' : ''; ?>>Soonest / Upcoming First</option>
                    <option value="latest" <?php echo $sortBy === 'latest' ? 'selected' : ''; ?>>Latest Scheduled</option>
                </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill">
                    <i class="fa-solid fa-filter me-1"></i> Apply Filters
                </button>
                <?php if (!empty($search) || $sortBy !== 'soonest'): ?>
                    <a href="webinars.php" class="btn btn-outline-secondary w-50 py-2 rounded-pill text-center">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Webinars Grid -->
    <div class="row justify-content-center">
        <?php if (empty($webinarsList)): ?>
            <div class="col text-center py-5">
                <i class="fa-solid fa-video-slash fs-1 text-muted mb-3 d-block"></i>
                <h4 class="fw-bold text-dark">No Webinars Found</h4>
                <p class="text-muted">No live webinars match your current filter criteria.</p>
                <a href="webinars.php" class="btn btn-primary rounded-pill px-4 mt-2">Clear Filters</a>
            </div>
        <?php else: ?>
            <?php foreach ($webinarsList as $webinar): 
                $isRegistered = Webinar::isUserRegistered($userId, $webinar['id']);
                require __DIR__ . '/views/components/webinar_card.php';
            endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
