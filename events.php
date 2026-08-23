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

// Helper functions for event properties
if (!function_exists('getEventTime')) {
    function getEventTime($desc) {
        if (preg_match('/(\d{1,2}:\d{2}\s*(?:AM|PM)\s*-\s*\d{1,2}:\d{2}\s*(?:AM|PM))/i', $desc, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(\d{1,2}\s*(?:AM|PM)\s*-\s*\d{1,2}\s*(?:AM|PM))/i', $desc, $matches)) {
            return $matches[1];
        }
        return '7:00 PM - 9:00 PM';
    }
}

if (!function_exists('getJoinUrl')) {
    function getJoinUrl($desc) {
        if (preg_match('/(https?:\/\/[^\s]+)/i', $desc, $matches)) {
            return $matches[1];
        }
        return '#';
    }
}

$pageTitle       = 'Upcoming Events';
$pageDescription = 'Stay updated with upcoming events, seminars, and campus activities on CAVA LMS. Register today and expand your network.';

require_once __DIR__ . '/views/layout/header.php';
?>

<div class="lp-body-scope page-events">
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
            <p class="lp-page-hero-subtitle">Discover upcoming sessions, workshops, and learning opportunities from CAVA LMS.</p>
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

            <!-- Events Catalog Grid -->
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
                    <?php 
                    $cardIndex = 0;
                    foreach ($eventsList as $ev): 
                        $timeStr = getEventTime($ev['description']);
                        $isPast = strtotime($ev['date']) < strtotime(date('Y-m-d'));
                        $joinUrl = !empty($ev['join_url']) ? $ev['join_url'] : '';
                        
                        // Resolve image
                        $imageUrl = '';
                        if (!empty($ev['event_image'])) {
                            if (file_exists(BASE_PATH . '/uploads/' . $ev['event_image'])) {
                                $imageUrl = SITE_URL . '/uploads/' . $ev['event_image'];
                            } else {
                                $imageUrl = SITE_URL . '/assets/images/' . $ev['event_image'];
                            }
                        }
                        
                        // Stagger delay
                        $delayClass = '';
                        $delayMod = $cardIndex % 3;
                        if ($delayMod === 1) {
                            $delayClass = ' lms-delay-1';
                        } elseif ($delayMod === 2) {
                            $delayClass = ' lms-delay-2';
                        }
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4 lms-reveal-card<?php echo $delayClass; ?>">
                            <div class="custom-card">
                                <div class="card-img-wrapper">
                                    <?php if ($imageUrl): ?>
                                        <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($ev['title']); ?>" loading="lazy">
                                    <?php else: ?>
                                        <!-- editorial placeholder -->
                                        <div class="d-flex align-items-center justify-content-center bg-light text-primary" style="height: 100%; aspect-ratio: 16/10; width: 100%;">
                                            <i class="fa-regular fa-calendar-check" style="font-size: 2.5rem; color: #6366f1;"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <span class="card-badge bg-primary"><?php echo $isPast ? 'Past' : 'Upcoming'; ?></span>
                                </div>
                                
                                <div class="card-content">
                                    <h4 class="card-title">
                                        <?php echo htmlspecialchars($ev['title']); ?>
                                    </h4>
                                    
                                    <p class="card-text text-muted mb-4 fs-7" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 3rem;">
                                        <?php echo htmlspecialchars(strip_tags($ev['description'] ?? '')); ?>
                                    </p>
                                    
                                    <div class="card-meta">
                                        <span><i class="fa-regular fa-calendar"></i> <?php echo date('M d, Y', strtotime($ev['date'])); ?></span>
                                        <span><i class="fa-regular fa-clock"></i> <?php echo $timeStr; ?></span>
                                    </div>
                                    
                                    <div class="card-price-row">
                                        <div>
                                            <?php if (!empty($joinUrl)): ?>
                                                <a href="<?php echo htmlspecialchars($joinUrl); ?>" target="_blank" class="btn btn-primary btn-sm px-4 rounded-pill fw-semibold <?php echo $isPast ? 'disabled btn-secondary' : ''; ?>" <?php echo $isPast ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                                    Join Event
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm border rounded-circle d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" style="width: 32px; height: 32px; padding: 0;">
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
                                </div>
                            </div>
                        </div>
                    <?php 
                        $cardIndex++;
                    endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
