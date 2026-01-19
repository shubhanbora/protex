<?php
$pageTitle = 'Categories Management';

// Security Check - Must be logged in as admin
require_once 'auth_check.php';

require_once '../config/database.php';
require_once '../config/security.php';
require_once 'includes/header.php';

$conn = getDBConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        if (isset($_POST['add_category'])) {
            $name = sanitizeInput($_POST['name']);
            $description = sanitizeInput($_POST['description']);
            
            if (empty($name)) {
                $error = 'Category name is required';
            } else {
                $query = "INSERT INTO categories (name, description) VALUES (?, ?)";
                $result = executeQuery($conn, $query, "ss", [$name, $description]);
                
                if ($result) {
                    $success = 'Category added successfully!';
                } else {
                    $error = 'Error adding category. Please try again.';
                }
            }
        }
        
        if (isset($_POST['update_category'])) {
            $id = intval($_POST['id']);
            $name = sanitizeInput($_POST['name']);
            $description = sanitizeInput($_POST['description']);
            
            if (empty($name)) {
                $error = 'Category name is required';
            } else {
                $query = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
                $result = executeQuery($conn, $query, "ssi", [$name, $description, $id]);
                
                if ($result) {
                    $success = 'Category updated successfully!';
                } else {
                    $error = 'Error updating category. Please try again.';
                }
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Check if category has products
    $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = ? AND is_deleted = 0";
    $check_result = executeQuery($conn, $check_query, "i", [$id]);
    $check_data = $check_result->fetch_assoc();
    
    if ($check_data['count'] > 0) {
        $error = 'Cannot delete category. It has ' . $check_data['count'] . ' products associated with it.';
    } else {
        $query = "DELETE FROM categories WHERE id = ?";
        $result = executeQuery($conn, $query, "i", [$id]);
        
        if ($result) {
            $success = 'Category deleted successfully!';
        } else {
            $error = 'Error deleting category. Please try again.';
        }
    }
}

// Get all categories
$categories_query = "SELECT c.*, COUNT(p.id) as product_count 
                     FROM categories c 
                     LEFT JOIN products p ON c.id = p.category_id AND p.is_deleted = 0 
                     GROUP BY c.id 
                     ORDER BY c.name";
$categories_result = executeQuery($conn, $categories_query);

// Get category for editing
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = "SELECT * FROM categories WHERE id = ?";
    $edit_result = executeQuery($conn, $edit_query, "i", [$edit_id]);
    if ($edit_result && $edit_result->num_rows > 0) {
        $edit_category = $edit_result->fetch_assoc();
    }
}
?>

<style>
.categories-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.categories-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.category-form {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 2fr auto;
    gap: 15px;
    align-items: end;
}

.categories-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.category-actions {
    display: flex;
    gap: 10px;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 0.85rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .categories-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}
</style>

<div class="categories-container">
    <div class="categories-header">
        <h2><?php echo $edit_category ? 'Edit Category' : 'Categories Management'; ?></h2>
        <div>
            <a href="products.php" class="btn" style="background: #10b981; color: white;">
                <i class="fas fa-box"></i> Manage Products
            </a>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-dashboard"></i> Dashboard
            </a>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Add/Edit Category Form -->
    <div class="category-form">
        <h3><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h3>
        <form method="POST">
            <?php echo csrfField(); ?>
            <?php if ($edit_category): ?>
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" 
                           name="name" 
                           class="form-control" 
                           value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>"
                           placeholder="e.g., Whey Protein Isolate" 
                           required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" 
                              class="form-control" 
                              rows="3"
                              placeholder="Brief description of the category..."><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" 
                            name="<?php echo $edit_category ? 'update_category' : 'add_category'; ?>" 
                            class="btn btn-primary">
                        <i class="fas fa-<?php echo $edit_category ? 'save' : 'plus'; ?>"></i>
                        <?php echo $edit_category ? 'Update' : 'Add'; ?> Category
                    </button>
                    <?php if ($edit_category): ?>
                        <a href="categories.php" class="btn" style="background: #6b7280; color: white; margin-left: 10px;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="categories-table">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($categories_result) > 0): ?>
                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars(substr($category['description'] ?? '', 0, 100)) . (strlen($category['description'] ?? '') > 100 ? '...' : ''); ?></td>
                            <td>
                                <span class="badge" style="background: #10b981; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8rem;">
                                    <?php echo $category['product_count']; ?> products
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($category['created_at'])); ?></td>
                            <td>
                                <div class="category-actions">
                                    <a href="categories.php?edit=<?php echo $category['id']; ?>" 
                                       class="btn btn-sm" 
                                       style="background: #f59e0b; color: white;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if ($category['product_count'] == 0): ?>
                                        <a href="categories.php?delete=<?php echo $category['id']; ?>" 
                                           class="btn btn-sm" 
                                           style="background: #ef4444; color: white;"
                                           onclick="return confirm('Are you sure you want to delete this category?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="btn btn-sm" 
                                              style="background: #d1d5db; color: #6b7280; cursor: not-allowed;"
                                              title="Cannot delete - has products">
                                            <i class="fas fa-lock"></i> Protected
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">
                            <i class="fas fa-tags" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                            No categories found. Add your first category above.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>