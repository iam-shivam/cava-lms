<?php
// Dedicated Courses Page with Filters
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';

// Fetch categories for filtering dropdown
$categories = Course::getCategories();

// Filter Inputs
$search = trim($_GET['search'] ?? '');
$categoryId = trim($_GET['category'] ?? '');
$sort = trim($_GET['sort'] ?? 'latest');

$userId = $_SESSION['user_id'] ?? null;

// Base query
$sql = "SELECT c.*, cat.name as category_name 
        FROM courses c 
        JOIN categories cat ON c.category_id = cat.id 
        WHERE c.status = 'Published'";
$params = [];

if (!empty($search)) {
    $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if (!empty($categoryId)) {
    $sql .= " AND c.category_id = ?";
    $params[] = $categoryId;
}

if ($userId) {
    $sql .= " AND c.id NOT IN (SELECT course_id FROM enrollments WHERE user_id = ?)";
    $params[] = $userId;
}

if ($sort === 'alpha') {
    $sql .= " ORDER BY c.title ASC";
} else {
    $sql .= " ORDER BY c.created_at DESC";
}

try {
    $coursesList = DB::fetchAll($sql, $params);
} catch (Exception $e) {
    $coursesList = [];
}

// SEO meta for this page
$pageTitle       = 'Browse Courses';
$pageDescription = 'Explore all self-paced courses on CAVA LMS. Filter by category, search by topic, and start learning immigration, career, and skills courses today.';

require_once __DIR__ . '/views/layout/header.php';
?>

<div class="lp-body-scope">
    <!-- Page Hero Banner -->
    <section class="lp-page-hero">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Courses</li>
                </ol>
            </nav>
            <h1 class="lp-page-hero-title">Browse Courses</h1>
            <p class="lp-page-hero-subtitle">High-quality video modules designed by industry experts with lifetime access and locked-syllabus progression.</p>
        </div>
    </section>

    <!-- Main Content Container -->
    <section class="py-5 bg-white">
        <div class="container">
            <!-- Filter & Search Controls Bar -->
            <div class="row align-items-center justify-content-between g-3 mb-5">
                <!-- Category Filter Pills -->
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="courses.php<?php 
                            $q = [];
                            if (!empty($search)) $q['search'] = $search;
                            if ($sort !== 'latest') $q['sort'] = $sort;
                            echo !empty($q) ? '?' . http_build_query($q) : ''; 
                           ?>" 
                           class="btn lp-filter-link <?php echo empty($categoryId) ? 'active' : ''; ?>">
                            All Courses
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="courses.php?category=<?php echo urlencode($cat['id']); ?><?php 
                                if (!empty($search)) echo '&search=' . urlencode($search);
                                if ($sort !== 'latest') echo '&sort=' . urlencode($sort);
                               ?>" 
                               class="btn lp-filter-link <?php echo (!empty($categoryId) && $categoryId === trim($cat['id'])) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Search Input Box & Sort Dropdown -->
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Search Form -->
                        <form action="courses.php" method="GET" class="m-0 flex-grow-1">
                            <?php if (!empty($categoryId)): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryId); ?>">
                            <?php endif; ?>
                            <?php if ($sort !== 'latest'): ?>
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                            <?php endif; ?>
                            
                            <div class="input-group lp-search-box align-items-center">
                                <span class="input-group-text p-0 me-2"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" class="form-control p-0" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Search courses..." autocomplete="off">
                                <?php if (!empty($search)): ?>
                                    <a href="courses.php<?php echo !empty($categoryId) ? '?category=' . urlencode($categoryId) : ''; ?>" class="text-muted ms-2"><i class="fa-solid fa-xmark"></i></a>
                                <?php endif; ?>
                            </div>
                        </form>

                        <!-- Sorting Select -->
                        <select class="form-select form-select-sm bg-white border fw-semibold rounded-pill px-3 py-2 text-dark shadow-sm" style="width: auto; min-width: 110px;" onchange="location = this.value;">
                            <?php 
                            $baseQuery = [];
                            if ($categoryId) $baseQuery['category'] = $categoryId;
                            if ($search) $baseQuery['search'] = $search;
                            
                            $latQuery = $baseQuery;
                            $latQuery['sort'] = 'latest';
                            $alpQuery = $baseQuery;
                            $alpQuery['sort'] = 'alpha';
                            ?>
                            <option value="courses.php?<?php echo http_build_query($latQuery); ?>" <?php echo $sort === 'latest' ? 'selected' : ''; ?>>Latest</option>
                            <option value="courses.php?<?php echo http_build_query($alpQuery); ?>" <?php echo $sort === 'alpha' ? 'selected' : ''; ?>>A – Z</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Courses Catalog Grid -->
            <div class="row g-4" id="courses-grid">
                <?php if (empty($coursesList)): ?>
                    <div class="col-12">
                        <div class="lp-empty-state">
                            <div class="lp-empty-icon">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-2">No Courses Found</h3>
                            <p class="text-muted mb-4" style="max-width: 440px; margin: 0 auto;">No courses matched your search or category filter. Try clearing filters to see all available programs.</p>
                            <a href="courses.php" class="lp-btn-pill-dark">
                                Clear Filters <i class="fa-solid fa-rotate-left ms-1"></i>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($coursesList as $course): 
                        $isEnrolled = Course::isUserEnrolled($userId, $course['id']);
                        require __DIR__ . '/views/components/course_card.php';
                    endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
