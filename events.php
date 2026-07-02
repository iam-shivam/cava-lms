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

<!-- Main Container -->
<div class="container mb-5">
    <!-- Header Row (Title & Sleek Search) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 mt-5">
        <h1 class="fw-extrabold text-dark m-0">Events</h1>
        
        <!-- Sleek Search Input -->
        <form action="events.php" method="GET" class="m-0" style="width: 100%; max-width: 320px;">
            <?php if ($timeframe !== 'upcoming'): ?>
                <input type="hidden" name="timeframe" value="<?php echo htmlspecialchars($timeframe); ?>">
            <?php endif; ?>
            <div class="input-group search-input-group align-items-center pe-3 bg-white">
                <span class="input-group-text bg-white border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" class="form-control border-0 ps-0" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Events..." autocomplete="off">
                <i class="fa-solid fa-xmark text-muted" id="search-clear" style="cursor: pointer; display: <?php echo !empty($search) ? 'block' : 'none'; ?>;"></i>
            </div>
        </form>
    </div>

    <!-- Filter Row (Horizontal Timeframe Tabs) -->
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom flex-wrap gap-3">
        <!-- Timeframe Pill Tabs -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="events.php?timeframe=upcoming<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
               class="btn btn-sm rounded-pill px-3 <?php echo $timeframe === 'upcoming' ? 'btn-primary' : 'btn-outline-secondary'; ?> fw-semibold filter-link">
                Upcoming Events
            </a>
            <a href="events.php?timeframe=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
               class="btn btn-sm rounded-pill px-3 <?php echo $timeframe === 'all' ? 'btn-primary' : 'btn-outline-secondary'; ?> fw-semibold filter-link">
                All Events
            </a>
        </div>
    </div>

    <!-- Events Grid -->
    <div class="row" id="events-grid">
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
