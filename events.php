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

$sql .= " ORDER BY created_at DESC";

try {
    $eventsList = DB::fetchAll($sql, $params);
} catch (Exception $e) {
    $eventsList = [];
}

$pageTitle       = 'Upcoming Events';
$pageDescription = 'Stay updated with upcoming events, seminars, and campus activities on CAVA LMS. Register today and expand your network.';

require_once __DIR__ . '/views/layout/header.php';
?>

<div class="lp-body-scope">
    <!-- Page Hero Banner -->
    <section class="lp-page-hero">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Events</li>
                </ol>
            </nav>
            <h1 class="lp-page-hero-title">Upcoming Events</h1>
            <p class="lp-page-hero-subtitle">Stay up to date with visa fairs, university meetups, interactive Q&A sessions, and mock evaluation events.</p>
        </div>
    </section>

    <!-- Main Content Container -->
    <section class="py-5 bg-white">
        <div class="container">
            <!-- Filter & Search Controls Bar -->
            <div class="row align-items-center justify-content-between g-3 mb-5">
                <!-- Timeframe Pill Tabs -->
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="events.php?timeframe=upcoming<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn lp-filter-link <?php echo $timeframe === 'upcoming' ? 'active' : ''; ?>">
                            <i class="fa-regular fa-calendar-check me-1"></i> Upcoming Events
                        </a>
                        <a href="events.php?timeframe=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn lp-filter-link <?php echo $timeframe === 'all' ? 'active' : ''; ?>">
                            <i class="fa-solid fa-list me-1"></i> All Events
                        </a>
                    </div>
                </div>

                <!-- Search Input Box -->
                <div class="col-lg-4">
                    <form action="events.php" method="GET" class="m-0">
                        <?php if ($timeframe !== 'upcoming'): ?>
                            <input type="hidden" name="timeframe" value="<?php echo htmlspecialchars($timeframe); ?>">
                        <?php endif; ?>
                        <div class="input-group lp-search-box align-items-center">
                            <span class="input-group-text p-0 me-2"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" class="form-control p-0" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" placeholder="Search events..." autocomplete="off">
                            <?php if (!empty($search)): ?>
                                <a href="events.php<?php echo $timeframe !== 'upcoming' ? '?timeframe=' . urlencode($timeframe) : ''; ?>" class="text-muted ms-2"><i class="fa-solid fa-xmark"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Events Grid -->
            <div class="row g-4" id="events-grid">
                <?php if (empty($eventsList)): ?>
                    <div class="col-12">
                        <div class="lp-empty-state">
                            <div class="lp-empty-icon">
                                <i class="fa-regular fa-calendar-xmark"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-2">No Events Found</h3>
                            <p class="text-muted mb-4" style="max-width: 440px; margin: 0 auto;">No campus events match your current filter parameters. Check back later for upcoming meetups.</p>
                            <a href="events.php" class="lp-btn-pill-dark">
                                Clear Filters <i class="fa-solid fa-rotate-left ms-1"></i>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($eventsList as $ev): 
                        $evDate = date('d M, Y', strtotime($ev['date']));
                        $evImg = 'https://placehold.co/600x340/6f42c1/ffffff?text=Event';
                        if ($ev['event_image'] && file_exists(BASE_PATH . '/uploads/' . $ev['event_image'])) {
                            $evImg = SITE_URL . '/uploads/' . $ev['event_image'];
                        }
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="custom-card border-0 shadow-sm bg-white rounded-4 overflow-hidden h-100 event-card">
                                <div class="card-img-wrapper" style="height: 190px;">
                                    <img src="<?php echo $evImg; ?>" alt="<?php echo htmlspecialchars($ev['title']); ?>" class="img-fluid w-100 h-100" style="object-fit: cover;" loading="lazy" onerror="this.src='https://placehold.co/600x340/6f42c1/ffffff?text=Event'">
                                </div>
                                <div class="p-4 d-flex flex-column h-100">
                                    <div class="mb-3 d-flex align-items-center justify-content-between">
                                        <span class="calendar-badge">
                                            <i class="fa-regular fa-calendar-days me-1"></i> <?php echo $evDate; ?>
                                        </span>
                                        <?php if (strtotime($ev['date']) < strtotime(date('Y-m-d'))): ?>
                                            <span class="badge bg-secondary-light text-secondary rounded-pill px-3 py-1 fw-semibold">Closed</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-light text-success rounded-pill px-3 py-1 fw-semibold">Upcoming</span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($ev['title']); ?></h5>
                                    <p class="text-muted fs-7 mb-0"><?php echo htmlspecialchars($ev['description']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
