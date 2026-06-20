<?php
// Admin Categories CRUD
require_once __DIR__ . '/admin_header.php';

$action = trim($_GET['action'] ?? 'list');
$id = intval($_GET['id'] ?? 0);

// Process Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF token verification failed.');
        header("Location: categories.php");
        exit;
    }
    
    $name = trim($_POST['name'] ?? '');
    // Generate a simple URL slug
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
    
    if (empty($name)) {
        set_flash_message('danger', 'Category name cannot be empty.');
        header("Location: categories.php");
        exit;
    }
    
    try {
        if ($_POST['form_action'] === 'add') {
            // Check if slug exists
            $exists = DB::fetch("SELECT id FROM categories WHERE slug = ?", [$slug]);
            if ($exists) {
                $slug .= '-' . time();
            }
            
            $stmt = DB::getConnection()->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
            set_flash_message('success', 'Category added successfully!');
        } elseif ($_POST['form_action'] === 'edit' && $id > 0) {
            $stmt = DB::getConnection()->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $id]);
            set_flash_message('success', 'Category updated successfully!');
        }
    } catch (Exception $e) {
        set_flash_message('danger', 'Database Error: ' . $e->getMessage());
    }
    
    header("Location: categories.php");
    exit;
}

// Handle Delete Action
if ($action === 'delete' && $id > 0) {
    // Check if category contains courses
    $courseCount = DB::fetch("SELECT COUNT(id) as count FROM courses WHERE category_id = ?", [$id])['count'];
    if ($courseCount > 0) {
        set_flash_message('danger', 'Cannot delete category containing courses. Delete the courses first.');
    } else {
        try {
            DB::query("DELETE FROM categories WHERE id = ?", [$id]);
            set_flash_message('success', 'Category deleted successfully!');
        } catch (Exception $e) {
            set_flash_message('danger', 'Database Error: ' . $e->getMessage());
        }
    }
    header("Location: categories.php");
    exit;
}

// Fetch Category Details if Editing
$editCategory = null;
if ($action === 'edit' && $id > 0) {
    $editCategory = DB::fetch("SELECT * FROM categories WHERE id = ?", [$id]);
}

// Fetch All Categories
$categories = DB::fetchAll("SELECT c.*, (SELECT COUNT(id) FROM courses WHERE category_id = c.id) as course_count FROM categories c ORDER BY name ASC");
$csrfToken = generate_csrf_token();
?>

<div class="row">
    <!-- Form Side -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
            <h5 class="fw-bold text-primary mb-3">
                <?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?>
            </h5>
            
            <form action="categories.php?id=<?php echo $id; ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="form_action" value="<?php echo $editCategory ? 'edit' : 'add'; ?>">
                
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Category Name</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>" 
                           placeholder="e.g. Immigrations" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                    <?php echo $editCategory ? 'Save Changes' : 'Create Category'; ?>
                </button>
                
                <?php if ($editCategory): ?>
                    <a href="categories.php" class="btn btn-outline-secondary w-100 rounded-pill py-2 mt-2">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Table Side -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-4">Course Categories</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th class="text-center">Courses Count</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No categories created yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($cat['name']); ?></td>
                                    <td class="text-muted"><?php echo htmlspecialchars($cat['slug']); ?></td>
                                    <td class="text-center"><span class="badge bg-primary-light text-primary"><?php echo $cat['course_count']; ?></span></td>
                                    <td class="text-end">
                                        <a href="categories.php?action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-outline-primary btn-sm me-1" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="categories.php?action=delete&id=<?php echo $cat['id']; ?>" 
                                           class="btn btn-outline-danger btn-sm" 
                                           onclick="return confirm('Are you sure you want to delete this category?');" 
                                           title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
