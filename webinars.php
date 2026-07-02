<?php
// Dedicated Webinars Page with Filters
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Webinar.php';

// Filter Inputs
$search = trim($_GET['search'] ?? '');
$sortBy = trim($_GET['sort'] ?? 'soonest'); // soonest, latest

// Base query
$sql = "SELECT * FROM webinars WHERE status = 'Active' AND (date > CURRENT_DATE() OR (date = CURRENT_DATE() AND time >= CURRENT_TIME()))";
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

$pageTitle       = 'Live Webinars';
$pageDescription = 'Join live interactive webinars with certified consultants on CAVA LMS. Learn from experts, ask questions, and get your immigration queries resolved.';

require_once __DIR__ . '/views/layout/header.php';
?>

<!-- Main Container -->
<div class="container mb-5">
    <!-- Header Row (Title & Sleek Search) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 mt-5">
        <h1 class="fw-extrabold text-dark m-0">Webinars</h1>
        
        <!-- Sleek Search Input -->
        <form action="webinars.php" method="GET" class="m-0" style="width: 100%; max-width: 320px;">
            <?php if ($sortBy !== 'soonest'): ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>">
            <?php endif; ?>
            <div class="input-group search-input-group align-items-center pe-3 bg-white">
                <span class="input-group-text bg-white border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" class="form-control border-0 ps-0" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Webinars..." autocomplete="off">
                <i class="fa-solid fa-xmark text-muted" id="search-clear" style="cursor: pointer; display: <?php echo !empty($search) ? 'block' : 'none'; ?>;"></i>
            </div>
        </form>
    </div>

    <!-- Filter Row (Horizontal Tabs & Sorting Select) -->
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom flex-wrap gap-3">
        <!-- Category Pill Tabs -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="webinars.php<?php 
                $q = [];
                if (!empty($search)) $q['search'] = $search;
                if ($sortBy !== 'soonest') $q['sort'] = $sortBy;
                echo !empty($q) ? '?' . http_build_query($q) : ''; 
               ?>" 
               class="btn btn-sm rounded-pill px-3 btn-primary fw-semibold filter-link">
                Upcoming Webinars
            </a>
        </div>
        
        <!-- Sorting Select -->
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm bg-white border fw-medium sort-select" style="width: auto; min-width: 130px; height: 36px; border-radius: 20px; padding-left: 14px; padding-right: 36px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);" onchange="location = this.value;">
                <?php 
                $baseQuery = [];
                if ($search) $baseQuery['search'] = $search;
                
                $soonQuery = $baseQuery;
                $soonQuery['sort'] = 'soonest';
                $latQuery = $baseQuery;
                $latQuery['sort'] = 'latest';
                ?>
                <option value="webinars.php?<?php echo http_build_query($soonQuery); ?>" <?php echo $sortBy === 'soonest' ? 'selected' : ''; ?>>Upcoming</option>
                <option value="webinars.php?<?php echo http_build_query($latQuery); ?>" <?php echo $sortBy === 'latest' ? 'selected' : ''; ?>>Latest</option>
            </select>
        </div>
    </div>

    <!-- Webinars Grid -->
    <div class="row justify-content-center" id="webinars-grid">
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
