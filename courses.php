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

<div class="container mb-5">
    <!-- Header Row (Title & Sleek Search) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 mt-5">
        <h1 class="fw-extrabold text-dark m-0">Courses</h1>
        
        <!-- Sleek Search Input -->
        <form action="courses.php" method="GET" class="m-0" style="width: 100%; max-width: 320px;">
            <?php if (!empty($categoryId)): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryId); ?>">
            <?php endif; ?>
            <?php if ($sort !== 'latest'): ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            <?php endif; ?>
            <div class="input-group search-input-group align-items-center pe-3 bg-white">
                <span class="input-group-text bg-white border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" class="form-control border-0 ps-0" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Courses..." autocomplete="off">
                <i class="fa-solid fa-xmark text-muted" id="search-clear" style="cursor: pointer; display: <?php echo !empty($search) ? 'block' : 'none'; ?>;"></i>
            </div>
        </form>
    </div>

    <!-- Filter Row (Horizontal Tabs & Sorting Select) -->
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom flex-wrap gap-3">
        <!-- Category Pill Tabs -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="courses.php<?php 
                $q = [];
                if (!empty($search)) $q['search'] = $search;
                if ($sort !== 'latest') $q['sort'] = $sort;
                echo !empty($q) ? '?' . http_build_query($q) : ''; 
               ?>" 
               class="btn btn-sm rounded-pill px-3 <?php echo empty($categoryId) ? 'btn-primary' : 'btn-outline-secondary'; ?> fw-semibold filter-link">
                All Courses
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="courses.php?category=<?php echo urlencode($cat['id']); ?><?php 
                    if (!empty($search)) echo '&search=' . urlencode($search);
                    if ($sort !== 'latest') echo '&sort=' . urlencode($sort);
                   ?>" 
                   class="btn btn-sm rounded-pill px-3 <?php echo (!empty($categoryId) && $categoryId === trim($cat['id'])) ? 'btn-primary' : 'btn-outline-secondary'; ?> fw-semibold filter-link">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <!-- Sorting Select -->
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm bg-white border fw-medium sort-select" style="width: auto; min-width: 120px; height: 36px; border-radius: 20px; padding-left: 14px; padding-right: 36px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);" onchange="location = this.value;">
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

    <!-- Courses Grid -->
    <div class="row" id="courses-grid">
        <?php if (empty($coursesList)): ?>
            <div class="col text-center py-5">
                <i class="fa-solid fa-circle-exclamation fs-1 text-muted mb-3 d-block"></i>
                <h4 class="fw-bold text-dark">No Courses Found</h4>
                <p class="text-muted">Try adjusting your filters or search keyword to find matching courses.</p>
                <a href="courses.php" class="btn btn-primary rounded-pill px-4 mt-2">Clear Filters</a>
            </div>
        <?php else: ?>
            <?php foreach ($coursesList as $course): 
                $isEnrolled = Course::isUserEnrolled($userId, $course['id']);
                require __DIR__ . '/views/components/course_card.php';
            endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
