<?php
// Dedicated Events Page with Filters
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Event.php';

// Filter Inputs
$search = trim($_GET['search'] ?? '');
$timeframe = trim($_GET['timeframe'] ?? 'upcoming'); // all, upcoming

// Base query
$sql = "SELECT * FROM events WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($timeframe === 'upcoming') {
    $sql .= " AND date >= ? ";
    $params[] = date('Y-m-d');
}

$sql .= " ORDER BY date ASC";

try {
    $eventsList = DB::fetchAll($sql, $params);
} catch (Exception $e) {
    $eventsList = [];
}

$pageTitle       = 'Upcoming Events';
$pageDescription = 'Stay updated with upcoming events, seminars, and campus activities on CAVA LMS. Register today and expand your network.';

require_once __DIR__ . '/views/layout/header.php';
?>

<!-- Header Banner -->
<div class="bg-light py-5 mb-5 border-bottom">
    <div class="container text-center">
        <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill fw-semibold mb-2">Campus Events</span>
        <h1 class="fw-extrabold display-5 text-dark">Portal Events & Fairs</h1>
        <p class="text-muted col-md-6 mx-auto">Explore student intake fairs, live group evaluations, and university networking summits scheduled online and offline.</p>
    </div>
</div>

<div class="container mb-5">
    <!-- Filter Panel -->
    <div class="card border-0 shadow-sm p-4 bg-white rounded-4 mb-5">
        <form action="events.php" method="GET" class="row g-3">
            <div class="col-md-5">
                <label for="search" class="form-label fw-semibold fs-7 text-muted">Search Events</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" class="form-control bg-light border-start-0 ps-0" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" placeholder="e.g. Ontario fair, Mock speaking...">
                </div>
            </div>
            
            <div class="col-md-4">
                <label for="timeframe" class="form-label fw-semibold fs-7 text-muted">Scheduled Period</label>
                <select class="form-select bg-light border" id="timeframe" name="timeframe">
                    <option value="upcoming" <?php echo $timeframe === 'upcoming' ? 'selected' : ''; ?>>Upcoming Events Only</option>
                    <option value="all" <?php echo $timeframe === 'all' ? 'selected' : ''; ?>>All Historical & Scheduled</option>
                </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill">
                    <i class="fa-solid fa-filter me-1"></i> Apply Filters
                </button>
                <?php if (!empty($search) || $timeframe !== 'upcoming'): ?>
                    <a href="events.php" class="btn btn-outline-secondary w-50 py-2 rounded-pill text-center">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Events Grid -->
    <div class="row">
        <?php if (empty($eventsList)): ?>
            <div class="col text-center py-5">
                <i class="fa-regular fa-calendar-times fs-1 text-muted mb-3 d-block"></i>
                <h4 class="fw-bold text-dark">No Events Found</h4>
                <p class="text-muted">No scheduled events match your filtering choices.</p>
                <a href="events.php" class="btn btn-primary rounded-pill px-4 mt-2">Clear Filters</a>
            </div>
        <?php else: ?>
            <?php foreach ($eventsList as $ev): 
                $evDate = date('d M, Y', strtotime($ev['date']));
                $evImg = 'https://placehold.co/600x340/6f42c1/ffffff?text=Event';
                if ($ev['event_image'] && file_exists(BASE_PATH . '/uploads/' . $ev['event_image'])) {
                    $evImg = SITE_URL . '/uploads/' . $ev['event_image'];
                }
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="custom-card border-0 shadow-sm bg-white rounded-4 overflow-hidden h-100 animate-fade-in-up">
                        <div class="card-img-wrapper" style="height: 180px;">
                            <img src="<?php echo $evImg; ?>" alt="Event banner" onerror="this.src='https://placehold.co/600x340/6f42c1/ffffff?text=Event'">
                        </div>
                        <div class="p-4">
                            <span class="text-primary fw-semibold fs-8 d-block mb-1">
                                <i class="fa-regular fa-calendar me-1"></i><?php echo $evDate; ?>
                                <?php if (strtotime($ev['date']) < strtotime(date('Y-m-d'))): ?>
                                    <span class="badge bg-secondary ms-2">Closed</span>
                                <?php else: ?>
                                    <span class="badge bg-success-light text-success ms-2">Upcoming</span>
                                <?php endif; ?>
                            </span>
                            <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($ev['title']); ?></h5>
                            <p class="text-muted fs-7 mb-0"><?php echo htmlspecialchars($ev['description']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
