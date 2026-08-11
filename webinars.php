<?php
// Dedicated Webinars Page with Filters
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Webinar.php';

$search = trim($_GET['search'] ?? '');
$sortBy = trim($_GET['sort'] ?? 'soonest'); // soonest, latest

$userId = $_SESSION['user_id'] ?? null;

// Base query
$sql = "SELECT * FROM webinars WHERE status = 'Active' AND (date > CURRENT_DATE() OR (date = CURRENT_DATE() AND time >= CURRENT_TIME()))";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($userId) {
    $sql .= " AND id NOT IN (SELECT webinar_id FROM webinar_registrations WHERE user_id = ?)";
    $params[] = $userId;
}

if ($sortBy === 'latest') {
    $sql .= " ORDER BY created_at DESC";
} else {
    // Default to soonest first
    $sql .= " ORDER BY date ASC, time ASC";
}

try {
    $webinarsList = DB::fetchAll($sql, $params);
} catch (Exception $e) {
    $webinarsList = [];
}

$pageTitle       = 'Live Webinars';
$pageDescription = 'Join live interactive webinars with certified consultants on CAVA LMS. Learn from experts, ask questions, and get your immigration queries resolved.';

require_once __DIR__ . '/views/layout/header.php';
?>

<div class="lp-body-scope">
    <!-- Page Hero Banner -->
    <section class="lp-page-hero">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Webinars</li>
                </ol>
            </nav>
            <h1 class="lp-page-hero-title">
                Live Webinars
            </h1>
            <p class="lp-page-hero-subtitle">Interact live with regulated consultants and career advisors. Ask questions in real-time and get personalized guidance.</p>
        </div>
    </section>

    <!-- Main Content Container -->
    <section class="py-5 bg-white">
        <div class="container">
            <!-- Filter & Search Controls Bar -->
            <div class="row align-items-center justify-content-between g-3 mb-5">
                <!-- Filter Pills -->
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="webinars.php<?php 
                            $q = [];
                            if (!empty($search)) $q['search'] = $search;
                            if ($sortBy !== 'soonest') $q['sort'] = $sortBy;
                            echo !empty($q) ? '?' . http_build_query($q) : ''; 
                           ?>" 
                           class="btn lp-filter-link active">
                            <i class="fa-solid fa-video me-1"></i> Upcoming Webinars
                        </a>
                    </div>
                </div>

                <!-- Search Input Box & Sort Dropdown -->
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Search Form -->
                        <form action="webinars.php" method="GET" class="m-0 flex-grow-1">
                            <?php if ($sortBy !== 'soonest'): ?>
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>">
                            <?php endif; ?>
                            
                            <div class="input-group lp-search-box align-items-center">
                                <span class="input-group-text p-0 me-2"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" class="form-control p-0" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Search webinars..." autocomplete="off">
                                <?php if (!empty($search)): ?>
                                    <a href="webinars.php" class="text-muted ms-2"><i class="fa-solid fa-xmark"></i></a>
                                <?php endif; ?>
                            </div>
                        </form>

                        <!-- Sorting Select -->
                        <select class="form-select form-select-sm bg-white border fw-semibold rounded-pill px-3 py-2 text-dark shadow-sm" style="width: auto; min-width: 120px;" onchange="location = this.value;">
                            <?php 
                            $baseQuery = [];
                            if ($search) $baseQuery['search'] = $search;
                            
                            $soonQuery = $baseQuery;
                            $soonQuery['sort'] = 'soonest';
                            $latQuery = $baseQuery;
                            $latQuery['sort'] = 'latest';
                            ?>
                            <option value="webinars.php?<?php echo http_build_query($soonQuery); ?>" <?php echo $sortBy === 'soonest' ? 'selected' : ''; ?>>Soonest</option>
                            <option value="webinars.php?<?php echo http_build_query($latQuery); ?>" <?php echo $sortBy === 'latest' ? 'selected' : ''; ?>>Latest</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Webinars Grid -->
            <div class="row justify-content-center g-4" id="webinars-grid">
                <?php if (empty($webinarsList)): ?>
                    <div class="col-12">
                        <div class="lp-empty-state">
                            <div class="lp-empty-icon">
                                <i class="fa-solid fa-video-slash"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-2">No Webinars Found</h3>
                            <p class="text-muted mb-4" style="max-width: 440px; margin: 0 auto;">No live masterclasses match your search criteria. Check back soon for new scheduled sessions.</p>
                            <a href="webinars.php" class="lp-btn-pill-dark">
                                Clear Filters <i class="fa-solid fa-rotate-left ms-1"></i>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($webinarsList as $webinar): 
                        $isRegistered = Webinar::isUserRegistered($userId, $webinar['id']);
                        require __DIR__ . '/views/components/webinar_card.php';
                    endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
