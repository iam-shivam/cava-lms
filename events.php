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

$sql .= " ORDER BY date ASC, created_at DESC";

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
            <!-- Events Grouped List -->
            <div id="events-list">
                <?php if (empty($eventsList)): ?>
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
                <?php else: 
                    // Helper function to extract time or fallback to a default
                    function getEventTime($desc) {
                        if (preg_match('/(\d{1,2}:\d{2}\s*(?:AM|PM)\s*-\s*\d{1,2}:\d{2}\s*(?:AM|PM))/i', $desc, $matches)) {
                            return $matches[1];
                        }
                        if (preg_match('/(\d{1,2}\s*(?:AM|PM)\s*-\s*\d{1,2}\s*(?:AM|PM))/i', $desc, $matches)) {
                            return $matches[1];
                        }
                        return '7:00 PM - 9:00 PM';
                    }

                    // Helper to get join URL from description or fallback
                    function getJoinUrl($desc) {
                        if (preg_match('/(https?:\/\/[^\s]+)/i', $desc, $matches)) {
                            return $matches[1];
                        }
                        return '#';
                    }

                    // Group events by date
                    $groupedEvents = [];
                    foreach ($eventsList as $ev) {
                        $dateKey = date('Y-m-d', strtotime($ev['date']));
                        $groupedEvents[$dateKey][] = $ev;
                    }
                    ksort($groupedEvents);

                    foreach ($groupedEvents as $dateStr => $eventsOfDay):
                ?>
                    <div class="date-separator py-2 px-3 mb-3 mt-4 rounded fw-bold text-muted" style="font-size: 0.95rem; letter-spacing: 0.5px; background-color: #f3f4f6;">
                        <?php echo date('F d, Y', strtotime($dateStr)); ?>
                    </div>

                    <?php foreach ($eventsOfDay as $ev): 
                        $timeStr = getEventTime($ev['description']);
                        $isPast = strtotime($ev['date']) < strtotime(date('Y-m-d'));
                        $joinUrl = !empty($ev['join_url']) ? $ev['join_url'] : '';
                    ?>
                        <div class="event-item-row d-flex flex-wrap align-items-center justify-content-between p-3 mb-3 bg-white border rounded shadow-sm">
                            <!-- Time Column -->
                            <div class="event-time-col col-md-3 d-flex align-items-center gap-2">
                                <span class="fw-semibold text-dark"><?php echo $timeStr; ?></span>
                                <i class="fa-regular fa-circle-question text-muted small" title="Event Time" style="cursor: pointer;"></i>
                            </div>
                            <!-- Title / Custom meeting Column -->
                            <div class="event-details-col col-md-6 mt-2 mt-md-0">
                                <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($ev['title']); ?></h6>
                                <span class="text-muted small"><?php echo htmlspecialchars(mb_strimwidth(strip_tags($ev['description']), 0, 100, '...')); ?></span>
                            </div>
                            <!-- Action Column -->
                            <div class="event-action-col col-md-3 text-md-end mt-3 mt-md-0 d-flex align-items-center justify-content-end gap-2">
                                <?php if (!empty($joinUrl)): ?>
                                    <a href="<?php echo htmlspecialchars($joinUrl); ?>" target="_blank" class="btn btn-primary btn-sm px-4 rounded-pill fw-semibold <?php echo $isPast ? 'disabled btn-secondary' : ''; ?>" <?php echo $isPast ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>Join</a>
                                <?php endif; ?>
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm border rounded-circle" type="button" data-bs-toggle="dropdown" style="width: 32px; height: 32px; padding: 0;">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <?php if (!empty($joinUrl)): ?>
                                            <li><a class="dropdown-item py-2" href="#" onclick="event.preventDefault(); navigator.clipboard.writeText('<?php echo htmlspecialchars($joinUrl); ?>'); alert('Event link copied to clipboard!');"><i class="fa-solid fa-share-nodes me-2"></i>Copy Link</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
