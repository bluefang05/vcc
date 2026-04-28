<?php
/**
 * VCC CMS - Categories Management
 */
session_start();
require_once '../config.php';

// Require login
requireLogin();

$pdo = getDBConnection();
$errors = [];
$success = false;

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($name)) {
        $errors[] = "Category name is required";
    } else {
        if (empty($slug)) {
            $slug = createSlug($name);
        }
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $slug, $description]);
        
        logActivity($pdo, $_SESSION['admin_id'], 'created_category', "Created category '$name'");
        header('Location: categories.php?success=1');
        exit;
    }
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    logActivity($pdo, $_SESSION['admin_id'], 'deleted_category', "Deleted category ID {$_GET['id']}");
    header('Location: categories.php?deleted=1');
    exit;
}

// Get all categories with post counts
$stmt = $pdo->query("
    SELECT c.*, COUNT(p.id) as post_count 
    FROM categories c 
    LEFT JOIN blog_posts p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.name
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Categories';
include 'includes/header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Categories</h1>
        <p>Manage your blog post categories</p>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Category created successfully!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Category deleted successfully!</div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="categories-layout">
        <!-- Add Category Form -->
        <div class="add-category-card">
            <h2>Add New Category</h2>
            <form method="POST" class="category-form">
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" required placeholder="Category name">
                </div>
                
                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" placeholder="category-slug (auto-generated if empty)">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Category description"></textarea>
                </div>
                
                <button type="submit" name="add_category" class="btn-add">Add Category</button>
            </form>
        </div>
        
        <!-- Categories List -->
        <div class="categories-list-card">
            <h2>All Categories</h2>
            
            <?php if (empty($categories)): ?>
                <div class="no-categories">
                    <p>No categories yet. Create your first category!</p>
                </div>
            <?php else: ?>
                <table class="categories-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Posts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                </td>
                                <td>
                                    <code><?php echo htmlspecialchars($cat['slug']); ?></code>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars(substr($cat['description'] ?: '-', 0, 50)); ?>
                                    <?php if (strlen($cat['description'] ?: '') > 50): ?>...<?php endif; ?>
                                </td>
                                <td>
                                    <span class="post-count"><?php echo $cat['post_count']; ?></span>
                                </td>
                                <td class="actions">
                                    <a href="categories.php?delete=1&id=<?php echo $cat['id']; ?>" class="btn-delete" onclick="return confirm('Delete this category? Posts will become uncategorized.')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .categories-layout {
        display: grid;
        grid-template-columns: 400px 1fr;
        gap: 25px;
    }
    
    .add-category-card,
    .categories-list-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .add-category-card h2,
    .categories-list-card h2 {
        font-size: 1.1rem;
        color: var(--primary);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .category-form .form-group {
        margin-bottom: 18px;
    }
    
    .category-form label {
        display: block;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 6px;
        font-size: 0.9rem;
    }
    
    .category-form input[type="text"],
    .category-form textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9rem;
        font-family: inherit;
    }
    
    .category-form input:focus,
    .category-form textarea:focus {
        outline: none;
        border-color: var(--secondary);
    }
    
    .category-form textarea {
        resize: vertical;
    }
    
    .btn-add {
        width: 100%;
        padding: 12px;
        background: var(--secondary);
        color: var(--primary);
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .categories-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .categories-table th,
    .categories-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .categories-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: var(--primary);
        font-size: 0.85rem;
    }
    
    .categories-table tr:last-child td {
        border-bottom: none;
    }
    
    .categories-table code {
        background: #f0f0f0;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #666;
    }
    
    .post-count {
        display: inline-block;
        padding: 4px 10px;
        background: rgba(0, 212, 212, 0.1);
        color: var(--primary);
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .btn-delete {
        padding: 6px 12px;
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
        border: none;
        border-radius: 5px;
        font-size: 0.8rem;
        cursor: pointer;
        text-decoration: none;
    }
    
    .no-categories {
        text-align: center;
        padding: 40px;
        color: #999;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success);
        border: 1px solid var(--success);
    }
    
    .alert-error {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
        border: 1px solid var(--danger);
    }
    
    .alert-error ul {
        margin: 0;
        padding-left: 20px;
    }
    
    @media (max-width: 1024px) {
        .categories-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>
