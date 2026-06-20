<?php
// Dedicated Courses Page with Filters
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';

// Fetch categories for filtering dropdown
$categories = Course::getCategories();

// Filter Inputs
$search = trim($_GET['search'] ?? '');
$categoryId = intval($_GET['category'] ?? 0);

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

if ($categoryId > 0) {
    $sql .= " AND c.category_id = ?";
    $params[] = $categoryId;
}

$sql .= " ORDER BY c.id DESC";

try {
    $coursesList = DB::fetchAll($sql, $params);
} catch (Exception $e) {
    $coursesList = [];
}

$userId = $_SESSION['user_id'] ?? null;
require_once __DIR__ . '/views/layout/header.php';
?>

<!-- Header Banner -->
<div class="bg-light py-5 mb-5 border-bottom">
    <div class="container text-center">
        <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill fw-semibold mb-2">Explore Modules</span>
        <h1 class="fw-extrabold display-5 text-dark">Our Self-Paced Courses</h1>
        <p class="text-muted col-md-6 mx-auto">Upgrade your skills at your own pace with our structured, video-enabled courses.</p>
    </div>
</div>

<div class="container mb-5">
    <!-- Filter Panel -->
    <div class="card border-0 shadow-sm p-4 bg-white rounded-4 mb-5">
        <form action="courses.php" method="GET" class="row g-3">
            <div class="col-md-5">
                <label for="search" class="form-label fw-semibold fs-7 text-muted">Search Courses</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" class="form-control bg-light border-start-0 ps-0" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" placeholder="e.g. Express Entry, IELTS...">
                </div>
            </div>
            
            <div class="col-md-4">
                <label for="category" class="form-label fw-semibold fs-7 text-muted">Filter by Category</label>
                <select class="form-select bg-light border" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId === intval($cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill">
                    <i class="fa-solid fa-filter me-1"></i> Apply Filters
                </button>
                <?php if (!empty($search) || $categoryId > 0): ?>
                    <a href="courses.php" class="btn btn-outline-secondary w-50 py-2 rounded-pill text-center">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Courses Grid -->
    <div class="row">
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
